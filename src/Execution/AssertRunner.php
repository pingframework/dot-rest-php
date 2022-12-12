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

use Pingframework\DotRestPhp\Exception\AssertionError;
use Pingframework\DotRestPhp\Exception\ExecutionError;
use Pingframework\DotRestPhp\Exception\SyntaxError;
use Pingframework\DotRestPhp\Reading\Line;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class AssertRunner implements Runner
{
    public function __construct(
        public readonly Line   $line,
        public readonly Value  $leftOperand,
        public readonly Value  $rightOperand,
        public readonly string $operator,
    ) {}

    /**
     * @param Context $ctx
     * @return void
     * @throws ExecutionError
     * @throws SyntaxError
     */
    public function run(Context $ctx): void
    {
        try {
            $expected = $this->rightOperand->resolve($this->line, $ctx);
            $actual = $this->leftOperand->resolve($this->line, $ctx);

            $result = match (strtolower($this->operator)) {
                '==='        => $this->assertSame($ctx),
                '!=='        => $this->assertNotSame($ctx),
                '=='         => $this->assertEq($ctx),
                '!=', '<>'   => $this->assertNotEq($ctx),
                '>'          => $this->assertGt($ctx),
                '>='         => $this->assertGte($ctx),
                '<'          => $this->assertLt($ctx),
                '<='         => $this->assertLte($ctx),
                'in'         => $this->assertIn($ctx),
                'nin'        => $this->assertNin($ctx),
                'isint'      => $this->assertIsInt($ctx),
                'isstring'   => $this->assertIsString($ctx),
                'isbool'     => $this->assertIsBool($ctx),
                'isarray'    => $this->assertIsArray($ctx),
                'isfloat'    => $this->assertIsFloat($ctx),
                'contains'   => $this->assertContains($ctx),
                'startswith' => $this->assertStartsWith($ctx),
                'endswith'   => $this->assertEndsWith($ctx),
                'regex'      => $this->assertRegex($ctx),
                'sha256'     => $this->assertSHA256($ctx),
                'md5'        => $this->assertMD5($ctx),
                default      => throw new SyntaxError("Unknown operator: {$this->operator}", $this->line),
            };

            assert(
                $result,
                new AssertionError(
                    sprintf("actual value is '%s'", var_export($actual, true)),
                    $this->line,
                    $expected,
                    $actual,
                ),
            );

            $ctx->logger->assertion()->success($this, $expected, $actual);
        } catch (AssertionError $e) {
            if ($ctx->config->failOnAssertionError) {
                throw $e;
            } else {
                $ctx->logger->assertion()->error($this, $e);
            }
        } catch (SyntaxError $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new ExecutionError($e->getMessage(), $this->line, $e->getCode(), $e);
        }
    }

    /**
     * @throws SyntaxError
     */
    private function assertSame(Context $ctx): bool
    {
        return $this->leftOperand->resolve($this->line, $ctx) === $this->rightOperand->resolve($this->line, $ctx);
    }

    /**
     * @throws SyntaxError
     */
    private function assertNotSame(Context $ctx): bool
    {
        return $this->leftOperand->resolve($this->line, $ctx) !== $this->rightOperand->resolve($this->line, $ctx);
    }

    /**
     * @throws SyntaxError
     */
    private function assertEq(Context $ctx): bool
    {
        return $this->leftOperand->resolve($this->line, $ctx) == $this->rightOperand->resolve($this->line, $ctx);
    }

    /**
     * @throws SyntaxError
     */
    private function assertNotEq(Context $ctx): bool
    {
        return $this->leftOperand->resolve($this->line, $ctx) != $this->rightOperand->resolve($this->line, $ctx);
    }

    /**
     * @throws SyntaxError
     */
    private function assertGt(Context $ctx): bool
    {
        return $this->leftOperand->resolve($this->line, $ctx) > $this->rightOperand->resolve($this->line, $ctx);
    }

    /**
     * @throws SyntaxError
     */
    private function assertGte(Context $ctx): bool
    {
        return $this->leftOperand->resolve($this->line, $ctx) >= $this->rightOperand->resolve($this->line, $ctx);
    }

    /**
     * @throws SyntaxError
     */
    private function assertLt(Context $ctx): bool
    {
        return $this->leftOperand->resolve($this->line, $ctx) < $this->rightOperand->resolve($this->line, $ctx);
    }

    /**
     * @throws SyntaxError
     */
    private function assertLte(Context $ctx): bool
    {
        return $this->leftOperand->resolve($this->line, $ctx) <= $this->rightOperand->resolve($this->line, $ctx);
    }

    /**
     * @throws ExecutionError
     * @throws SyntaxError
     */
    private function assertIn(Context $ctx): bool
    {
        $rightOperand = $this->rightOperand->resolve($this->line, $ctx);
        if (!is_array($rightOperand)) {
            throw new ExecutionError('Right operand of "in" operator must be an array', $this->line);
        }
        return in_array($this->leftOperand->resolve($this->line, $ctx), $rightOperand);
    }

    /**
     * @throws ExecutionError
     * @throws SyntaxError
     */
    private function assertNin(Context $ctx): bool
    {
        $rightOperand = $this->rightOperand->resolve($this->line, $ctx);
        if (!is_array($rightOperand)) {
            throw new ExecutionError('Right operand of "in" operator must be an array', $this->line);
        }
        return !in_array($this->leftOperand->resolve($this->line, $ctx), $rightOperand);
    }

    private function assertIsInt(Context $ctx): bool
    {
        $result = $this->leftOperand->resolve($this->line, $ctx);

        if (is_int($result)) {
            return true;
        }

        return is_numeric($result) && (int)$result == $result;
    }

    private function assertIsFloat(Context $ctx): bool
    {
        $result = $this->leftOperand->resolve($this->line, $ctx);

        if (is_float($result)) {
            return true;
        }

        return is_numeric($result) && str_contains($result, '.');
    }

    private function assertIsBool(Context $ctx): bool
    {
        $result = $this->leftOperand->resolve($this->line, $ctx);

        if (is_bool($result)) {
            return $result;
        }

        return is_string($result) && in_array(strtolower($result), ['true', 'false', 1, 0, true, false]);
    }

    private function assertIsArray(Context $ctx): bool
    {
        return is_array($this->leftOperand->resolve($this->line, $ctx));
    }

    private function assertIsString(Context $ctx): bool
    {
        return is_string($this->leftOperand->resolve($this->line, $ctx));
    }

    private function assertContains(Context $ctx): bool
    {
        $leftOperand = $this->leftOperand->resolve($this->line, $ctx);
        $rightOperand = $this->rightOperand->resolve($this->line, $ctx);
        return is_string($leftOperand) && is_string($rightOperand) && str_contains($leftOperand, $rightOperand);
    }

    private function assertStartsWith(Context $ctx): bool
    {
        $leftOperand = $this->leftOperand->resolve($this->line, $ctx);
        $rightOperand = $this->rightOperand->resolve($this->line, $ctx);
        return is_string($leftOperand) && is_string($rightOperand) && str_starts_with($leftOperand, $rightOperand);
    }

    private function assertEndsWith(Context $ctx): bool
    {
        $leftOperand = $this->leftOperand->resolve($this->line, $ctx);
        $rightOperand = $this->rightOperand->resolve($this->line, $ctx);
        return is_string($leftOperand) && is_string($rightOperand) && str_ends_with($leftOperand, $rightOperand);
    }

    private function assertRegex(Context $ctx): bool
    {
        $leftOperand = $this->leftOperand->resolve($this->line, $ctx);
        $rightOperand = $this->rightOperand->resolve($this->line, $ctx);
        return is_string($leftOperand) && is_string($rightOperand) && preg_match($rightOperand, $leftOperand) === 1;
    }

    private function assertSHA256(Context $ctx): bool
    {
        $leftOperand = $this->leftOperand->resolve($this->line, $ctx);
        $rightOperand = $this->rightOperand->resolve($this->line, $ctx);
        return is_string($leftOperand) && is_string($rightOperand) && hash_equals(
                $rightOperand,
                hash('sha256', $leftOperand),
            );
    }

    private function assertMD5(Context $ctx): bool
    {
        $leftOperand = $this->leftOperand->resolve($this->line, $ctx);
        $rightOperand = $this->rightOperand->resolve($this->line, $ctx);
        return is_string($leftOperand) && is_string($rightOperand) && hash_equals($rightOperand, md5($leftOperand));
    }
}