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
 *
 * @category Class
 * @package  Nats\Tests\Unit
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
class TestConnection extends \PHPUnit_Framework_TestCase
{
    private $c;

    /**
     * Setup tests
     */
    public function setUp() 
    {
        $this->c = $this->getMockBuilder('Nats\Connection')->getMock();
        $this->c->expects($this->any())->method("connect")->willReturn(null);
        $this->c->expects($this->any())->method("pingsCount")->willReturn(1);
        $this->c->expects($this->any())->method("pubsCount")->willReturn(1);
        $this->c->expects($this->any())->method("reconnectsCount")->willReturn(1);
        $this->c->expects($this->any())->method("subscriptionsCount")->willReturn(1);
        $this->c->expects($this->any())->method("getSubscriptions")->willReturn(["foo", "bar"]);
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
        $this->c->connect();
        $this->c->close();
    }

    /**
     * Test Ping command
     *
     * @return null
     */
    public function testPing() 
    {
        $count = $this->c->pingsCount();
        $this->assertInternalType("int", $count);
        $this->assertGreaterThan(0, $count);
        $this->c->close();
    }

    /**
     * Test Publish command
     *
     * @return null
     */
    public function testPublish() 
    {
        $this->c->publish("foo", "bar");
        $this->count = $this->c->pubsCount();
        $this->assertInternalType("int", $this->count);
        $this->assertGreaterThan(0, $this->count);
        $this->c->close();
    }

    /**
     * Test Server reconnection
     *
     * @return null
     */
    public function testReconnect()
    {
        $this->c->reconnect();
        $this->count = $this->c->reconnectsCount();
        $this->assertInternalType("int", $this->count);
        $this->assertGreaterThan(0, $this->count);
        $this->c->close();
    }

    /**
     * Test Server subscription
     *
     * @return null
     */
    public function testSubscription()
    {
        $this->c->subscribe(
            "foo", function ($message) {
                $this->assertNotNull($message);
            }
        );
        $this->assertGreaterThan(0, $this->c->subscriptionsCount());
        $subscriptions = $this->c->getSubscriptions();
        $this->assertInternalType("array", $subscriptions);

        $this->c->publish("foo", "bar");
        $this->c->wait(1);
    }
}
