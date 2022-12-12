<?php

namespace Pingframework\DotRestPhp\Tests\Execution;

use PHPUnit\Framework\TestCase;
use Pingframework\DotRestPhp\Config;
use Pingframework\DotRestPhp\Execution\Context;
use Pingframework\DotRestPhp\Output\Console\ConsoleLogger;
use Pingframework\DotRestPhp\Parsing\ConfigParser;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;
use Pingframework\DotRestPhp\Reading\LinearFileReader;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConfigRunnerTest extends TestCase
{
    public function testRun()
    {
        $fr = new LinearFileReader(__DIR__ . '/../config.rest.php');
        $pr = new ParserRegistry(
            new ConfigParser()
        );
        $ctx = new Context(
            new Config(),
            ConsoleLogger::build(new SymfonyStyle(new StringInput(''), new BufferedOutput())),
        );

        $runners = [];
        while ($l = $fr->nextToken()) {
            $runners[] = $pr->find($l)->parse($l, $fr, $pr);
        }

        $this->assertTrue($ctx->config->failOnAssertionError);
        $this->assertNull($ctx->config->auth);
        foreach ($runners as $runner) {
            $runner->run($ctx);
        }
        $this->assertIsArray($ctx->config->auth);
        $this->assertEquals('my user with space', $ctx->config->auth[0]);
        $this->assertEquals('my password with , comma', $ctx->config->auth[1]);
        $this->assertEquals('digest', $ctx->config->auth[2]);
    }
}
