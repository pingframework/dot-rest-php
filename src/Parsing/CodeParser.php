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

namespace Pingframework\DotRestPhp\Parsing;

use Pingframework\DotRestPhp\Exception\SyntaxError;
use Pingframework\DotRestPhp\Execution\CodeBlock;
use Pingframework\DotRestPhp\Execution\CodeRunner;
use Pingframework\DotRestPhp\Execution\Runner;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Reading\LinearReader;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class CodeParser implements Parser
{
    public function isApplicable(Line $l): bool
    {
        return str_starts_with(trim($l->content), '<?php');
    }

    /**
     * @param Line           $l
     * @param LinearReader   $r
     * @param ParserRegistry $pr
     * @return Runner
     * @throws SyntaxError
     */
    public function parse(Line $l, LinearReader $r, ParserRegistry $pr): Runner
    {
        return new CodeRunner(
            $l,
            $this->isSingleLineBlock($l)
                ? $this->parseSingleLineBlock($l)
                : $this->parseMultiLineBlock($l, $r)
        );
    }

    private function isSingleLineBlock(Line $l): bool
    {
        preg_match('/(<\?php\s+(.+?)\?>)/', $l->content, $matches);
        return !empty($matches);
    }

    private function parseMultilineBlock(Line $l, LinearReader $r): array
    {
        $code = trim(str_replace('<?php', '', $l->content));
        while ($line = $r->next()) {
            if (str_contains($line->content, '?>')) {
                preg_match('/(.*?)\?>/', $line->content, $matches);
                $code .= $matches[1];
                return array_merge([new CodeBlock($code, $line)], $this->parseSingleLineBlock($line));
            }
            $code .= $line->content;
        }
        return [new CodeBlock($code, $l)];
    }

    private function parseSingleLineBlock(Line $l): array
    {
        preg_match_all('/(<\?php\s+(.+?)\?>)/', $l->content, $matches);
        if (empty($matches[2])) {
            return [];
        }

        $codeBlocks = [];
        foreach ($matches[2] as $code) {
            $codeBlocks[] = new CodeBlock($code, $l);
        }

        return $codeBlocks;
    }
}