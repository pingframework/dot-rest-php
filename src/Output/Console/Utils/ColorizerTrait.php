<?php

namespace Pingframework\DotRestPhp\Output\Console\Utils;

trait ColorizerTrait
{
    public function colorize(string $text, string $color): string
    {
        return sprintf(
            "%s%s\033[0m",
            match ($color) {
                'red'     => "\033[91m",
                'green'   => "\033[92m",
                'yellow'  => "\033[93m",
                'blue'    => "\033[34m",
                'magenta' => "\033[95m",
                'cyan'    => "\033[96m",
                'white'   => "\033[97m",
                'black'   => "\033[30m",
                'gray'    => "\033[90m",
                default   => "\033[0m",
            },
            $text,
        );
    }
}