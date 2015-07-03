<?php

/**
 * TestConnection Class.
 *
 * PHP version 5
 *
 * @category Class
 *
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link     https://github.com/repejota/phpnats
 */

namespace Nats\tests\Unit;

use Nats;
use Cocur\BackgroundProcess\BackgroundProcess;



/**
 * Class TestConnection.
 *
 * @category Class
 *
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link     https://github.com/repejota/phpnats
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    private $_c;

    private static $_process;

    private static $_port;

    public static function setUpBeforeClass()
    {

        self::$_process = new BackgroundProcess('/usr/bin/php tests/Unit/ListeningServerStub.php &');
        self::$_process->run();

    }

    public static function tearDownAfterClass()
    {
        self::$_process->stop();
        //socket_close(self::$_server);
    }

    public function setUp()
    {
        $this->_c = new Nats\Connection('localhost', 55555);
        $this->_c->connect();
    }

    /**
     * Test Dummy.
     */
    public function testDummy()
    {
        $this->assertTrue(true);
    }

    /**
     * Test Connection.
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
     * Test Ping command.
     */
    public function testPing()
    {
        $this->_c->ping();
        $count = $this->_c->pingsCount();
        $this->assertInternalType('int', $count);
        $this->assertGreaterThan(0, $count);
        $this->_c->close();
    }

    /**
     * Test Publish command.
     */
    public function testPublish()
    {
        $this->_c->publish('foo', 'bar');
        $count = $this->_c->pubsCount();
        $this->assertInternalType('int', $count);
        $this->assertGreaterThan(0, $count);
        $this->_c->close();
    }

    /**
     * Test Reconnect command.
     */
    public function testReconnect()
    {
        $this->_c->reconnect();
        $count = $this->_c->reconnectsCount();
        $this->assertInternalType('int', $count);
        $this->assertGreaterThan(0, $count);
        $this->_c->close();
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
        $this->_c->subscribe('foo', $callback);
        $this->assertGreaterThan(0, $this->_c->subscriptionsCount());
        $subscriptions = $this->_c->getSubscriptions();
        $this->assertInternalType('array', $subscriptions);

        $this->_c->publish('foo', 'bar');
        $this->assertEquals(1, $this->_c->pubsCount());
        // $this->_c->wait(1);
    }

    public function testWait()
    {
    }
}
