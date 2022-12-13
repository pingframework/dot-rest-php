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

namespace Pingframework\DotRestPhp;

use DateTimeImmutable;
use Pingframework\DotRestPhp\Execution\Context;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;
use Pingframework\DotRestPhp\Parsing\Utils\FileParserTrait;
use Pingframework\DotRestPhp\Utils\DateTimeInterval;
use Pingframework\Streams\Helpers\the;
use Pingframework\Streams\Stream;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class DotRestPhp
{
    use FileParserTrait;

    public function __construct(
        public readonly ParserRegistry $parserRegistry,
        public readonly Context        $ctx,
    ) {}

    /**
     * @param string $file
     * @return bool
     */
    public function run(string $file): bool
    {
        try {
            $dti = new DateTimeInterval();
            Stream::of($this->parseFile($file, $this->parserRegistry))
                ->forEach(the::object()->run($this->ctx));

            $this->ctx->config->testMode
                ? $this->ctx->logger->summary()->print($file, $dti, $this->ctx)
                : $this->ctx->logger->return()->print($this->ctx);

            return !$this->ctx->config->testMode || $this->ctx->logger->assertion()->getFailedCount() === 0;
        } catch (Throwable $e) {
            $this->ctx->logger->error()->print($e);
            return false;
        }
    }
}