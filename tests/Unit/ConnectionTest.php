<?php
namespace Nats\Tests\Unit;

use Nats;

/**
 * Class TestConnection
 * @package Nats\Tests\Unit
 */
class TestConnection extends \PHPUnit_Framework_TestCase {

    /**
     * Test Dummy
     */
    public function testDummy() {
        $this->assertTrue(true);
    }

    /**
     * Test Connection
     */
    public function testConnection() {
        $c = new Nats\Connection();
        $c->connect();
        $c->close();
    }

    /**
     * Test Ping command
     */
    public function testPing() {
        $c = new Nats\Connection();
        $c->connect();
        $c->ping();
        $c->close();
    }

    /**
     * Test Publish command
     */
    public function testPublish() {
        $c = new Nats\Connection();
        $c->connect();
        $c->publish("foo", "bar");
        $c->close();
    }

}
