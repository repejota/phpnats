<?php
require_once __DIR__ . "/../../vendor/autoload.php";

$connectionOptions = new \Nats\ConnectionOptions();
$connectionOptions
    ->setHost('localhost')
    ->setPort(4222);
$c = new Nats\Connection($connectionOptions);
$c->connect();

$callback = function ($payload) {
    printf("Data: %s\r\n", $payload);
};

$sid = $c->subscribe("foo", $callback);

$c->wait(2);

$c->unsubscribe($sid);
