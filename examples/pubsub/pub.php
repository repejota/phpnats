<?php
require_once "../../vendor/autoload.php";

const HOST = "localhost";
const PORT = 4222;

$c = new Nats\Connection();
$c->connect();

$c->publish("msg", "tonto");
$c->publish("hola", "Pepe");
$c->publish("hola", "Paco");
$c->publish("hola", "Manolo");

$c->close();