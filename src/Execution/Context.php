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

use DOMNode;
use Flow\JSONPath\JSONPath;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SetCookie;
use JsonException;
use PhpOption\Option;
use Pingframework\DotRestPhp\Config;
use Pingframework\DotRestPhp\Exception\ContextError;
use Pingframework\DotRestPhp\Output\Logger;
use Pingframework\DotRestPhp\Utils\DateTimeInterval;
use Pingframework\Streams\Stream;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

/**
 * @author    Oleg Bronzov <oleg.bronzov@gmail.com>
 * @copyright 2022
 * @license   https://opensource.org/licenses/MIT  The MIT License
 */
class Context
{
    public const FUNCTIONS_PATTERN = 'env|var|unset|config|status|body|header|cookie|jsonbody|jsonpath|xpath|duration';

    private const CACHE_KEY_RESPONSE = '__RESPONSE__';
    private const CACHE_KEY_BODY     = '__BODY__';
    private const CACHE_KEY_JSONBODY = '__JSONBODY__';

    private ?Client $client = null;

    public function __construct(
        public readonly Config  $config,
        public readonly Logger  $logger,
        public array            $vars = [],
        public DateTimeInterval $durationStartedAt = new DateTimeInterval(),
    ) {}

    public function getClient(): Client
    {
        if ($this->client === null) {
            $this->client = new Client($this->config->buildGuzzleConfigMap());
        }

        return $this->client;
    }

    public function duration(string $format = '%s.%f sec'): string
    {
        $d = $this->durationStartedAt->duration($format);
        $this->durationStartedAt = new DateTimeInterval();
        return $d;
    }

    /**
     * @throws ContextError
     */
    public function var(string $name, mixed $value = null): mixed
    {
        // getter
        if ($value === null) {
            if (!isset($this->vars[$name])) {
                throw new ContextError("Undefined variable: $name");
            }
            return $this->vars[$name];
        }

        // setter
        $this->vars[$name] = $value;
        return $value;
    }

    public function has(string $name): bool
    {
        return isset($this->vars[$name]);
    }

    public function hasResponse(): bool
    {
        return isset($this->vars[self::CACHE_KEY_RESPONSE]);
    }

    /**
     * @throws ContextError
     */
    public function config(string $name, mixed $value): mixed
    {
        if (!property_exists($this->config, $name)) {
            throw new ContextError("Undefined config variable: $name");
        }

        // getter
        if ($value === null) {
            return $this->config->{$name};
        }

        // setter
        $this->config->{$name} = $value;
        return $value;
    }

    public function unset(string $name): void
    {
        unset($this->vars[$name]);
    }

    public function cleanCache(): void
    {
        $this->unset(self::CACHE_KEY_BODY);
        $this->unset(self::CACHE_KEY_JSONBODY);
    }

    /**
     * @throws ContextError
     */
    public function response(?ResponseInterface $response = null): ResponseInterface
    {
        // getter
        if ($response === null) {
            return $this->var(self::CACHE_KEY_RESPONSE);
        }

        // setter
        return $this->var(self::CACHE_KEY_RESPONSE, $response);
    }

    public function env(string $name, array|string|false|null $default = null): array|string|false|null
    {
        return getenv($name) ?: $default;
    }

    /**
     * @throws ContextError
     */
    public function header(string $name): string
    {
        return $this->response()->getHeaderLine($name);
    }

    /**
     * @throws ContextError
     */
    public function cookie(string $name, string $attribute = 'value'): string|int|bool|null
    {
        foreach ($this->response()->getHeader('Set-Cookie') as $header) {
            $cookie = SetCookie::fromString($header);

            if ($cookie->getName() === $name) {
                return match ($attribute) {
                    'value'    => $cookie->getValue(),
                    'domain'   => $cookie->getDomain(),
                    'path'     => $cookie->getPath(),
                    'expires'  => $cookie->getExpires(),
                    'max-age'  => $cookie->getMaxAge(),
                    'secure'   => $cookie->getSecure(),
                    'httponly' => $cookie->getHttpOnly(),
                    'exists'   => true,
                    default    => throw new ContextError("Undefined cookie attribute: $attribute"),
                };
            }
        }

        return false;
    }

    /**
     * @throws ContextError
     */
    public function status(): int
    {
        return $this->response()->getStatusCode();
    }

    /**
     * @throws ContextError
     */
    public function body(): string
    {
        if (!isset($this->vars[self::CACHE_KEY_BODY])) {
            return $this->var(self::CACHE_KEY_BODY, (string)$this->response()->getBody());
        }

        return $this->var(self::CACHE_KEY_BODY);
    }

    /**
     * @throws ContextError
     */
    public function jsonbody(string $extract = "all"): mixed
    {
        if (!isset($this->vars[self::CACHE_KEY_JSONBODY])) {
            try {
                $json = json_decode($this->body(), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new ContextError("Can't decode json body: {$e->getMessage()}");
            }

            if (!is_array($json)) {
                $json = [];
            }

            $this->var(self::CACHE_KEY_JSONBODY, Stream::of($json));
        }

        $stream = $this->var(self::CACHE_KEY_JSONBODY);
        return $this->extractFromStream($stream, $extract, $stream->size() > 0);
    }

    /**
     * @throws ContextError
     */
    public function jsonpath(string $selector, string $extract = "text"): mixed
    {
        try {
            $jsonPath = (new JSONPath($this->jsonbody()))->find($selector);
            $exists = $jsonPath->valid();
            return $this->extractFromStream(
                Stream::of($jsonPath)
                    ->map(fn(mixed $jsonPath) => $jsonPath instanceof JSONPath ? $jsonPath->getData() : $jsonPath),
                $extract,
                $exists,
            );
        } catch (Throwable $e) {
            throw new ContextError($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws ContextError
     */
    private function extractFromStream(Stream $stream, string $extract, bool $exists): mixed
    {
        try {
            $arrayConverter = fn(array $array): string => json_encode($array, JSON_THROW_ON_ERROR);
            $textExtractor = fn() => match ($stream->size()) {
                0       => null,
                1       => $stream
                    ->first()
                    ->orElse(Option::fromValue(null, false))
                    ->map(fn(mixed $value) => is_array($value) ? $arrayConverter($value) : $value)
                    ->get(),
                default => $stream->collect($arrayConverter)
            };

            return match ($extract) {
                'text'          => $textExtractor(),
                'first'         => $stream->first()->getOrElse(null),
                'last'          => $stream->last()->getOrElse(null),
                'all'           => $stream->toMap(),
                'count'         => $stream->size(),
                'len', 'length' => strlen((string)$textExtractor()),
                'exists'        => $exists,
                default         => throw new ContextError("Unknown (json/x)path result type: $extract"),
            };
        } catch (Throwable $e) {
            throw new ContextError($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws ContextError
     */
    public function xpath(string $xpath, string $extract = "text"): mixed
    {
        try {
            $crawler = (new Crawler($this->body()))->evaluate($xpath);
        } catch (Throwable $e) {
            throw new ContextError($e->getMessage(), $e->getCode(), $e);
        }

        $stream = Stream::of(is_array($crawler) ? $crawler : $crawler->getIterator())
            ->map(fn(mixed $v): mixed => $v instanceof DOMNode ? $v->textContent : $v);

        return $this->extractFromStream($stream, $extract, $stream->size() > 0);
    }
}