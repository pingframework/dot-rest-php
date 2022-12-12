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

use Pingframework\DotRestPhp\Exception\HttpClientError;
use Pingframework\DotRestPhp\Output\Console\Utils\ColorizerTrait;
use Pingframework\DotRestPhp\Output\HttpClientLogger;
use Pingframework\DotRestPhp\Utils\StringifierTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class ConsoleHttpClientLogger extends AbstractConsoleLogger implements HttpClientLogger
{
    use ColorizerTrait;
    use StringifierTrait;

    private int                $requestCounter = 0;
    private ?ResponseInterface $lastResponse   = null;

    private int $headerLength = 0;

    public function request(string $method, string $uri, array $options): void
    {
        $this->requestCounter++;
        $this->headerLength = 0;

        if (!$this->io->isVerbose()) {
            return;
        }

        $mask = "<fg=#ffa500;options=bold>%s</> <fg=bright-cyan;options=bold>%s</>";
        $str = sprintf($mask, $method, $uri);
        $this->io->writeln("\n");
        $this->io->write($str);
        $this->headerLength += strlen(sprintf("%s %s", $method, $uri));

        if ($this->io->isDebug()) {
            $this->io->block('OPTIONS = ' . $this->colorize($this->stringifyArray($options, true), 'yellow'));
        }
    }

    public function response(ResponseInterface $response): void
    {
        $this->lastResponse = $response;

        if (!$this->io->isVerbose() || $this->io->isDebug()) {
            return;
        }

        $code = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $color = match (true) {
            $code >= 200 && $code < 300 => 'green',
            $code >= 300 && $code < 400 => 'yellow',
            $code >= 400 && $code < 500 => 'bright-red',
            $code >= 500 && $code < 600 => 'red',
            default                     => 'white',
        };

        $maxLen = 80;
        $separatorLength = $this->headerLength > $maxLen ? 3 : $maxLen - $this->headerLength;
        $this->headerLength += $separatorLength;
        $this->io->write(str_repeat('.', $separatorLength));

        $this->io->writeln(sprintf("<fg=%s>%s %s</>", $color, $code, $reason));
        $this->headerLength += strlen(sprintf("%s %s", $color, $reason));
        $this->io->writeln(str_repeat('=', $this->headerLength));
    }

    public function printLatestBody(): void
    {
        $this->io->writeln((string)$this->lastResponse?->getBody());
    }

    public function error(HttpClientError $error): void
    {
        $this->io->writeln("<fg=red;options=bold>FAILED!</>");
    }

    public function summary(): string
    {
        return (string)$this->requestCounter;
    }
}