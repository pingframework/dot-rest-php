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

use Pingframework\DotRestPhp\Execution\CodeBlock;
use Pingframework\DotRestPhp\Output\Console\Utils\ColorizerTrait;
use Pingframework\DotRestPhp\Output\EvalLogger;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class ConsoleEvalLogger extends AbstractConsoleLogger implements EvalLogger
{
    use ColorizerTrait;

    private int $total = 0;

    public function start(CodeBlock $cb): void
    {
        $this->total++;

        if (!$this->io->isDebug()) {
            return;
        }

        $this->io->write(
            sprintf(
                "\n\n <fg=green>[INFO]</> Evaluating code: <fg=blue>%s</>:%d",
                $this->colorize($cb->line->path, 'blue'),
                $cb->line->num,
            ),
        );
    }

    public function success(CodeBlock $cb): void
    {
        if (!$this->io->isDebug()) {
            return;
        }

        $this->io->write(
            sprintf(
                "...%s",
                $this->colorize('OK', 'green'),
            ),
        );

        $this->io->writeln('');
        $mask = "%5s | %-80s\n";
        $n = $cb->line->num;
        foreach (explode("\n", $cb->code) as $line) {
            $n++;
            $l = trim($line, "\n\r");
            $this->io->write(sprintf($mask, $n, $this->colorize($l, 'gray')));
        }
    }

    public function summary(): string
    {
        return (string)$this->total;
    }
}