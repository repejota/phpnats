<?php
/**
 * TestConnection Class
 *
 * @category Class
 * @package  Nats\Tests\Unit
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
namespace Nats\Tests\Unit;

use Nats;

/**
 * Class TestConnection
 * @package Nats\Tests\Unit
 */
class TestConnection extends \PHPUnit_Framework_TestCase
{

    /**
     * Test Dummy
     */
    public function testDummy() 
    {
        $this->assertTrue(true);
    }

    /**
     * Test Connection
     */
    public function testConnection() 
    {
        $c = new Nats\Connection();
        $c->connect();
        $c->close();
    }

    /**
     * Test Ping command
     */
    public function testPing() 
    {
        $c = new Nats\Connection();
        $c->connect();
        $c->ping();
        $c->ping();
        $this->assertGreaterThan(0, $c->getNPings());
        $c->close();
    }

    /**
     * Test Publish command
     */
    public function testPublish() 
    {
        $c = new Nats\Connection();
        $c->connect();
        $c->publish("foo", "bar");
        $this->assertGreaterThan(0, $c->getNPubs());
        $c->close();
    }
}
