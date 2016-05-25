<?php

require_once __DIR__ . "/../../vendor/autoload.php";

$connectionOptions = new \Nats\ConnectionOptions();
$connectionOptions->setHost('localhost')->setPort(4222);

$c = new Nats\Connection($connectionOptions);
$c->connect();

$c->queue(
    'sayhello',
    'g1',
    function ($response) {
        echo $response->getBody();
    }
);

$c->wait();
