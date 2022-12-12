<?php

namespace Pingframework\DotRestPhp\Tests\Execution;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Pingframework\DotRestPhp\Config;
use Pingframework\DotRestPhp\Execution\Context;
use Pingframework\DotRestPhp\Output\Console\ConsoleLogger;
use Pingframework\DotRestPhp\Parsing\AssertParser;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;
use Pingframework\DotRestPhp\Reading\LinearFileReader;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class AssertRunnerTest extends TestCase
{
    public function testJsonpath()
    {
        $json = '{"results": [{"id": 42, "bool_var": true, "float_var": 3.14, "string_var": "foo", "null_var": null, "array_var": [1, 2, 3], "sha256_var": "123", "md5_var": "123"}]}';
        $fr = new LinearFileReader(__DIR__ . '/../asserts_jsonpath.rest.php');
        $pr = new ParserRegistry(
            new AssertParser()
        );
        $response = new Response(200, [], $json);
        $ctx = new Context(
            new Config(),
            ConsoleLogger::build(new SymfonyStyle(new StringInput(''), new BufferedOutput())),
            ['__RESPONSE__' => $response]
        );

        $runners = [];
        while ($l = $fr->nextToken()) {
            $runners[] = $pr->find($l)->parse($l, $fr, $pr);
        }

        $this->expectExceptionMessage("actual value is 'false'");
        foreach ($runners as $runner) {
            $runner->run($ctx);
        }
    }

    public function testXpath()
    {
        $fr = new LinearFileReader(__DIR__ . '/../asserts_xpath.rest.php');
        $pr = new ParserRegistry(
            new AssertParser()
        );
        $response = new Response(200, [], file_get_contents(__DIR__ . '/../test.html'));
        $ctx = new Context(
            new Config(),
            ConsoleLogger::build(new SymfonyStyle(new StringInput(''), new BufferedOutput())),
            ['__RESPONSE__' => $response]
        );

        $runners = [];
        while ($l = $fr->nextToken()) {
            $runners[] = $pr->find($l)->parse($l, $fr, $pr);
        }

        $this->expectExceptionMessage("actual value is 'false'");
        foreach ($runners as $runner) {
            $runner->run($ctx);
        }
    }
}
