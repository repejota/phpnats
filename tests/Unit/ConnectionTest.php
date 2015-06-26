<?php
namespace Nats\Tests\Unit;

use Nats;

class TestConnection extends \PHPUnit_Framework_TestCase {

    public function testDummy() {
        $this->assertTrue(true);
    }

    public function testConnection() {
        $c = new Nats\Connection();
        $c->connect();
        $c->close();
    }

    public function testPing() {
        $c = new Nats\Connection();
        $c->connect();
        $c->ping();
        $c->close();
    }

    public function testSubscribeUnsubscribe() {

    }

    public function testPublish() {
        $c = new Nats\Connection();
        $c->connect();
        $c->publish("foo", "bar");
        $c->close();
    }

}
