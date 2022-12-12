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

use Pingframework\DotRestPhp\Exception\AssertionError;
use Pingframework\DotRestPhp\Execution\AssertRunner;
use Pingframework\DotRestPhp\Output\AssertionLogger;
use Pingframework\DotRestPhp\Utils\StringifierTrait;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class ConsoleAssertionLogger extends AbstractConsoleLogger implements AssertionLogger
{
    use StringifierTrait;

    /**
     * @var array<AssertRunner>
     */
    private array $assertions = [];
    /**
     * @var array<AssertRunner>
     */
    private array $failed = [];

    public function success(
        AssertRunner $assertion,
        mixed        $expected,
        mixed        $actual,
    ): void {
        $this->assertions[] = $assertion;

        if (!$this->io->isVerbose()) {
            return;
        }

        $this->printAssertion(
            true,
            $assertion->leftOperand->expression,
            $assertion->operator,
            $assertion->rightOperand->expression,
            $expected,
            $actual,
        );
    }

    public function error(
        AssertRunner   $assertion,
        AssertionError $error,
    ): void {
        $this->assertions[] = $assertion;
        $this->failed[] = $assertion;

        if (!$this->io->isVerbose()) {
            return;
        }

        $this->printAssertion(
            false,
            $assertion->leftOperand->expression,
            $assertion->operator,
            $assertion->rightOperand->expression,
            $error->expected,
            $error->actual,
        );
    }

    private function printAssertion(
        bool   $success,
        string $leftOperand,
        string $operator,
        string $rightOperand,
        mixed  $expected,
        mixed  $actual,
    ): void {
        $this->io->writeln(
            sprintf(
                '  [<fg=%s;options=bold>%s</>] <fg=magenta>assertion</>: <fg=green>%s</> %s <fg=green>%s</>',
                $success ? 'green' : 'red',
                $success ? 'OK' : 'ER',
                $leftOperand,
                $operator,
                $rightOperand,
            ),
        );

        if ($this->io->isVeryVerbose() && !$success) {
            $this->io->write(
                sprintf(
                    "<fg=yellow>%17s</>: %-80s\n",
                    'expected',
                    $this->stringify($expected),
                ),
            );
            $this->io->writeln(
                sprintf(
                    "<fg=red>%17s</>: %-80s\n",
                    'actual',
                    $this->stringify($actual),
                ),
            );
        }
    }

    public function getFailedCount(): int
    {
        return count($this->failed);
    }

    public function getTotalCount(): int
    {
        return count($this->assertions);
    }

    public function summary(): string
    {
        return sprintf(
            "total: <fg=white;options=bold>%d</>, failed: <fg=%s>%d</>",
            count($this->assertions),
            count($this->failed) > 0 ? 'red;options=bold' : 'green',
            count($this->failed),
        );
    }
}