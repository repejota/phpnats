<?php
namespace Nats;

/**
 * A Connection represents a bare connection to a nats-server
 */
class Connection {

    /**
     * @var $host string Host name or ip of the server
     */
    private $host;

    /**
     * @var $port integer Post number
     */
    private $port;

    /**
     * @var $fp mixed Socket file pointer
     */
    private $fp;

    /**
     * Constructor
     */
    public function __construct() {

    }

    /**
     * Connect will attempt to connect to the NATS server.
     * The url can contain username/password semantics.
     */
    public function connect() {
        $this->host = "localhost";
        $this->port = 4222;
        $this->fp = fsockopen($this->host, $this->port, $errno, $errstr, 30);
        $msg = "CONNECT {}\r\n";
        fwrite($this->fp, $msg);
        $res = fgets($this->fp);
        return $res;
    }

    /**
     * Sends PING message
     */
    public function ping() {
        $msg = "PING\r\n";
        fwrite($this->fp, $msg);
        $res = fgets($this->fp);
        return $res;
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param $subject (string): a string with the subject
     * @param $payload (string): payload string
     * @return string
     */
    public function publish($subject, $payload) {
        $msg = "PUB " . $subject . " " . strlen($payload) . "\r\n";
        fwrite($this->fp, $msg);
        fwrite($this->fp, $payload);
        $res = fgets($this->fp);
        return $res;
    }

    /**
     * Close the connection to the NATS server and open a new one.
     */
    public function reconnect() {
        $this->close();
        $this->connect();
    }

    /**
     * Close will close the connection to the server.
     */
    public function close() {
        fclose($this->fp);
    }


}
