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
use Pingframework\DotRestPhp\Execution\RequestRunner;
use Pingframework\DotRestPhp\Execution\Runner;
use Pingframework\DotRestPhp\Parsing\Request\BodyReader;
use Pingframework\DotRestPhp\Parsing\Request\HeadersReader;
use Pingframework\DotRestPhp\Parsing\Request\OptionsReader;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Reading\LinearReader;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class RequestParser implements Parser
{
    private const PATTERN = '/^\s*(GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS)\s+(.*?)$/';

    private array $cache = [];

    public function __construct(
        public readonly HeadersReader $headersReader,
        public readonly OptionsReader $optionsReader,
        public readonly BodyReader    $bodyReader,
    ) {}

    public function isApplicable(Line $l): bool
    {
        preg_match(self::PATTERN, $l->content, $this->cache);
        return !empty($this->cache);
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
        return new RequestRunner(
            $l,
            $this->cache[1],
            $this->cache[2],
            $this->headersReader->read($r),
            $this->optionsReader->read($r),
            $this->bodyReader->read($r, $pr),
        );
    }
}