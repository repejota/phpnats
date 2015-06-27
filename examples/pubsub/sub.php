<?php
require_once "../../vendor/autoload.php";

const HOST = "localhost";
const PORT = 4222;

$c = new Nats\Connection();
$c->connect();

$callback = function($payload)
{
    printf("Data: %s\r\n", $payload);
};

$c->subscribe("hola", $callback);

$c->subscribe("msg", function($msg) {
    echo $msg . PHP_EOL;
});

$c->wait(10);
