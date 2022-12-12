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

use Pingframework\DotRestPhp\Exception\EvaluationError;
use Pingframework\DotRestPhp\Exception\ExecutionError;
use Pingframework\DotRestPhp\Reading\Line;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class CodeBlock implements Runner
{
    private static array $defers = [];

    public function __construct(
        public readonly string $code,
        public readonly Line   $line,
    ) {}

    /**
     * @param Context $ctx
     * @return void
     * @throws ExecutionError
     */
    public function run(Context $ctx): void
    {
        $ctx->logger->eval()->start($this);
        try {
            eval($this->code);
            $ctx->logger->eval()->success($this);
        } catch (Throwable $e) {
            throw new EvaluationError("Code evaluation error. Reason: " . $e->getMessage(), $this, $e->getCode(), $e);
        }
    }

    public function curdir(): string
    {
        return dirname($this->line->path);
    }

    public function defer(callable $defer): void
    {
        self::$defers[] = $defer;
    }

    public function __destruct()
    {
        foreach (self::$defers as $defer) {
            $defer();
        }
    }
}