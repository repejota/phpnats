<?php
namespace Nats\tests\Unit;

use Nats;
use Nats\ConnectionOptions;
use Nats\EncodedConnection;
use Nats\Encoders\JSONEncoder;

/**
 * Class EncodedConnectionTest.
 */
class EncodedConnectionTest extends \PHPUnit_Framework_TestCase
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
        $encoder = new JSONEncoder();
        $options = new ConnectionOptions();
        $this->c = new EncodedConnection($options, $encoder);
        $this->c->connect();
    }


    /**
     * Test Connection.
     *
     * @return void
     */
    public function testConnection()
    {
        // Connect.
        $this->c->connect();
        $this->assertTrue($this->c->isConnected());

        // Disconnect.
        $this->c->close();
        $this->assertFalse($this->c->isConnected());
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
     * Test Request command.
     *
     * @return void
     */
    public function testRequest()
    {
        $this->c->subscribe(
            'sayhello',
            function ($res) {
                $res->reply('Hello, '.$res->getBody().' !!!');
            }
        );

        $this->c->request(
            'sayhello',
            'McFly',
            function ($res) {
                $this->assertEquals('Hello, McFly !!!', $res->getBody());
            }
        );
    }

    /**
     * Test Request command.
     *
     * @return void
     */
    public function testRequestArray()
    {
        $this->c->subscribe(
            'sayhello',
            function ($res) {
                $res->reply('Hello, '.$res->getBody()[1].' !!!');
            }
        );

        $this->c->request(
            'sayhello',
            [
             'foo',
             'McFly',
            ],
            function ($res) {
                $this->assertEquals('Hello, McFly !!!', $res->getBody());
            }
        );
    }
}
