<?php
require_once "../vendor/autoload.php";

$c = new Nats\Connection();
$c->connect("localhost", 4222, "foo", "bar");
$c->close();
