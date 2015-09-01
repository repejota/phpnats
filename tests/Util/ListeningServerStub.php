<?php

namespace Nats\tests\Util;

require 'vendor/autoload.php';

/**
 * Class ListeningServerStub.
 */
class ListeningServerStub
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
     * Constructor.
     *
     * @throws \Exception Connection Error exception.
     */
    public function __construct()
    {
        try {
            $address = "tcp://localhost:4222";
            $this->sock = stream_socket_server($address, $errno, $errstr);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Close socket.
     */
    public function close()
    {
        socket_close($this->sock);
    }

    /**
     * Get the socket.
     *
     * @return resource socket pointer
     */
    public function getSock()
    {
        return $this->sock;
    }
}

$server = new ListeningServerStub();
$time = 15;

while ($time>0) {
    time_nanosleep(1, 0);
    $clientSocket = stream_socket_accept($server->getSock());

    if (!is_null($clientSocket)) {
        $lll =  trim(fgets($clientSocket));

        $line = "MSG OK $lll 10";
        if (strpos($lll, 'CONNECT') === false) {
            fwrite($clientSocket, $line, strlen($line));
        } else {
            fwrite($clientSocket, "PING", strlen("PING"));
        }

    } else {
        $line = 'PING';
  //      fwrite($server->getSock(), $line, strlen($line));
        continue;
    }
    $time--;
}

$server->close();
