<?php
/**
 * Connection example
 *
 * PHP version 5
 *
 * @category Script
 * @package  Nats
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
require_once "../vendor/autoload.php";

const HOST = "localhost";
const PORT = 4222;

echo "Server: nats://" . HOST . ":" . PORT . PHP_EOL;
$c = new Nats\Connection();
echo "Connecting ..." . PHP_EOL;
$c->connect();
echo "Disconnecting ..." . PHP_EOL;
$c->close();
