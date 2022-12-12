<?php

namespace Pingframework\DotRestPhp\Tests\Execution;

use PHPUnit\Framework\TestCase;
use Pingframework\DotRestPhp\Config;
use Pingframework\DotRestPhp\Execution\Context;
use Pingframework\DotRestPhp\Output\Console\ConsoleLogger;
use Pingframework\DotRestPhp\Parsing\AssertParser;
use Pingframework\DotRestPhp\Parsing\CodeParser;
use Pingframework\DotRestPhp\Parsing\CommentParser;
use Pingframework\DotRestPhp\Parsing\ConfigParser;
use Pingframework\DotRestPhp\Parsing\IncludeParser;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;
use Pingframework\DotRestPhp\Parsing\Request\BodyReader;
use Pingframework\DotRestPhp\Parsing\Request\HeadersReader;
use Pingframework\DotRestPhp\Parsing\Request\OptionsReader;
use Pingframework\DotRestPhp\Parsing\RequestParser;
use Pingframework\DotRestPhp\Parsing\VarParser;
use Pingframework\DotRestPhp\Reading\LinearFileReader;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class RequestRunnerTest extends TestCase
{
    public function testRun()
    {
        $fr = new LinearFileReader(__DIR__ . '/../request2.rest.php');
        $pr = new ParserRegistry(
            new ConfigParser(),
            new IncludeParser(),
            new CommentParser(),
            new AssertParser(),
            new RequestParser(
                new HeadersReader(),
                new OptionsReader(),
                new BodyReader(),
            ),
            new CodeParser(),
            new VarParser(),
        );
        $ctx = new Context(
            new Config(),
            ConsoleLogger::build(new SymfonyStyle(new StringInput(''), new BufferedOutput())),
        );

        $runners = [];
        while ($l = $fr->nextToken()) {
            $runners[] = $pr->find($l)->parse($l, $fr, $pr);
        }

        $this->expectExceptionMessage("actual value is '500'");
        foreach ($runners as $runner) {
            $runner->run($ctx);
        }
    }
}
