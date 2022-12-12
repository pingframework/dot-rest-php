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

namespace Pingframework\DotRestPhp\Reading;

use Pingframework\DotRestPhp\Exception\FileError;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class LinearFileReader implements LinearReader
{
    private readonly mixed $file;
    private int            $lineNum      = 0;
    private ?Line          $returnedLine = null;

    /**
     * @throws FileError
     */
    public function __construct(
        public readonly string $path,
    ) {
        if (!is_readable($this->path)) {
            throw new FileError("File {$this->path} not found or not readable");
        }

        try {
            $this->file = fopen($this->path, 'r');
        } catch (Throwable $e) {
            throw new FileError("Failed to open file {$this->path}", $e->getCode(), $e);
        }

        if ($this->file === false) {
            throw new FileError("Failed to open file {$this->path}");
        }
    }

    /**
     * @throws FileError
     */
    public static function fromPath(string $path): static
    {
        return new static($path);
    }

    public function __destruct()
    {
        fclose($this->file);
    }

    public function nextToken(): Line|false
    {
        while ($l = $this->next()) {
            if (!$l->isEmpty()) {
                return $l;
            }
        }

        return false;
    }

    public function next(): Line|false
    {
        if ($this->returnedLine !== null) {
            $line = $this->returnedLine;
            $this->returnedLine = null;
            return $line;
        }

        $line = fgets($this->file);
        if ($line === false) {
            return false;
        }

        return new Line($this->path, ++$this->lineNum, $line);
    }

    public function back(Line $line): void
    {
        $this->returnedLine = $line;
    }
}