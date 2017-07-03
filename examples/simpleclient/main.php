<?php
require_once __DIR__.'/../../vendor/autoload.php';

use Nats\Connection as NatsClient;

$client = new NatsClient();
$client->connect();

printf('Connected to NATS.'.PHP_EOL);

// Simple Subscriber.
$client->subscribe(
    'foo',
    function ($message) {
        printf("Data: %s\r\n".PHP_EOL, $message->getBody());
    }
);
printf('Subscribed to "foo" messages.'.PHP_EOL);

// Wait for messages.
printf('Wait for messages on %s subscriptions'.PHP_EOL, $client->subscriptionsCount());
while (true) {
    $client->wait();

    printf('Reconnecting ...'.PHP_EOL);
    $client->reconnect();
    printf('Reconnected %s subscriptions'.PHP_EOL, $client->subscriptionsCount());
}
