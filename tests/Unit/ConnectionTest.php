<?php

namespace Nats\tests\Unit;

use Nats;
use Nats\ConnectionOptions;
use Cocur\BackgroundProcess\BackgroundProcess;

/**
 * Class ConnectionTest.
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var resource Client
     */
    private $c;

    /**
     * @var resource A separated process
     */
    private static $process;

    /**
     * @var bool Am I using a real or a fake server?
     */
    private static $isGnatsd = false;

    /**
     * Before Class code setup.
     */
    public static function setUpBeforeClass()
    {
        if (($socket = @fsockopen('localhost', 4222, $err)) !== false) {
            self::$isGnatsd = true;
        } else {
            self::$process = new BackgroundProcess('/usr/bin/php ./tests/Util/ListeningServerStub.php ');
            self::$process->run();
        }
    }

    /**
     * After Class code setup.
     */
    public static function tearDownAfterClass()
    {
        if (!self::$isGnatsd) {
            self::$process->stop();
        }
    }

    /**
     * setUp test suite.
     */
    public function setUp()
    {
        $options = new ConnectionOptions();
        if (!self::$isGnatsd) {
            time_nanosleep(0, 300000000);
            $options->port = 4222;
        }
        $this->c = new Nats\Connection($options);
        $this->c->connect();
    }

    /**
     * Test Connection.
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
     * Test Connection with bad configuration.
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function testConnectionBadStream()
    {
        $options = new ConnectionOptions();
        $options->host = null;
        $options->port = null;
        $this->c = new Nats\Connection($options);
        $this->c->connect();
    }

    /**
     * Test Ping command.
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
     */
    public function testSubscription()
    {

        $callback = function ($message) {
            $this->assertNotNull($message);
            $this->assertEquals($message, 'bar');
        };
        $sid = $this->c->subscribe('foo', $callback);

        $this->assertGreaterThan(0, $this->c->subscriptionsCount());

        $subscriptions = $this->c->getSubscriptions();
        $this->assertInternalType('array', $subscriptions);

        $this->c->publish('foo', 'bar');
        $this->assertEquals(1, $this->c->pubsCount());

        $process = new BackgroundProcess('/usr/bin/php ./tests/Util/ClientServerStub.php '.$sid);
        $process->run();

        $this->c->wait(1);
    }

    /**
     * Test Unsubscription command.
     */
    public function testUnSubscription()
    {
        $callback = function ($message) {
            $this->assertNotNull($message);
            $this->assertEquals($message, 'bar');
        };

        $sid = $this->c->subscribe('foo', $callback);

        $this->assertGreaterThan(0, $this->c->subscriptionsCount());
        $subscriptions = $this->c->getSubscriptions();
        $this->assertInternalType('array', $subscriptions);

        $this->c->publish('foo', 'bar');
        $this->assertEquals(1, $this->c->pubsCount());

        $this->c->unsubscribe($sid);
        $this->assertEquals(0, $this->c->subscriptionsCount());
    }
}
