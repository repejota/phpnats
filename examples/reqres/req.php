<?php
require_once __DIR__ . "/../../vendor/autoload.php";

$connectionOptions = new \Nats\ConnectionOptions();
$connectionOptions->setHost('localhost')->setPort(4222);

$c = new Nats\Connection($connectionOptions);
$c->connect();

$c->request(
    'sayhello',
    'Marty McFly',
    function ($response) {
        echo $response->getBody();
    }
);
