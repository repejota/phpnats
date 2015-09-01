<?php
namespace Nats\tests\Util;

require 'vendor/autoload.php';

/**
 * Class ClientServerStub
 */
class ClientServerStub
{
    /**
     * @var resource Client
     */
    protected $client;

    /**
     * @var resource Socket
     */
    protected $sock;

    /**
     * @var string Server address
     */
    protected $addr;

    /**
     * @var int Server port
     */
    protected $port;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->sock, 'localhost', 4222);
    }

    /**
     * Sends a PING command
     *
     * @return void
     */
    public function write()
    {
        socket_write($this->sock, "PING");

    }

    /**
     * Close the connection
     *
     * @return void
     */
    public function close()
    {
        socket_close($this->sock);
    }
}

$client = new ClientServerStub();
time_nanosleep(4, 0);

$client->write();

 $client->close();
