<?php

namespace Nats\tests\Util;

require 'vendor/autoload.php';


use Nats\tests\Util\ClientServerStub;

class ListeningServerStub
{
    protected $client;

    protected $sock;

    protected $addr;

    protected $port;

    public function __construct()
    {
        try {
            if (($this->sock = socket_create_listen(55555)) === false) {
                echo socket_strerror(socket_last_error());
            } else {
                echo "Socket created\n";
            }
            socket_getsockname($this->sock, $this->addr, $this->port);
            time_nanosleep(0, 100);
        
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function listen()
    {
        // Start listening for connections
        socket_listen($this->sock);

        // Accept incoming requests and handle them as child processes.
        $this->client = socket_accept($this->sock);
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

$server = new ListeningServerStub();
$time=8;

/*
$client = new ClientServerStub();
*/


while ($time>0) {
    time_nanosleep(1, 100);
    $clientSocket = socket_accept($server->getSock());
var_dump($clientSocket);


file_put_contents("/tmp/a.txt", "aaaa\n", FILE_APPEND);
    if(!is_null($clientSocket)) {
        $lll = socket_read($clientSocket, 100000);
        $line = "MSG OK 55966a4463383 10";
        $line = "PING";
        socket_write($clientSocket, $line);
    } else {
        $client->write();
        $line = "PING";
echo $line;
file_put_contents("/tmp/a.txt", $line."\n", FILE_APPEND);
        socket_write($server->getSock(), $line);
//        echo $client->write(100);
        time_nanosleep(0, 20000);
        continue;
    
    }
    $time--;
}

$client->close();
$server->close();
