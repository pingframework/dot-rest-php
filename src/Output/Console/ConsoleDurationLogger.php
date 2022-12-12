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

use Pingframework\DotRestPhp\Output\Console\Utils\ColorizerTrait;
use Pingframework\DotRestPhp\Output\DurationLogger;
use Pingframework\DotRestPhp\Output\VarLogger;
use Pingframework\DotRestPhp\Utils\DateTimeInterval;
use Pingframework\DotRestPhp\Utils\StringifierTrait;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class ConsoleDurationLogger extends AbstractConsoleLogger implements DurationLogger
{
    use ColorizerTrait;
    use StringifierTrait;

    private int $total = 0;

    public function print(DateTimeInterval $dti): void
    {
        $this->total++;

        if (!$this->io->isDebug()) {
            return;
        }

        $this->io->info(
            sprintf(
                "%s: %s",
                $this->colorize('Time is marked at', 'white'),
                $this->colorize($dti->startedAt->format('r'), 'green'),
            ),
        );
    }

    public function summary(): string
    {
        return (string)$this->total;
    }
}