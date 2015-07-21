<?php
require_once "../vendor/autoload.php";

const HOST = "localhost";
const PORT = 4222;

echo "Server: nats://" . HOST . ":" . PORT . PHP_EOL;
$c = new Nats\Connection();
$c->connect();

$c->ping();
