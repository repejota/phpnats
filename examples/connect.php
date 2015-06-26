<?php
require_once "../vendor/autoload.php";

const HOST = "localhost";
const PORT = 4222;

echo "Server: nats://" . HOST . ":" . PORT . PHP_EOL;
echo "Connecting ..." . PHP_EOL;
$c = new Nats\Connection();

var_dump($c);

$c->connect();

var_dump($c);

$c->close();

var_dump($c);