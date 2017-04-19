<?php
require_once __DIR__.'/../../vendor/autoload.php';

$connectionOptions = new \Nats\ConnectionOptions();
$connectionOptions->setHost('localhost')->setPort(4222);
$c = new Nats\Connection($connectionOptions);
$c->connect();

$sid = $c->subscribe(
    'sayhello',
    function ($res) {
        $res->reply('Hello, '.$res->getBody().' !!!');
    }
);

$c->wait(2);

$c->unsubscribe($sid);
