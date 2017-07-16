<?php
require_once __DIR__.'/../../vendor/autoload.php';

$client = new \Nats\Connection();
$client->connect();

// Simple Publisher.
// Request.
$client->request(
    'foo',
    'Marty McFly',
    function ($message) {
        echo $message->getBody();
    }
);

$client->close();
