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

use Pingframework\DotRestPhp\Execution\IncludeFile;
use Pingframework\DotRestPhp\Execution\IncludeRunner;
use Pingframework\DotRestPhp\Execution\Runner;
use Pingframework\DotRestPhp\Parsing\Utils\FileParserTrait;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Reading\LinearReader;
use Pingframework\DotRestPhp\Utils\PlaceholderTrait;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class IncludeParser implements Parser
{
    use FileParserTrait;
    use PlaceholderTrait;

    private const PATTERN = '/^\s*(include)\s+(.*?)$/';
    private array $cache = [];

    public function isApplicable(Line $l): bool
    {
        return preg_match(self::PATTERN, $l->content, $this->cache) === 1;
    }

    /**
     * @param Line           $l
     * @param LinearReader   $r
     * @param ParserRegistry $pr
     * @return Runner
     */
    public function parse(Line $l, LinearReader $r, ParserRegistry $pr): Runner
    {
        return new IncludeRunner(
            new IncludeFile(
                $pr,
                $l,
                $this->cache[2],
                dirname(realpath($l->path))
            )
        );
    }
}