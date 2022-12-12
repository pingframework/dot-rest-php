<?php

namespace Pingframework\DotRestPhp\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Pingframework\DotRestPhp\Execution\RequestRunner;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;
use Pingframework\DotRestPhp\Parsing\Request\BodyReader;
use Pingframework\DotRestPhp\Parsing\Request\HeadersReader;
use Pingframework\DotRestPhp\Parsing\Request\OptionsReader;
use Pingframework\DotRestPhp\Parsing\RequestParser;
use Pingframework\DotRestPhp\Reading\LinearFileReader;

class RequestParserTest extends TestCase
{
    public function testParseGet1()
    {
        $pr = new ParserRegistry(
            new RequestParser(
                new HeadersReader(),
                new OptionsReader(),
                new BodyReader(),
            )
        );
        $fr = new LinearFileReader(__DIR__ . '/../request1.rest.php');
        $l = $fr->next();
        $runner = $pr->find($l)->parse($l, $fr, $pr);

        $this->assertInstanceOf(RequestRunner::class, $runner);
        $this->assertEquals(1, $runner->line->num);
        $this->assertEquals("POST /api/v1/users\n", $runner->line->content);
        $this->assertEquals(__DIR__ . '/../request1.rest.php', $runner->line->path);
        $this->assertEquals('POST', $runner->method);
        $this->assertEquals('/api/v1/users', $runner->uri);
        $this->assertEquals([
            'headers' => ['Authorization' => 'Bearer {{token}}'],
        ], $runner->headers);
        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'qux',
        ], $runner->options);
        $this->assertIsString($runner->body);
        $this->assertEquals("{\n    \"foo\": \"bar\"\n}", $runner->body);
    }
}
