<?php

namespace Pingframework\DotRestPhp\Utils;

use Pingframework\DotRestPhp\Exception\SyntaxError;
use Pingframework\DotRestPhp\Execution\Context;
use Pingframework\DotRestPhp\Execution\Value;
use Pingframework\DotRestPhp\Reading\Line;

trait PlaceholderTrait
{
    /**
     * @param Line    $l
     * @param string  $token
     * @param Context $ctx
     * @return string
     * @throws SyntaxError
     */
    public function replacePlaceholders(string $token, Context $ctx, Line $l): string
    {
        return preg_replace_callback_array([
            '/\{\{\w+\}\}/'                                                           => fn(
                array $matches
            ): string => (new Value($matches[0]))->resolve($l, $ctx),
            '/\{\s*(' . Context::FUNCTIONS_PATTERN . ').*?\}/' => fn(
                array $matches
            ): string => (new Value($matches[0]))->resolve($l, $ctx),
        ], $token);
    }
}