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
     * Before Class code setup
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        if (($socket = @fsockopen("localhost", 4222, $err))!==false) {
             self::$isGnatsd = true;
        } else {
            self::$process = new BackgroundProcess('/usr/bin/php ./tests/Util/ListeningServerStub.php ');
            self::$process->run();
        }
    }

    /**
     * After Class code setup
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        if (!self::$isGnatsd) {
            self::$process->stop();
        }
    }

    /**
     * setUp test suite
     *
     * @return void
     */
    public function setUp()
    {
        $options = new ConnectionOptions();
        if (!self::$isGnatsd) {
            time_nanosleep(1, 700000000);
            $options->setPort(4222);
        }
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
     *
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

        $process = new BackgroundProcess('/usr/bin/php ./tests/Util/ClientServerStub.php ');
        $process->run();
        // time_nanosleep(1, 0);
        $this->c->wait(1);
    }
}
