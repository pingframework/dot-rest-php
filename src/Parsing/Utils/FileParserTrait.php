<?php

namespace Pingframework\DotRestPhp\Parsing\Utils;

use Pingframework\DotRestPhp\Exception\FileError;
use Pingframework\DotRestPhp\Exception\SyntaxError;
use Pingframework\DotRestPhp\Execution\Runner;
use Pingframework\DotRestPhp\Reading\LinearFileReader;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;

trait FileParserTrait
{
    /**
     * @param string         $path
     * @param ParserRegistry $pr
     * @return array<Runner>
     * @throws FileError
     * @throws SyntaxError
     */
    public function parseFile(string $path, ParserRegistry $pr): array
    {
        $r = new LinearFileReader($path);

        $runners = [];
        while ($l = $r->nextToken()) {
            $runners[] = $pr->find($l)->parse($l, $r, $pr);
        }

        return $runners;
    }
}