<?php
require_once __DIR__.'/../../vendor/autoload.php';

$client = new \Nats\Connection();
$client->connect();

// Publish Subscribe

# Simple Subscriber
$callback = function($message)
{
    printf("Data: %s\r\n", $message->getBody());
};
$client->subscribe("foo", $callback);

# Simple Publisher
$client->publish("foo", "foo bar");

# Wait for 1 message
$client->wait(1);
