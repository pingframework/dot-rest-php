POST /api/v1/users
Authorization: Bearer {{token}}
[Options]
foo: bar

baz: qux

{
    "foo": "bar"
}

# multi-line code block
<?php
/** @var \Pingframework\DotRestPhp\Execution\Context $ctx */

$ctx->var('token', '1');
?>

# inline code block
<?php $ctx->var('token2', '2'); ?>

# inline code block multiple times
<?php $ctx->var('token3', '3'); ?> <?php $ctx->var('token4', '4'); ?>

# multi-line code block combined with inline code block
<?php $ctx->var('token5', '5'); // inline comment

$ctx->var('token6', '6'); ?> <?php $ctx->var('token7', '7'); ?><?php $ctx->var('token8', '8'); ?>


GET /api/v1/users
