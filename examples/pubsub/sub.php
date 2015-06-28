<?php
/**
 * Subscriber example
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

$c = new Nats\Connection(HOST, PORT);
$c->connect();

$callback = function ($payload) {
    printf("Data: %s\r\n", $payload);
};

$sid = $c->subscribe("foo", $callback);

$c->wait();
