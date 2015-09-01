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
        $address = "tcp://localhost:4222";
        $this->sock = stream_socket_client($address, $errno, $errstr, STREAM_CLIENT_CONNECT);
/*
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->sock, 'localhost', 4222);
*/
    }

    /**
     * Sends a PING command
     *
     * @return void
     */
    public function write($msg)
    {
        fwrite($this->sock, $msg, strlen($msg));
    }
}

$client = new ClientServerStub();
$msg = "PING";
if (!empty($argv[1])) {
    $msg = trim($argv[1]);
}

$client->write($msg);
