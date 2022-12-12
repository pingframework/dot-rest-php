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

namespace Pingframework\DotRestPhp;

use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Config data object.
 *
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class Config
{
    /**
     * @param bool              $testMode             Test mode flag. Not returns latest response body in test mode.
     * @param bool              $failOnAssertionError Fail on first assertion error exception.
     * @param int               $verbosity            Verbosity level.
     * @param string|null       $baseUri              Base URI of the client that is merged into relative URIs.
     *                                                When a relative URI is provided to a client,
     *                                                the client will combine the base URI with the relative URI
     *                                                using the rules described in RFC 3986, section 5.2.
     * @param bool|array|null   $allowRedirects       {@see RequestOptions::ALLOW_REDIRECTS}
     * @param array|null        $auth                 {@see RequestOptions::AUTH}
     * @param array|string|null $sert                 {@see RequestOptions::CERT}
     * @param float             $connectionTimeout    {@see RequestOptions::CONNECT_TIMEOUT}
     * @param bool|null         $decodeContent        {@see RequestOptions::DECODE_CONTENT}
     * @param int|null          $dilay                {@see RequestOptions::DELAY}
     * @param bool|int|null     $expect               {@see RequestOptions::EXPECT}
     * @param bool|int|null     $idnConversion        {@see RequestOptions::IDN_CONVERSION}
     * @param string|array|null $proxy                {@see RequestOptions::PROXY}
     * @param string|array|null $sslKey               {@see RequestOptions::SSL_KEY}
     * @param bool|null         $stream               {@see RequestOptions::STREAM}
     * @param bool|null         $verify               {@see RequestOptions::VERIFY}
     * @param float             $timeout              {@see RequestOptions::TIMEOUT}
     * @param float             $readTimeout          {@see RequestOptions::READ_TIMEOUT}
     * @param float|null        $version              {@see RequestOptions::VERSION}
     * @param bool|null         $forceIpResolve       {@see RequestOptions::FORCE_IP_RESOLVE}
     */
    public function __construct(
        public bool              $testMode = false,
        public bool              $failOnAssertionError = true,
        public int               $verbosity = 0,
        public ?string           $baseUri = null,
        public bool|array|null   $allowRedirects = null,
        public ?array            $auth = null,
        public array|string|null $sert = null,
        public float             $connectionTimeout = 10.0,
        public ?bool             $decodeContent = null,
        public ?int              $dilay = null,
        public bool|int|null     $expect = null,
        public bool|int|null     $idnConversion = null,
        public string|array|null $proxy = null,
        public string|array|null $sslKey = null,
        public ?bool             $stream = null,
        public ?bool             $verify = null,
        public float             $timeout = 10.0,
        public float             $readTimeout = 10.0,
        public ?float            $version = null,
        public ?bool             $forceIpResolve = null,
    ) {}

    public function buildGuzzleConfigMap(): array
    {
        $map = [
            RequestOptions::HTTP_ERRORS     => false,
            RequestOptions::DEBUG           => $this->verbosity === OutputInterface::VERBOSITY_DEBUG,
            RequestOptions::CONNECT_TIMEOUT => $this->connectionTimeout,
            RequestOptions::TIMEOUT         => $this->timeout,
            RequestOptions::READ_TIMEOUT    => $this->readTimeout,
        ];

        $this->setup($map, $this->baseUri, 'base_uri');
        $this->setup($map, $this->allowRedirects, RequestOptions::ALLOW_REDIRECTS);
        $this->setup($map, $this->auth, RequestOptions::AUTH);
        $this->setup($map, $this->sert, RequestOptions::CERT);
        $this->setup($map, $this->decodeContent, RequestOptions::DECODE_CONTENT);
        $this->setup($map, $this->dilay, RequestOptions::DELAY);
        $this->setup($map, $this->expect, RequestOptions::EXPECT);
        $this->setup($map, $this->idnConversion, RequestOptions::IDN_CONVERSION);
        $this->setup($map, $this->proxy, RequestOptions::PROXY);
        $this->setup($map, $this->sslKey, RequestOptions::SSL_KEY);
        $this->setup($map, $this->stream, RequestOptions::STREAM);
        $this->setup($map, $this->verify, RequestOptions::VERIFY);
        $this->setup($map, $this->version, RequestOptions::VERSION);
        $this->setup($map, $this->forceIpResolve, RequestOptions::FORCE_IP_RESOLVE);

        return $map;
    }

    private function setup(array &$payload, mixed $value, string $key)
    {
        if ($value !== null) {
            $payload[$key] = $value;
        }
    }
}