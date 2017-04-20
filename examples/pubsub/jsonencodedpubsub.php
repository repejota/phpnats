<?php
require_once __DIR__.'/../../vendor/autoload.php';

$encoder = new \Nats\Encoders\JSONEncoder();
$options = new \Nats\ConnectionOptions();
$client = new \Nats\EncodedConnection($options, $encoder);
$client->connect();

// Publish Subscribe


# Simple Subscriber
$callback = function($payload)
{
    printf("Data: %s\r\n", $payload->getBody()[1]);
};
$client->subscribe("foo", $callback);

# Simple Publisher
$client->publish("foo", ["foo", "bar"]);

# Wait for 1 message
$client->wait(1);

// Request Response


# Responding to requests
$sid = $client->subscribe("sayhello", function ($message) {
    $message->reply("Reply: Hello, " . $message->getBody()[1] . " !!!");
});

# Request
$client->request('sayhello', ["foo", "McFly"], function ($message) {
    echo $message->getBody();
});

