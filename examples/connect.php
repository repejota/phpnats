<?php
require_once "../vendor/autoload.php";

$c = new Nats\Connection();
$c->connect();
$c->close();
