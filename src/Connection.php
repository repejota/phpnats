<?php
namespace Nats;

const DEFAULT_URI = 'nats://localhost:4222';

/**
 * A Connection represents a bare connection to a nats-server
 */
class Connection {

    private $fp = null;

    public function __construct() {

    }

    /**
     * Connect will attempt to connect to the NATS server.
     * The url can contain username/password semantics.
     */
    public function connect() {
        $this->fp = fsockopen("localhost", 4222, $errno, $errstr, 30);
        $msg = "CONNECT {}\r\n";
        fwrite($this->fp, $msg);
        $res = fgets($this->fp);
        var_dump($res);
    }

    /**
     * Sends PING message
     */
    public function ping() {
        $msg = "PING\r\n";
        fwrite($this->fp, $msg);
        $res = fgets($this->fp);
        var_dump($res);
    }

    /**
     * Subscribe will express interest in the given subject. The subject can
     * have wildcards (partial:*, full:>). Messages will be delivered to the
     * associated callback.
     *
     * Args:
     * subject (string): a string with the subject
     * callback (function): callback to be called
     */
    public function subscribe() {

    }

    /**
     * Unsubscribe will remove interest in the given subject. If max is
     * provided an automatic Unsubscribe that is processed by the server
     * when max messages have been received
     *
     * @param $subscription (pynats.Subscription): a Subscription object
     * @param $max (int=None): number of messages
     */
    public function unsubscribe($subscription, $max) {

    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param $subject (string): a string with the subject
     * @param $payload (string): payload string
     * @param $reply (string): subject used in the reply
     */
    public function publish($subject, $payload) {
        $msg = "PUB " . $subject . " " . strlen($payload) . "\r\n";
        fwrite($this->fp, $msg);
        fwrite($this->fp, $payload);
        $res = fgets($this->fp);
        var_dump($res);
    }

    /**
     * Publish a message with an implicit inbox listener as the reply.
     * Message is optional.
     *
     * @param $subject (string): a string with the subject
     * @param $callback (function): callback to be called
     * @param $msg (string=None): payload string
     */
    public function request() {

    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param $duration (float): will wait for the given number of seconds
     * @param $count (count): stop of wait after n messages from any subject
     */
    public function wait() {

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
