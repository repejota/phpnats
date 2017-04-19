<?php
require_once __DIR__ . "/../../vendor/autoload.php";

$encoder = new \Nats\JSONEncoder();
$connectionOptions = new \Nats\ConnectionOptions();
$connectionOptions
    ->setHost('localhost')
    ->setPort(4222);
$c = new Nats\EncodedConnection($connectionOptions, $encoder);
$c->connect();

$c->reconnect();

$c->publish("foo", "bar");
$c->publish("foo", "bar");
$c->publish("foo", "bar");
$c->publish("foo", "bar");
$c->publish("foo", "bar");
