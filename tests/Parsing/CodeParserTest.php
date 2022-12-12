<?php

namespace Pingframework\DotRestPhp\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Pingframework\DotRestPhp\Execution\CodeRunner;
use Pingframework\DotRestPhp\Reading\LinearFileReader;
use Pingframework\DotRestPhp\Parsing\CodeParser;
use Pingframework\DotRestPhp\Parsing\CommentParser;
use Pingframework\DotRestPhp\Parsing\ParserRegistry;
use Pingframework\DotRestPhp\Parsing\Request\BodyReader;
use Pingframework\DotRestPhp\Parsing\Request\HeadersReader;
use Pingframework\DotRestPhp\Parsing\Request\OptionsReader;
use Pingframework\DotRestPhp\Parsing\RequestParser;
use Pingframework\Streams\Helpers\is;
use Pingframework\Streams\Stream;

class CodeParserTest extends TestCase
{
    public function testParseGet1()
    {
        $pr = new ParserRegistry(
            new RequestParser(
                new HeadersReader(),
                new OptionsReader(),
                new BodyReader(),
            ),
            new CommentParser(),
            new CodeParser(),
        );
        $fr = new LinearFileReader(__DIR__ . '/../eval.rest.php');

        $runners = [];
        while ($l = $fr->nextToken()) {
            $runners[] = $pr->find($l)->parse($l, $fr, $pr);
        }

        /** @var array<CodeRunner> $runners */
        $runners = Stream::of($runners)->filter(is::instanceOf(CodeRunner::class))->toList();
        $this->assertCount(4, $runners);
        $this->assertCount(1, $runners[0]->codeBlocks);
        $this->assertEquals(
            "/** @var \\Pingframework\\DotRestPhp\\Execution\\Context \$ctx */\n\n\$ctx->var('token', '1');\n",
            $runners[0]->codeBlocks[0]->code
        );
        $this->assertCount(1, $runners[1]->codeBlocks);
        $this->assertEquals("\$ctx->var('token2', '2'); ", $runners[1]->codeBlocks[0]->code);
        $this->assertCount(2, $runners[2]->codeBlocks);
        $this->assertEquals("\$ctx->var('token3', '3'); ", $runners[2]->codeBlocks[0]->code);
        $this->assertEquals("\$ctx->var('token4', '4'); ", $runners[2]->codeBlocks[1]->code);
        $this->assertCount(3, $runners[3]->codeBlocks);
        $this->assertEquals(
            "\$ctx->var('token5', '5'); // inline comment\n\$ctx->var('token6', '6'); ",
            $runners[3]->codeBlocks[0]->code
        );
        $this->assertEquals("\$ctx->var('token7', '7'); ", $runners[3]->codeBlocks[1]->code);
        $this->assertEquals("\$ctx->var('token8', '8'); ", $runners[3]->codeBlocks[2]->code);
    }
}
