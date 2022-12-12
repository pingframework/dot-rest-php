<?php

namespace Pingframework\DotRestPhp\Utils;

trait StringifierTrait
{
    public function stringify(mixed $value): string
    {
        return match (true) {
            is_bool($value)                  => $value ? 'true' : 'false',
            is_string($value)                => $value,
            is_array($value)                 => $this->stringifyArray($value),
            is_int($value), is_float($value) => (string)$value,
            is_null($value)                  => 'null',
            is_object($value)                => sprintf('[%s]', get_class($value)),
            default                          => 'unknown',
        };
    }

    public function stringifyArray(array $array, bool $multiline = false): string
    {
        $export = var_export($array, true);
        $patterns = [
            "/array \(/" => '[',
            "/^([ ]*)\)(,?)$/m" => '$1]$2',
            "/=>[ ]?\n[ ]+\[/" => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];
        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);

        if ($multiline) {
            return $export;
        }

        return preg_replace("/\n[ ]+/", ' ', $export);
    }
}