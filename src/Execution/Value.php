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

use Pingframework\DotRestPhp\Exception\ContextError;
use Pingframework\DotRestPhp\Exception\SyntaxError;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Utils\PlaceholderTrait;
use Pingframework\Streams\Helpers\func;
use Pingframework\Streams\Stream;
use Pingframework\Streams\StreamPipeline;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class Value
{
    use PlaceholderTrait;

    private mixed $resolved = null;

    public function __construct(
        public readonly string $expression,
    ) {}

    public static function of(string $expression): self
    {
        return new self($expression);
    }

    /**
     * @throws SyntaxError
     */
    public function resolve(Line $l, Context $ctx): mixed
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $defer = function (mixed $value): mixed {
            $this->resolved = $value;
            return $value;
        };

        try {
            return match (true) {
                $this->isNull()        => $defer($this->toNull()),
                $this->isInt()         => $defer($this->toInt()),
                $this->isString()      => $defer($this->toString()),
                $this->isFloat()       => $defer($this->toFloat()),
                $this->isBool()        => $defer($this->toBool()),
                $this->isArray()       => $defer($this->toArray($l, $ctx)),
                $this->isVar()         => $defer($this->toVar($l, $ctx)),
                $this->isFunc()        => $defer($this->toFunc($l, $ctx)),
                $this->isWrappedFunc() => $defer($this->toWrappedFunc($l, $ctx)),
                $this->isFile()        => $defer($this->toFile($l, $ctx)),
                default                => $defer($this->replacePlaceholders($this->expression, $ctx, $l)),
            };
        } catch (Throwable $e) {
            throw new SyntaxError(
                sprintf('Failed to resolve value [%s]. Reason: %s', $this->expression, $e->getMessage()), $l
            );
        }
    }

    private function toNull(): mixed
    {
        return null;
    }

    private function isNull(): bool
    {
        return strtolower($this->expression) === 'null';
    }

    private function toInt(): int
    {
        return (int)$this->expression;
    }

    private function isInt(): bool
    {
        return is_numeric($this->expression) && (int)$this->expression == $this->expression;
    }

    private function toFloat(): float
    {
        return (float)$this->expression;
    }

    private function isFloat(): bool
    {
        return is_numeric($this->expression) && (float)$this->expression == $this->expression;
    }

    private function toBool(): bool
    {
        return strtolower($this->expression) === 'true';
    }

    private function isBool(): bool
    {
        return in_array(strtolower($this->expression), ['true', 'false']);
    }

    private function toString(): string
    {
        return substr($this->expression, 1, -1);
    }

    private function isString(): bool
    {
        return str_starts_with($this->expression, '"') && str_ends_with($this->expression, '"');
    }

    /**
     * @throws SyntaxError
     */
    private function toVar(Line $l, Context $ctx): mixed
    {
        try {
            return $ctx->var(trim(substr($this->expression, 2, -2)));
        } catch (ContextError $e) {
            throw new SyntaxError($e->getMessage(), $l);
        }
    }

    private function isVar(): bool
    {
        return str_starts_with($this->expression, '{{') && str_ends_with($this->expression, '}}');
    }

    private function toArray(Line $l, Context $ctx): array
    {
        return Stream::ofRegex("/(?<!\\\\),/", $this->toString())
            ->map(fn(string $token): string => trim(str_replace('\\,', ',', $token)))
            ->map(fn(string $token): array => explode('=>', $token, 2))
            ->map(
                StreamPipeline::forIterable()
                    ->map(func::unary('trim'))
                    ->map(fn(string $token): mixed => Value::of($token)->resolve($l, $ctx))
                    ->toList(),
            )
            ->remapBy(fn(array $pair, int $i): mixed => count($pair) === 2 ? $pair[0] : $i)
            ->map(fn(array $pair): mixed => count($pair) === 2 ? $pair[1] : $pair[0])
            ->toMap();
    }

    private function isArray(): bool
    {
        return str_starts_with($this->expression, '[') && str_ends_with($this->expression, ']');
    }

    /**
     * @throws SyntaxError
     */
    private function toFunc(Line $l, Context $ctx): mixed
    {
        preg_match(
            '/^(' . Context::FUNCTIONS_PATTERN . ')\s*(.*?)$/',
            $this->expression,
            $matches,
        );

        $args = [];
        if (!empty($matches[2])) {
            $stream = Stream::ofRegex("/(?<!\\\\),/", $matches[2])
                ->map(fn(string $v): mixed => (new Value(trim(str_replace('\\,', ',', $v))))->resolve($l, $ctx));
            if ($stream->size() > 2) {
                throw new SyntaxError(
                    sprintf(
                        'Too many arguments for function [%s]. NOTE! The comma character must be escaped in a string argument.',
                        $this->expression,
                    ),
                    $l
                );
            }
            $args = $stream->toList();
        }

        return call_user_func_array([$ctx, $matches[1]], $args);
    }

    private function isFunc(): bool
    {
        preg_match(
            '/^(' . Context::FUNCTIONS_PATTERN . ')/',
            $this->expression,
            $matches,
        );
        return !empty($matches);
    }

    private function isFile(): bool
    {
        return preg_match('/^\s*(?<!\\\\)<\s+(.+?)$/', $this->expression) === 1;
    }

    /**
     * @throws SyntaxError
     */
    private function toFile(Line $l, Context $ctx): string
    {
        preg_match(
            '/^\s*(?<!\\\\)<\s+(.+?)$/',
            $this->expression,
            $matches,
        );

        $file = $this->replacePlaceholders($matches[1], $ctx, $l);
        $path = $file[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $file) > 0
            ? $file
            : dirname($l->path) . DIRECTORY_SEPARATOR . $file;

        try {
            return file_get_contents($path);
        } catch (Throwable $e) {
            throw new SyntaxError($e->getMessage(), $l);
        }
    }

    /**
     * @throws SyntaxError
     */
    private function toWrappedFunc(Line $l, Context $ctx): mixed
    {
        return (new Value(trim(substr($this->expression, 1, -1))))->toFunc($l, $ctx);
    }

    private function isWrappedFunc(): bool
    {
        if (str_starts_with($this->expression, '{') && str_ends_with($this->expression, '}')) {
            return (new Value(trim(substr($this->expression, 1, -1))))->isFunc();
        }
        return false;
    }
}