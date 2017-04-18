<?php
require_once __DIR__ . "/../../vendor/autoload.php";

$start = microtime(true);

$c = new Nats\Connection();
$c->connect();

$limit = 100000;
for ($i = 1; $i <= $limit; $i++) {
    $c->publish("foo");
}

$c->close();

$time_elapsed_secs = microtime(true) - $start;

$speed = $limit/$time_elapsed_secs;
print "Published " . $limit . " messages in " . $time_elapsed_secs . " seconds" . PHP_EOL;
print round($speed) . " messages/second" . PHP_EOL;
