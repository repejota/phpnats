<?php
require_once __DIR__ . "/../vendor/autoload.php";

$connectionOptions = new \Nats\ConnectionOptions();
$connectionOptions
    ->setHost('localhost')
    ->setPort(4222);

echo "Server: nats://" . $connectionOptions->getHost() . ":" . $connectionOptions->getPort() . PHP_EOL;

$c = new Nats\EncodedConnection($connectionOptions);
$c->connect();

$c->ping();
