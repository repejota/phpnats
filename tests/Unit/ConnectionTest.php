<?php

/**
 * TestConnection Class.
 *
 * PHP version 5
 *
 * @category Class
 *
 * @author  Raül Përez <repejota@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link https://github.com/repejota/phpnats
 */

namespace Nats\tests\Unit;

use Nats;
use Nats\ConnectionOptions;
use Cocur\BackgroundProcess\BackgroundProcess;

/**
 * Class ConnectionTest.
 *
 * @category Class
 *
 * @author  Raül Përez <repejota@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link https://github.com/repejota/phpnats
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    private $c;

    private static $process;

    private static $isGnatsd = false;

    public static function setUpBeforeClass()
    {
        if (($socket = @fsockopen("localhost", 4222, $err))!==false) {
             self::$isGnatsd = true;
        } else {
            self::$process = new BackgroundProcess('/usr/bin/php ./tests/Util/ListeningServerStub.php ');
            self::$process->run();
        }
    }

    public static function tearDownAfterClass()
    {
        if (!self::$isGnatsd) {
            self::$process->stop();
        }
    }

    public function setUp()
    {
        $options = new ConnectionOptions();
        if (!self::$isGnatsd) {
            time_nanosleep(2, 0);
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

        $this->c->subscribe('foo', $callback);
        $this->assertGreaterThan(0, $this->c->subscriptionsCount());
        $subscriptions = $this->c->getSubscriptions();
        $this->assertInternalType('array', $subscriptions);

        $this->c->publish('foo', 'bar');
        $this->assertEquals(1, $this->c->pubsCount());

        //$process = new BackgroundProcess('/usr/bin/php ./tests/Util/ClientServerStub.php ');
        //$process->run();
        //$this->c->wait(1);
    }
}
