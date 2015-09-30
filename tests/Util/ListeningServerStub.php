<?php

namespace Nats\tests\Util;

require 'vendor/autoload.php';

/**
 * Class ListeningServerStub
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
            if (($this->sock = socket_create_listen(4222)) === false) {
                echo socket_strerror(socket_last_error());
            } else {
                echo "Socket created\n";
            }
            socket_getsockname($this->sock, $this->addr, $this->port);
        
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Close socket.
     *
     * @return void
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
$time=25;

while ($time>0) {
    time_nanosleep(1, 100000);
    $clientSocket = socket_accept($server->getSock());

    if (!is_null($clientSocket)) {
        $lll = socket_read($clientSocket, 100000);
        $line = "MSG OK 55966a4463383 10";
        $line = "PING";
        socket_write($clientSocket, $line);
    } else {
        $line = "PING";
        socket_write($server->getSock(), $line);
        time_nanosleep(1, 20000);
        continue;
    
    }
    $time--;
}

$server->close();
