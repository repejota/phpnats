<?php
namespace Nats\tests\Unit;

use Nats;
use Nats\ConnectionOptions;
use Nats\StreamWrapper;
use Prophecy\Argument;

/**
 * Class ConnectionTest.
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Client.
     *
     * @var resource Client
     */
    private $c;

    /**
     * Process.
     *
     * @var resource A separated process.
     */
    private static $process;

    /**
     * Gnatsd switch.
     *
     * @var bool Am I using a real or a fake server?
     */
    private static $isGnatsd = false;


    /**
     * SetUp test suite.
     *
     * @return void
     */
    public function setUp()
    {
        $options = new ConnectionOptions();
        $this->c = new Nats\Connection($options);
        $this->c->connect();
    }


    /**
     * Test Connection.
     *
     * @return void
     */
    public function testConnection()
    {
        // Connect
        $this->c->connect();
        $this->assertTrue($this->c->isConnected());

        // Disconnect
        $this->c->close();
        $this->assertFalse($this->c->isConnected());
    }


    /**
     * Test Ping command.
     *
     * @return void
     */
    public function testPing()
    {
        $this->c->ping();
        $count = $this->c->pingsCount();
        $this->assertInternalType('int', $count);
        $this->assertGreaterThan(0, $count);
        $this->c->close();
    }

    /**
     * Test Publish command.
     *
     * @return void
     */
    public function testPublish()
    {
        $this->c->ping();
        $this->c->publish('foo', 'bar');
        $count = $this->c->pubsCount();
        $this->assertInternalType('int', $count);
        $this->assertGreaterThan(0, $count);
        $this->c->close();
    }

    /**
     * Test Reconnect command.
     * @return void
     */
    public function testReconnect()
    {
        $this->c->reconnect();
        $count = $this->c->reconnectsCount();
        $this->assertInternalType('int', $count);
        $this->assertGreaterThan(0, $count);
        $this->c->close();
    }

    /**
     * Test Subscription command.
     *
     * @return void
     */
    public function testSubscription()
    {
        $this->markTestSkipped(
            'WIP: moving to real NATS dockerized server.'
        );

        $callback = function ($message) {
            $this->assertNotNull($message);
            $this->assertEquals($message, 'bar');
        };

        $this->c->subscribe('foo', $callback);
        $this->assertGreaterThan(0, $this->c->subscriptionsCount());
        $subscriptions = $this->c->getSubscriptions();
        $this->assertInternalType('array', $subscriptions);

        $this->c->publish('foo', 'bar');
        $this->assertEquals(1, $this->c->pubsCount());
        $this->c->wait(1);
    }

    /**
     * Test Queue Subscription command.
     *
     * @return void
     */
    public function testQueueSubscription()
    {
        $this->markTestSkipped(
            'WIP: moving to real NATS dockerized server.'
        );

        $callback = function ($message) {
            $this->assertNotNull($message);
            $this->assertEquals($message, 'bar');
        };

        $this->c->queueSubscribe('foo', 'bar', $callback);
        $this->assertGreaterThan(0, $this->c->subscriptionsCount());
        $subscriptions = $this->c->getSubscriptions();
        $this->assertInternalType('array', $subscriptions);

        $this->c->publish('foo', 'bar');
        $this->assertEquals(1, $this->c->pubsCount());
/*
        $process = new BackgroundProcess('/usr/bin/php ./tests/Util/ClientServerStub.php ');
        $process->run();
*/
        // time_nanosleep(1, 0);
        $this->c->wait(1);
    }

    /**
     * Test Request command.
     *
     * @return void
     */
    public function testRequest()
    {
        $this->c->subscribe(
            "sayhello",
            function ($res) {
                $res->reply("Hello, ".$res->getBody(). " !!!");
            }
        );

        $this->c->request(
            'sayhello',
            'McFly',
            function ($message) {
                $this->assertNotNull($message);
                $this->assertEquals($message, 'Hello, McFly !!!');
            }
        );
    }

    /**
     * Test Unsubscribe command.
     *
     * @return void
     */
    public function testUnsubscribe()
    {
        $sid = $this->c->subscribe(
            "unsub",
            function ($res) {
                $this->assertTrue(false);
            }
        );
        $this->c->unsubscribe($sid);
        $this->c->publish('unsub', 'bar');

        $this->assertTrue(true);
    }

    /**
     * Test setStreamTimeout command.
     *
     * @return void
     */
    public function testSetStreamTimeout()
    {
        $this->assertTrue($this->c->setStreamTimeout(2));
        $this->assertFalse($this->c->setStreamTimeout("hello"));
    }
}
