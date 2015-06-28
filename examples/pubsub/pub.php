<?php
/**
 * Publisher example
 *
 * PHP version 5
 *
 * @category Script
 * @package  Nats
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
require_once "../../vendor/autoload.php";

const HOST = "localhost";
const PORT = 4222;

$c = new Nats\Connection();
$c->connect();

$c->reconnect();

$c->publish("foo", "bar");
$c->publish("foo", "bar");
$c->publish("foo", "bar");
$c->publish("foo", "bar");
$c->publish("foo", "bar");
