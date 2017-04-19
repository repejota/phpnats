<?php
require_once __DIR__.'/../../vendor/autoload.php';

$encoder           = new \Nats\Encoders\JSONEncoder();
$connectionOptions = new \Nats\ConnectionOptions();

$connectionOptions->setHost('localhost')->setPort(4222);
$c = new Nats\EncodedConnection($connectionOptions, $encoder);
$c->connect();

$callback = function ($payload) {
    printf("Data: %s\r\n", $payload);
};

$sid = $c->subscribe('foo', $callback);

$c->wait(2);

$c->unsubscribe($sid);
