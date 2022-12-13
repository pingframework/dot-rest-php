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

use Pingframework\DotRestPhp\Execution\Context;
use Pingframework\DotRestPhp\Execution\Value;
use Pingframework\DotRestPhp\Output\ReturnLogger;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Utils\StringifierTrait;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class ConsoleReturnLogger extends AbstractConsoleLogger implements ReturnLogger
{
    use StringifierTrait;

    private ?Line  $l = null;
    private ?Value $v = null;

    public function store(Line $l, Value $v): void
    {
        $this->l = $l;
        $this->v = $v;
    }

    public function print(Context $ctx): void
    {
        if ($this->l !== null || $this->v !== null) {
            $this->io->writeln($this->stringify($this->v->resolve($this->l, $ctx)));
            return;
        }

        $this->io->writeln($ctx->hasResponse() ? $ctx->body() : '');
    }
}