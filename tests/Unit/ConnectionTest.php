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

use Prophecy\PhpUnit\ProphecyTestCase as TestCase;

use Nats;

/**
 * Class TestConnection
 *
 * @category Class
 * @package  Nats\Tests\Unit
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
class TestConnection extends TestCase
{
    private $_c;

    private $_server;

    /**
     * Setup tests
     *
     * @return null
     */
    public function setUp()
    {
        $this->_c = new Nats\Connection();
        $this->_c->connect();
    }

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
        // Connect
        $this->_c->connect();
        $this->assertTrue($this->_c->isConnected());

        // Disconnect
        $this->_c->close();
        $this->assertFalse($this->_c->isConnected());
    }

    /**
     * Test Ping command
     *
     * @return null
     */
    public function testPing()
    {
        $this->_c->ping();
        $count = $this->_c->pingsCount();
        $this->assertInternalType("int", $count);
        $this->assertGreaterThan(0, $count);
        $this->_c->close();
    }

    /**
     * Test Publish command
     *
     * @return null
     */
    public function testPublish()
    {
        $this->_c->publish("foo", "bar");
        $count = $this->_c->pubsCount();
        $this->assertInternalType("int", $count);
        $this->assertGreaterThan(0, $count);
        $this->_c->close();
    }

    /**
     * Test Server reconnection
     *
     * @return null
     */
    public function testReconnect()
    {
        $this->_c->reconnect();
        $count = $this->_c->reconnectsCount();
        $this->assertInternalType("int", $count);
        $this->assertGreaterThan(0, $count);
        $this->_c->close();
    }

    /**
     * Test Server subscription
     *
     * @return null
     */
    public function testSubscription()
    {
        $callback = function ($message) {
            $this->assertNotNull($message);
            $this->assertEquals($message, "bar");
        };
        $this->_c->subscribe("foo", $callback);
        $this->assertGreaterThan(0, $this->_c->subscriptionsCount());

        $subscriptions = $this->_c->getSubscriptions();
        $this->assertInternalType("array", $subscriptions);

        $this->_c->publish("foo", "bar");
        $this->_c->wait(1);
    }

}
