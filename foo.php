<?php
require_once __DIR__.'/vendor/autoload.php';

use Nats\ConnectionOptions;
use Nats\EncodedConnection;
use Nats\Encoders\JSONEncoder;

$options = new ConnectionOptions();
$encoder = new JSONEncoder();
$c       = new EncodedConnection($options, $encoder);
$c->connect();

$a = array('foo', 'bar', 1, 2, 3);

$callback = function($message) {
    print($message->getBody()[1]);
};
$sid = $c->subscribe('foo', $callback);

$c->request('foo', $a);