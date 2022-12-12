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

use GuzzleHttp\Exception\GuzzleException;
use Pingframework\DotRestPhp\Exception\ExecutionError;
use Pingframework\DotRestPhp\Exception\HttpClientError;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Utils\PlaceholderTrait;
use Pingframework\Streams\Stream;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class RequestRunner implements Runner
{
    use PlaceholderTrait;

    /**
     * @param Line                      $line
     * @param string                    $method
     * @param string                    $uri
     * @param array<string, string>     $headers
     * @param array<string, string>     $options
     * @param array<string, array>|null $body
     */
    public function __construct(
        public readonly Line              $line,
        public readonly string            $method,
        public readonly string            $uri,
        public readonly array             $headers = [],
        public readonly array             $options = [],
        public readonly array|string|null $body = null,
    ) {}

    /**
     * @param Context $ctx
     * @return void
     * @throws ExecutionError
     */
    public function run(Context $ctx): void
    {
        $ctx->cleanCache();

        try {
            $uri = $this->replacePlaceholders($this->uri, $ctx, $this->line);
            $options = Stream::of($this->options)
                ->merge($this->headers)
                ->merge($this->extractBody($ctx))
                ->map(fn($v) => is_array($v) ? $this->replacePlaceholdersRecursive($v, $ctx, $this->line) : $v)
                ->toMap();

            $ctx->logger->httpClient()->request($this->method, $uri, $options);

            $response = $ctx->getClient()->request(
                $this->method,
                $uri,
                $options,
            );
            $ctx->response($response);

            $ctx->logger->httpClient()->response($response);
        } catch (GuzzleException $e) {
            $err = new HttpClientError($e->getMessage(), $this->line, $e->getCode(), $e);
            $ctx->logger->httpClient()->error($err);
            throw $err;
        }
    }

    private function extractBody(Context $ctx): array
    {
        if (is_array($this->body)) {
            return $this->body;
        }

        $body = [];
        if (is_string($this->body)) {
            $body = $this->tryFile($ctx);
            if (empty($body)) {
                $body = $this->tryJson($this->replacePlaceholders($this->body, $ctx, $this->line));
            }
        }

        return $body;
    }

    private function tryFile(Context $ctx): array
    {
        if (preg_match('/^\s*(?<!\\\\)<\s+(.+?)$/', $this->body) === 1) {
            return ['body' => Value::of($this->body)->resolve($this->line, $ctx)];
        }
        return [];
    }

    private function tryJson(string $content): array
    {
        $arr = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($arr)) {
            return ['json' => $arr];
        }
        return ['body' => $content];
    }

    private function replacePlaceholdersRecursive(array $data, Context $ctx, Line $line): array
    {
        return Stream::of($data)
            ->map(fn($v) => match (true) {
                is_array($v)  => $this->replacePlaceholdersRecursive($v, $ctx, $line),
                is_string($v) => $this->replacePlaceholders($v, $ctx, $line),
                default       => $v,
            })
            ->toMap();
    }
}