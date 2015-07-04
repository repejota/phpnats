<?php

namespace Nats\tests\Util;

require 'vendor/autoload.php';


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
        socket_write($this->sock, "PING");

    }

    public function close()
    {
        socket_close($this->sock);
    }

    public function read()
    {
        // Read the input from the client &#8211; 1024 bytes
        $input = socket_read($this->sock, 1024);

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
time_nanosleep(0, 100000000);

$client->write();

$client->close();
