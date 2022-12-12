<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/hello/{name}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->get('/error', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    return $response->withStatus(500);
});

$app->get('/html', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    $response->getBody()->write(file_get_contents(__DIR__ . '/test.html'));
    return $response;
});

$app->run();