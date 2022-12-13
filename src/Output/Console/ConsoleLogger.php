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

namespace Pingframework\DotRestPhp\Output\Console;

use Pingframework\DotRestPhp\Output\AssertionLogger;
use Pingframework\DotRestPhp\Output\CommentLogger;
use Pingframework\DotRestPhp\Output\ConfigLogger;
use Pingframework\DotRestPhp\Output\DurationLogger;
use Pingframework\DotRestPhp\Output\EchoLogger;
use Pingframework\DotRestPhp\Output\ErrorLogger;
use Pingframework\DotRestPhp\Output\EvalLogger;
use Pingframework\DotRestPhp\Output\HttpClientLogger;
use Pingframework\DotRestPhp\Output\IncludeLogger;
use Pingframework\DotRestPhp\Output\Logger;
use Pingframework\DotRestPhp\Output\ReturnLogger;
use Pingframework\DotRestPhp\Output\SummaryLogger;
use Pingframework\DotRestPhp\Output\VarLogger;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class ConsoleLogger implements Logger
{
    public function __construct(
        public readonly EchoLogger       $echoLogger,
        public readonly AssertionLogger  $assertionLogger,
        public readonly ErrorLogger      $errorLogger,
        public readonly CommentLogger    $commentLogger,
        public readonly HttpClientLogger $httpClientLogger,
        public readonly EvalLogger       $evalLogger,
        public readonly ConfigLogger     $configLogger,
        public readonly VarLogger        $varLogger,
        public readonly IncludeLogger    $includeLogger,
        public readonly DurationLogger   $durationLogger,
        public readonly SummaryLogger    $summaryLogger,
        public readonly ReturnLogger     $returnLogger,
    ) {}

    public static function build(SymfonyStyle $io): static
    {
        return new static(
            new ConsoleEchoLogger($io),
            new ConsoleAssertionLogger($io),
            new ConsoleErrorLogger($io),
            new ConsoleCommentLogger($io),
            new ConsoleHttpClientLogger($io),
            new ConsoleEvalLogger($io),
            new ConsoleConfigLogger($io),
            new ConsoleVarLogger($io),
            new ConsoleIncludeLogger($io),
            new ConsoleDurationLogger($io),
            new ConsoleSummaryLogger($io),
            new ConsoleReturnLogger($io),
        );
    }

    public function duration(): DurationLogger
    {
        return $this->durationLogger;
    }

    public function include(): IncludeLogger
    {
        return $this->includeLogger;
    }

    public function var(): VarLogger
    {
        return $this->varLogger;
    }

    public function config(): ConfigLogger
    {
        return $this->configLogger;
    }

    public function eval(): EvalLogger
    {
        return $this->evalLogger;
    }

    public function echo(): EchoLogger
    {
        return $this->echoLogger;
    }

    public function assertion(): AssertionLogger
    {
        return $this->assertionLogger;
    }

    public function error(): ErrorLogger
    {
        return $this->errorLogger;
    }

    public function comment(): CommentLogger
    {
        return $this->commentLogger;
    }

    public function httpClient(): HttpClientLogger
    {
        return $this->httpClientLogger;
    }

    public function summary(): SummaryLogger
    {
        return $this->summaryLogger;
    }

    public function return(): ReturnLogger
    {
        return $this->returnLogger;
    }
}