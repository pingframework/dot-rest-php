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

use Pingframework\DotRestPhp\Exception\EvaluationError;
use Pingframework\DotRestPhp\Exception\FileError;
use Pingframework\DotRestPhp\Exception\LinearError;
use Pingframework\DotRestPhp\Output\Console\Utils\ColorizerTrait;
use Pingframework\DotRestPhp\Output\ErrorLogger;
use Pingframework\Streams\Stream;
use ReflectionClass;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class ConsoleErrorLogger extends AbstractConsoleLogger implements ErrorLogger
{
    use ColorizerTrait;

    public function print(Throwable $error): void
    {
        if ($this->io->isQuiet()) {
            return;
        }

        $this->io->error((new ReflectionClass($error))->getShortName() . ': ' . $error->getMessage());

        if (!$this->io->isVerbose()) {
            return;
        }

        match (true) {
            $error instanceof EvaluationError => $this->printLinesBlock(
                Stream::ofString(PHP_EOL, $error->codeBlock->code)
                    ->remapBy(fn(string $v, int $i): int => $i + $error->getSourceFileLine()->num)
                    ->toMap(),
                $error->getSourceFileLine()->path,
                $error->getSourceFileLine()->num,
                null,
            ),
            $error instanceof LinearError     => $this->printLinesBlock(
                $this->extractLines(
                    $error->getSourceFileLine()->path,
                    $error->getSourceFileLine()->num,
                ),
                $error->getSourceFileLine()->path,
                $error->getSourceFileLine()->num,
                $error->getMessage(),
            ),
            default => $this->printErrorBlock($error),
        };
    }

    private function printErrorBlock(Throwable $error): void
    {
        $this->io->error($error->getTraceAsString());
    }

    private function printLinesBlock(array $lines, string $file, int $errorLineNum, ?string $error): void
    {
        $this->io->write(sprintf("      --> %s:%d\n", realpath($file), $errorLineNum));

        $mask = "%5s | %-80s\n";

        $this->io->write(sprintf($mask, '', $this->colorize('...', 'gray')));
        foreach ($lines as $lineNum => $line) {
            $l = trim($line, "\n\r");
            $this->io->write(sprintf($mask, $lineNum, $this->colorize($l, $error !== null && $lineNum === $errorLineNum ? 'white' : 'gray')));
            if ($error !== null && $lineNum === $errorLineNum) {
                $this->io->write(
                    sprintf($mask, '', $this->colorize(str_repeat("^", strlen($l)) . " " . str_replace("\n", '\n', $error), 'red')),
                );
            }
        }
        $this->io->writeln(sprintf($mask, '', $this->colorize('...', 'gray')));
    }

    /**
     * @throws FileError
     */
    private function extractLines(string $path, int $targetNum): array
    {
        if (!is_readable($path)) {
            throw new FileError("File '$path' is not readable");
        }

        $lines = [];
        $num = 0;
        $file = fopen($path, 'r');
        while (($line = fgets($file)) !== false) {
            $num++;
            if ($num >= $targetNum - 4 && $num <= $targetNum + 4) {
                $lines[$num] = $line;
            }
        }
        fclose($file);

        return $lines;
    }
}