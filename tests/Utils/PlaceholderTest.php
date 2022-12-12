<?php

namespace Pingframework\DotRestPhp\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Pingframework\DotRestPhp\Config;
use Pingframework\DotRestPhp\Execution\Context;
use Pingframework\DotRestPhp\Output\Console\ConsoleLogger;
use Pingframework\DotRestPhp\Reading\Line;
use Pingframework\DotRestPhp\Utils\PlaceholderTrait;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class PlaceholderTest extends TestCase
{
    use PlaceholderTrait;

    public function testReplacePlaceholders()
    {
        $this->assertEquals(
            'Hello World!',
            $this->replacePlaceholders(
                'Hello {{world}}!',
                new Context(
                    new Config(),
                    ConsoleLogger::build(new SymfonyStyle(new StringInput(''), new BufferedOutput())),
                    ['world' => "World"]
                ),
                new Line('', 0, ''),
            ),
        );
        $this->assertEquals(
            'Hello World!',
            $this->replacePlaceholders(
                'Hello {var world}!',
                new Context(
                    new Config(),
                    ConsoleLogger::build(new SymfonyStyle(new StringInput(''), new BufferedOutput())),
                    ['world' => "World"]
                ),
                new Line('', 0, ''),
            ),
        );
        $this->assertEquals(
            'Hello World World!',
            $this->replacePlaceholders(
                'Hello {{world}} {var world}!',
                new Context(
                    new Config(),
                    ConsoleLogger::build(new SymfonyStyle(new StringInput(''), new BufferedOutput())),
                    ['world' => "World"]
                ),
                new Line('', 0, ''),
            ),
        );
    }
}
