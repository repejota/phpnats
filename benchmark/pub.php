<?php
require_once __DIR__ . "/../vendor/autoload.php";

$start = microtime(true);

$connectionOptions = new \Nats\ConnectionOptions();
$connectionOptions->setHost('localhost')->setPort(4222);

$c = new Nats\Connection($connectionOptions);
$c->connect();

$limit = 1000000;
for ($i = 1; $i <= $limit; $i++) {
    print $i."\n";
    $c->publish("foo");
}

$c->close();

$time_elapsed_secs = microtime(true) - $start;

$speed = $limit/$time_elapsed_secs;
print "Published " . $limit . " messages in " . $time_elapsed_secs . " seconds" . PHP_EOL;
print round($speed) . " messages/second" . PHP_EOL;
