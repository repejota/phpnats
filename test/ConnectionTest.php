<?php
namespace Nats\tests\Unit;

use Nats;
use Nats\ConnectionOptions;
use Prophecy\Argument;

/**
 * Class ConnectionTest.
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Client.
     *
     * @var Nats\Connection Client
     */
    private $c;


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
     * Test Request command.
     *
     * @return void
     */
    public function testRequest()
    {

        $i = 0;
        do {
            $this->c->subscribe(
                "sayhello$i",
                function ($res) {
                    $res->reply("Hello, ".$res->getBody(). " !!!");
                }
            );

            $this->c->request(
                "sayhello$i",
                'McFly',
                function ($message) {
                    $this->assertNotNull($message);
                    $this->assertEquals($message, 'Hello, McFly !!!');
                }
            );

            $i++;

        } while ($i < 100);
    }

    /**
     * Test Request command with large payload.
     *
     * @return void
     */
    public function testLargeRequest()
    {

        $content = file_get_contents(dirname(__FILE__).'/test.pdf');
        
        $contentLen = strlen($content);

        $i = 0;
        do {

            $this->c->subscribe(
                "saybighello$i",
                function ($res) use ($contentLen) {
                    $gotLen = strlen($res->getBody());
                    $this->assertEquals($contentLen, $gotLen);
                    $res->reply($gotLen);
                }
            );

            $this->c->request(
                "saybighello$i",
                $content,
                function ($message) use ($contentLen) {
                    $this->assertNotNull($message);
                    $this->assertEquals($message->getBody(), $contentLen);
                }
            );

            $i++;
        } while ($i < 100);

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
}
