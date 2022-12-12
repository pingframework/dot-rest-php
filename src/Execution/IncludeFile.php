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

namespace Pingframework\DotRestPhp\Execution;

use Pingframework\DotRestPhp\Exception\FileError;
use Pingframework\DotRestPhp\Exception\SyntaxError;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;
use Pingframework\DotRestPhp\Parsing\Utils\FileParserTrait;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Utils\PlaceholderTrait;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class IncludeFile
{
    use FileParserTrait;
    use PlaceholderTrait;

    public function __construct(
        public readonly ParserRegistry $parserRegistry,
        public readonly Line           $line,
        public readonly string         $file,
        public readonly string         $dir,
    ) {}

    /**
     * @throws SyntaxError
     */
    public function parse(Context $ctx): array
    {
        try {
            $path = $this->dir . DIRECTORY_SEPARATOR . $this->replacePlaceholders(
                    $this->file,
                    $ctx,
                    $this->line,
                );
            $ctx->logger->include()->print($path);
        } catch (SyntaxError $e) {
            throw new SyntaxError(
                sprintf(
                    "Failed to resolve file path %s. Reason: %s",
                    $this->file,
                    $e->getMessage(),
                ),
                $this->line,
            );
        }

        try {
            return $this->parseFile($path, $this->parserRegistry);
        } catch (FileError $e) {
            throw new SyntaxError(
                sprintf(
                    "Failed to include file %s. Reason: %s",
                    $path,
                    $e->getMessage(),
                ),
                $this->line,
            );
        }
    }
}