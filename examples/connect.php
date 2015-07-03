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

$c = new Nats\Connection();
$c->connect();
$c->close();
