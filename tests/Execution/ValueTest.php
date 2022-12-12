<?php

namespace Pingframework\DotRestPhp\Tests\Execution;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Pingframework\DotRestPhp\Config;
use Pingframework\DotRestPhp\Execution\Context;
use Pingframework\DotRestPhp\Execution\Value;
use Pingframework\DotRestPhp\Output\Console\ConsoleLogger;
use Pingframework\DotRestPhp\Reading\Line;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class ValueTest extends TestCase
{

    public function testResolveInt()
    {
        $value = new Value('42');
        $this->assertEquals(42, $value->resolve($this->getLine(), $this->getContext()));
    }

    public function testResolveFloat()
    {
        $value = new Value('4.2');
        $this->assertEquals(4.2, $value->resolve($this->getLine(), $this->getContext()));
    }

    public function testResolveBool()
    {
        $value = new Value('true');
        $this->assertTrue($value->resolve($this->getLine(), $this->getContext()));
        $value = new Value('false');
        $this->assertFalse($value->resolve($this->getLine(), $this->getContext()));
        $value = new Value('TRUE');
        $this->assertTrue($value->resolve($this->getLine(), $this->getContext()));
        $value = new Value('fAlSe');
        $this->assertFalse($value->resolve($this->getLine(), $this->getContext()));
    }

    public function testResolveList()
    {
        $value = new Value('[1, "str", true]');
        $this->assertEquals([1, "str", true], $value->resolve($this->getLine(), $this->getContext()));
    }

    public function testResolveMap()
    {
        $value = new Value(
            '[my key => 42, "my key 2" => str, my key bool no space=>true, my key bool => true, positional value]'
        );
        $actual = $value->resolve($this->getLine(), $this->getContext());

        $this->assertIsArray($actual);
        $this->assertArrayHasKey('my key', $actual);
        $this->assertArrayHasKey('my key 2', $actual);
        $this->assertArrayHasKey('my key bool no space', $actual);
        $this->assertArrayHasKey('my key bool', $actual);
        $this->assertArrayHasKey(4, $actual);
        $this->assertEquals(42, $actual['my key']);
        $this->assertEquals('str', $actual['my key 2']);
        $this->assertEquals(true, $actual['my key bool no space']);
        $this->assertEquals(true, $actual['my key bool']);
        $this->assertEquals('positional value', $actual[4]);
    }

    public function testResolveString()
    {
        $value = new Value('"my "q" s\'tring"');
        $this->assertEquals('my "q" s\'tring', $value->resolve($this->getLine(), $this->getContext()));
    }

    public function testResolveVar()
    {
        $value = new Value('{{myVar1}}');
        $this->assertEquals(42, $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42])));
    }

    public function testResolveFunc()
    {
        $value = new Value('var myVar1');
        $this->assertEquals(42, $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42])));
        $value = new Value('var "myVar1"');
        $this->assertEquals(42, $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42])));
        $value = new Value('var {{myVar2}}');
        $this->assertEquals(
            42,
            $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42, 'myVar2' => 'myVar1'])),
        );
    }

    public function testResolveWrappedFunc()
    {
        $value = new Value('{var myVar1}');
        $this->assertEquals(42, $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42])));
    }

    public function testResolveWrappedFuncDuration()
    {
        $value = new Value('{duration %S.%f}');
        $actual = $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42]));
        $this->assertGreaterThan(0, (float)$actual);
    }

    public function testResolveFuncWithoutArgsStatus()
    {
        $response = new Response(200, [], '{"myVar1": 42}');
        $value = new Value('{status}');
        $this->assertEquals(
            200,
            $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42, '__RESPONSE__' => $response])),
        );
    }

    public function testResolveFuncWithoutArgsJson()
    {
        $response = new Response(200, [], '{"myVar1": 42}');
        $value = new Value('{jsonbody}');
        $actual = $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42, '__RESPONSE__' => $response]));
        $this->assertEquals(['myVar1' => 42], $actual);

        $value = new Value('jsonbody');
        $actual = $value->resolve($this->getLine(), $this->getContext(['myVar1' => 42, '__RESPONSE__' => $response]));
        $this->assertEquals(['myVar1' => 42], $actual);
    }

    public function testResolveJsonpath()
    {
        $response = new Response(200, [], '{"jsonrpc":"2.0","id":1,"result":{"id":1,"email":"shimon@bbumgames.com","groups":[],"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NzA0OTQxMDAuNzc4NTcyLCJuYmYiOjE2NzA0OTQxMDAuNzc4NTcyLCJleHAiOjE2NzA0OTc3MDAuNzc4NTcyLCJ1aWQiOjF9.h-Kg1Zx291GCACyC8zV7MEdZbNlDkzTiJfM0p0FOykE","acl":["admin.acl.list","admin.group.create","admin.group.delete","admin.group.list","admin.group.update","admin.group.view","admin.user.create","admin.user.delete","admin.user.list","admin.user.update","admin.user.view","cron.job.create","cron.job.grid","cron.job.list","cron.job.run","cron.job.update","cron.run.grid","cron.run.kill","grm.collectibles.import","grm.contests.import","report.view"]}}');
        $value = new Value('{jsonpath $.result.token}');
        $actual = $value->resolve($this->getLine(), $this->getContext(['__RESPONSE__' => $response]));
        $this->assertSame('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NzA0OTQxMDAuNzc4NTcyLCJuYmYiOjE2NzA0OTQxMDAuNzc4NTcyLCJleHAiOjE2NzA0OTc3MDAuNzc4NTcyLCJ1aWQiOjF9.h-Kg1Zx291GCACyC8zV7MEdZbNlDkzTiJfM0p0FOykE', $actual);
    }

    public function testResolveFileRelativePath()
    {
        $value = new Value('< test.txt');
        $this->assertEquals('text file content', $value->resolve($this->getLine(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'test.rest.php'), $this->getContext()));
    }

    private function getLine(string $path = ''): Line
    {
        return new Line($path, 0, '');
    }

    private function getContext(array $vars = []): Context
    {
        return new Context(
            new Config(),
            ConsoleLogger::build(new SymfonyStyle(new StringInput(''), new BufferedOutput())),
            $vars
        );
    }
}
