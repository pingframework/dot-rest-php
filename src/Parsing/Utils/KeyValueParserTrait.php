<?php

namespace Pingframework\DotRestPhp\Parsing\Utils;

use Pingframework\DotRestPhp\Reading\LinearReader;

trait KeyValueParserTrait
{
    private function readKeyValue(
        LinearReader $r,
        string       $pattern = '/^\s*([a-zA-Z0-9-_]+)\s*:\s*(.*?)\s*$/',
        ?callable    $validator = null,
    ): array {
        $map = [];

        while ($l = $r->nextToken()) {
            $parsed = $this->parseKeyValue($l->content, $pattern);
            if ($parsed === null) {
                $r->back($l);
                return $map;
            }

            if ($validator !== null) {
                $validator($l, $parsed);
            }

            $map[$parsed[0]] = $parsed[1];
        }

        return $map;
    }

    protected function parseKeyValue(
        string $content,
        string $pattern = '/^\s*([a-zA-Z0-9-_]+)\s*:\s*(.*?)\s*$/'
    ): ?array {
        if (preg_match($pattern, $content, $matches)) {
            return [$matches[1], $matches[2]];
        }

        return null;
    }
}