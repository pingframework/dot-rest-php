# Config section
serverHost = localhost:8888

config baseUri = http://{{serverHost}}

# Setting up built-in php server
<?php

/** @var CodeBlock $this */
/** @var Context $ctx */

use Pingframework\DotRestPhp\Execution\CodeBlock;
use Pingframework\DotRestPhp\Execution\Context;
use Symfony\Component\Console\Output\OutputInterface;

// starting built in server
$proc = proc_open(
    "php -S {$ctx->var('serverHost')} {$this->curdir()}/server.php",
    [
        ["pipe", "r"],
        ["pipe", "w"],
        ["pipe", "w"],
    ],
    $pipes,
);

// close the process on exit
$this->defer(fn() => proc_terminate($proc));

// waiting for server to start
sleep(1);
