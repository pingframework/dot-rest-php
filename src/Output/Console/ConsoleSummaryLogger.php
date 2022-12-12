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
use Pingframework\DotRestPhp\Output\Console\Utils\ColorizerTrait;
use Pingframework\DotRestPhp\Output\SummaryLogger;
use Pingframework\DotRestPhp\Utils\DateTimeInterval;
use Pingframework\DotRestPhp\Utils\StringifierTrait;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class ConsoleSummaryLogger extends AbstractConsoleLogger implements SummaryLogger
{
    use StringifierTrait;
    use ColorizerTrait;

    public function print(string $file, DateTimeInterval $dti, Context $ctx): void
    {
        if ($this->io->isDebug()) {
            $this->io->writeln(['', '']);
            $this->io->horizontalTable([
                'Executed at',
                'Memory Usage',
                'Files included',
                'Code blocks',
                'Var setters',
                'Config setters',
                'Duration setters',
                'Comments Amount',
            ], [
                [
                    $dti->startedAt->format('r'),
                    sprintf('%s MB', round(memory_get_peak_usage() / 1024 / 1024, 2)),
                    $ctx->logger->include()->summary(),
                    $ctx->logger->eval()->summary(),
                    $ctx->logger->var()->summary(),
                    $ctx->logger->config()->summary(),
                    $ctx->logger->duration()->summary(),
                    $ctx->logger->comment()->summary(),
                ],
            ]);

            $this->io->horizontalTable([
                'failOnAssertionError',
                'baseUri',
                'allowRedirects',
                'auth',
                'sert',
                'connectionTimeout',
                'decodeContent',
                'dilay',
                'idnConversion',
                'proxy',
                'sslKey',
                'stream',
                'verify',
                'timeout',
                'readTimeout',
                'version',
                'forceIpResolve',
            ], [
                [
                    $this->stringify($ctx->config->failOnAssertionError),
                    $this->stringify($ctx->config->baseUri),
                    $this->stringify($ctx->config->allowRedirects),
                    $this->stringify($ctx->config->auth),
                    $this->stringify($ctx->config->sert),
                    $this->stringify($ctx->config->connectionTimeout),
                    $this->stringify($ctx->config->decodeContent),
                    $this->stringify($ctx->config->dilay),
                    $this->stringify($ctx->config->idnConversion),
                    $this->stringify($ctx->config->proxy),
                    $this->stringify($ctx->config->sslKey),
                    $this->stringify($ctx->config->stream),
                    $this->stringify($ctx->config->verify),
                    $this->stringify($ctx->config->timeout),
                    $this->stringify($ctx->config->readTimeout),
                    $this->stringify($ctx->config->version),
                    $this->stringify($ctx->config->forceIpResolve),
                ],
                [
                    "Fail on first assertion error exception",
                    "Base URI of the client that is merged into relative URIs.\nWhen a relative URI is provided to a client,\nthe client will combine the base URI with the relative URI",
                    "(bool|array) Controls redirect behavior. \nPass false to disable redirects, pass true to enable redirects, \npass an associative to provide custom redirect settings. \nDefaults to “false”. This option only works if your handler has the RedirectMiddleware. \nWhen passing an associative array, you can provide the following key value pairs:\n * max: (int, default=5) maximum number of allowed redirects.\n * strict: (bool, default=false) Set to true to use strict redirects\n           meaning redirect POST requests with POST requests vs. doing what most browsers\n           do which is redirect POST requests with GET requests\n * referer: (bool, default=false) Set to true to enable the Referer header.\n * protocols: (array, default=['http', 'https']) Allowed redirect protocols.\n * on_redirect: (callable) PHP callable that is invoked when a redirect is encountered.\n                The callable is invoked with the request, the redirect response that was received,\n                and the effective URI. Any return value from the on_redirect function is ignored.",
                    "auth: (array) Pass an array of HTTP authentication parameters to use with the request.\nThe array must contain the username in index [0], the password in index [1],\nand you can optionally provide a built-in authentication type in index [2].\nPass null to disable authentication for a request.",
                    "cert: (string|array) Set to a string to specify the path to a file containing\na PEM formatted SSL client side certificate.\nIf a password is required, then set cert to an array containing\nthe path to the PEM file in the first array element followed\nby the certificate password in the second array element.",
                    "connect_timeout: (float, default=0) Float describing the number of seconds to wait\nwhile trying to connect to a server.\nUse 0 to wait indefinitely (the default behavior).",
                    "decode_content: (bool, default=true) Specify whether or not Content-Encoding\nresponses (gzip, deflate, etc.) are automatically decoded.",
                    "delay: (int) Number of milliseconds to delay before sending the request.",
                    "idn: (bool|int, default=true) A combination of IDNA_* constants\nfor idn_to_ascii() PHP's function (see “options” parameter).\nSet to false to disable IDN support completely,\nor to true to use the default configuration (IDNA_DEFAULT constant).",
                    "proxy: (string|array) Pass a string to specify an HTTP proxy,\nor an array to specify different proxies for different protocols\n(where the key is the protocol and the value is a proxy string).",
                    "ssl_key: (array|string) Specify the path to a file containing\na private SSL key in PEM format.\nIf a password is required, then set to an array containing\nthe path to the SSL key in the first array element followed\nby the password required for the certificate in the second element.",
                    "stream: Set to true to attempt to stream a response rather than download it all up-front.",
                    "verify: (bool|string, default=true) Describes the SSL certificate verification behavior of a request.\nSet to true to enable SSL certificate verification using the system CA bundle when available (the default).\nSet to false to disable certificate verification (this is insecure!).\nSet to a string to provide the path to a CA bundle on disk to enable verification using a custom certificate.",
                    "timeout: (float, default=0) Float describing the timeout of the request in seconds.\nUse 0 to wait indefinitely (the default behavior).",
                    "read_timeout: (float, default=default_socket_timeout ini setting) Float describing the body read timeout,\nfor stream requests.",
                    "version: (float) Specifies the HTTP protocol version to attempt to use.",
                    "force_ip_resolve: (bool) Force client to use only ipv4 or ipv6 protocol",
                ],
            ]);
        }

        $summary = sprintf(
            " %s: %s (%d request(s), %s assertions failed) in %s",
            basename($file),
            $ctx->logger->assertion()->getFailedCount() > 0 ? $this->colorize('Failed', 'red') : $this->colorize('Passed', 'green'),
            $ctx->logger->httpClient()->summary(),
            $this->colorize($ctx->logger->assertion()->getFailedCount() . '/' . $ctx->logger->assertion()->getTotalCount(), $ctx->logger->assertion()->getFailedCount() > 0 ? 'red' : 'green'),
            $dti->duration('%I:%S.%f')
        );

        $this->io->writeln([
            '',
            str_repeat('~', strlen($summary)),
            $summary,
        ]);
    }
}