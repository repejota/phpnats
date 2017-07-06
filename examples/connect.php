<?php
require_once __DIR__.'/../vendor/autoload.php';

$connectionOptions = new \Nats\ConnectionOptions(
    [
     'host' => '127.0.0.1',
     'port' => 4222,
    ]
);

$c = new Nats\Connection($connectionOptions);
$c->connect();
$c->close();
