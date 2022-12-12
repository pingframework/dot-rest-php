<?php

/**
 * Dot Rest PHP
 *
 * MIT License
 *
 * Copyright (c) 2022 pingframework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */

declare(strict_types=1);

namespace Pingframework\DotRestPhp\Parsing\Request;

use Pingframework\DotRestPhp\Exception\SyntaxError;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;
use Pingframework\DotRestPhp\Parsing\Utils\KeyValueParserTrait;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Reading\LinearReader;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class BodyReader
{
    use KeyValueParserTrait;

    /**
     * @throws SyntaxError
     */
    public function read(LinearReader $r, ParserRegistry $pr): array|string|null
    {
        $l = $r->nextToken();

        if ($l === false) {
            return null;
        }

        $token = strtolower(trim($l->content));
        if ($token === '[form]') {
            return ['form_params' => $this->readKeyValue($r)];
        }

        $r->back($l);

        if ($token === '[multipart]') {
            return ['multipart' => $this->readMultipart($r)];
        }

        if (!$this->isKnownToken($l, $pr)) {
            return $this->readPlantText($r, $pr);
        }

        return null;
    }

    private function isKnownToken(Line $l, ParserRegistry $pr): bool
    {
        try {
            $pr->find($l);
            return true;
        } catch (SyntaxError $e) {
            return false;
        }
    }

    /**
     * @throws SyntaxError
     */
    private function readMultipart(LinearReader $r): array
    {
        $result = [];
        while ($l = $r->nextToken()) {
            $token = strtolower(trim($l->content));
            if ($token === '[multipart]') {
                $multipart = $this->readKeyValue($r);
                if (!isset($multipart['name']) || !isset($multipart['contents'])) {
                    throw new SyntaxError('Multipart name or contents is not set', $l);
                }
                $result[] = $multipart;
            } else {
                $r->back($l);
                break;
            }
        }
        return $result;
    }

    private function readPlantText(LinearReader $r, ParserRegistry $pr): string
    {
        $result = '';
        while ($l = $r->next()) {
            if ($this->isKnownToken($l, $pr)) {
                $r->back($l);
                break;
            }
            $result .= $l->content;
        }
        return trim($result);
    }
}