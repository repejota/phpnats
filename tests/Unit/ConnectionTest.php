<?php
/**
 * TestConnection Class
 *
 * PHP version 5
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
 * @category Class
 * @package Nats\Tests\Unit
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
class TestConnection extends \PHPUnit_Framework_TestCase
{

    /**
     * Test Dummy
     *
     * @return null
     */
    public function testDummy() 
    {
        $this->assertTrue(true);
    }

    /**
     * Test Connection
     *
     * @return null
     */
    public function testConnection() 
    {
        $c = new Nats\Connection();
        $c->connect();
        $c->close();
    }

    /**
     * Test Ping command
     *
     * @return null
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
     *
     * @return null
     */
    public function testPublish() 
    {
        $c = new Nats\Connection();
        $c->connect();
        $c->publish("foo", "bar");
        $this->assertGreaterThan(0, $c->pubsCount());
        $c->close();
    }
}
