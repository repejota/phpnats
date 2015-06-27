<?php
namespace Nats;

/**
 * A Connection represents a bare connection to a nats-server
 */
class Connection {

    /**
     * @var array List of subscriptions
     */
    private $subscriptions = [];

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
        $address = "tcp://" . $this->host . ":" . $this->port;
        $this->fp	= stream_socket_client( $address, $errno, $errstr, 30 );
        stream_set_blocking( $this->fp, 1 );
        $msg = 'CONNECT {}' . "\r\n";
        fwrite($this->fp, $msg, strlen($msg));
    }

    /**
     * Sends PING message
     */
    public function ping() {
        $msg = "PING" . "\r\n";
        fwrite($this->fp, $msg, strlen($msg));
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
        fwrite($this->fp, $msg, strlen($msg));
        $msg = $payload . "\r\n";
        fwrite($this->fp, $msg, strlen($msg));
    }

    /**
     * Subscribe subscribes to an specific event given a subject.
     *
     * @param $subject
     * @param $callback
     * @return string
     */
    public function subscribe($subject, $callback) {
        $id = uniqid();
        $msg = "SUB " . $subject . " " . $id . "\r\n";
        fwrite($this->fp, $msg, strlen($msg));
        $key = $id .$subject;
        $this->subscriptions[$key] = $callback;
    }

    /**
     * Waits for messages
     */
    public function wait($quantity=0) {
        $count = 0;
        while (!feof($this->fp)) {
            $line = trim(fgets($this->fp));

            if (strpos($line, 'MSG') === 0) {
                $count = $count+1;

                $parts = explode(" ", $line);

                $subject = $parts[1];
                $length = $parts[3];
                $sid = $parts[2];

                $payload = fgets($this->fp, $length+1);

                $key = $sid .$subject;
                $f = $this->subscriptions[$key];
                $f($payload);

                if (($quantity !=0) && ($count >= $quantity)) {
                    return;
                }
            }
        }
    }

    /**
     * Close will close the connection to the server.
     */
    public function close() {
        fgets($this->fp);
        fclose($this->fp);
    }

}
