<?php
require_once "../../vendor/autoload.php";

const HOST = "localhost";
const PORT = 4222;

$c = new Nats\Connection();
$c->connect();

$c->reconnect();

$c->publish("foo", "bar");

$c->close();
