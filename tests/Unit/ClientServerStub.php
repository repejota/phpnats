<?php

namespace Nats\tests\Unit;

require 'vendor/autoload.php';
use Cocur\BackgroundProcess\BackgroundProcess;


class ClientServerStub
{
    protected $client;

    protected $sock;

    protected $addr;

    protected $port;

    public function __construct()
    {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->sock, 'localhost', 55555);
    }

    public function write()
    {
        socket_write($this->sock, "PUBLISH");

    }

    public function close()
    {
        socket_close($this->sock);
    }

    public function read()
    {
        // Read the input from the client &#8211; 1024 bytes
        $input = socket_read($client, 1024);

        return $input;
    }

    public function getAddr()
    {
        return $this->addr;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getSock()
    {
        return $this->sock;
    }
}

$client = new ClientServerStub();
$client->write();

$client->close();
