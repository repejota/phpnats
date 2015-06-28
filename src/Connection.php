<?php
/**
 * Connection Class
 *
 * @category Class
 * @package  Nats
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
namespace Nats;

/**
 * A Connection represents a bare connection to a nats-server
 */
class Connection
{
    /**
     * @var int Number of pings published
     */
    private $pings = 0;

    /**
     * Return the number of pings published
     *
     * @return int Number of pings published
     */
    public function getNPings()
    {
        return $this->pings;
    }

    /**
     * @var int Number of messages published
     */
    private $pubs = 0;

    /**
     * Return the number of messages published
     *
     * @return int Number of messages published
     */
    public function getNPubs()
    {
        return $this->pubs;
    }

    /**
     * @var int Number of reconnects
     */
    private $reconnects = 0;

    /**
     * Get number of reconnects
     *
     * @return int Number of reconnects
     */
    public function getNReconnects()
    {
        return $this->reconnects;
    }

    /**
     * @var array List of subscriptions
     */
    private $subscriptions = [];

    /**
     * Get number of subscription
     *
     * @return int Number of subscription
     */
    public function getNSubscription()
    {
        return count($this->subscriptions);
    }

    /**
     * Get subscriptions ids
     *
     * @return array List of subscriptions ids
     */
    public function getSubscriptions()
    {
        return array_keys($this->subscriptions);
    }

    /**
     * @var string Host name or ip of the server
     */
    private $host;

    /**
     * @var integer Post number
     */
    private $port;

    /**
     * @var mixed Socket file pointer
     */
    private $fp;

    /**
     * @var string Server address
     */
    private $address = "nats://";

    /**
     * Constructor
     * @param string $host
     * @param int    $port
     */
    public function __construct($host = "localhost", $port = 4222)
    {
        $this->host = $host;
        $this->port = $port;
        $this->address = "tcp://" . $this->host . ":" . $this->port;
    }

    /**
     * Sends a message
     *
     * @param $payload
     */
    private function _Send($payload)
    {
        $msg = $payload . "\r\n";
        fwrite($this->fp, $msg, strlen($msg));
    }

    /**
     * Receives a message
     *
     * @param  $len
     * @return string
     */
    private function _Receive($len = null)
    {
        if ($len) {
            return trim(fgets($this->fp, $len + 1));
        } else {
            return trim(fgets($this->fp));
        }
    }

    /**
     * Connect will attempt to connect to the NATS server.
     * The url can contain username/password semantics.
     */
    public function connect()
    {
        $this->fp = stream_socket_client($this->address, $errno, $errstr, STREAM_CLIENT_CONNECT);
        if (!$this->fp) {
            echo $errstr . ":" . $errno;
        }
        stream_set_blocking($this->fp, 0);
        $msg = 'CONNECT {}';
        $this->_Send($msg);
    }

    /**
     * Sends PING message
     */
    public function ping()
    {
        $msg = "PING";
        $this->_Send($msg);
        $this->pings += 1;
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param  $subject (string): a string with the subject
     * @param  $payload (string): payload string
     * @return string
     */
    public function publish($subject, $payload)
    {
        $msg = "PUB " . $subject . " " . strlen($payload);
        $this->_Send($msg);
        $this->_Send($payload);
        $this->pubs += 1;
    }

    /**
     * Subscribes to an specific event given a subject.
     *
     * @param  $subject
     * @param  $callback
     * @return string
     */
    public function subscribe($subject, $callback)
    {
        $sid = uniqid();
        $msg = "SUB " . $subject . " " . $sid;
        $this->_Send($msg);
        $this->subscriptions[$sid] = $callback;
        return $sid;
    }

    /**
     * Unsubscribe from a event given a subject.
     *
     * @param $sid
     */
    public function unsubscribe($sid)
    {
        $msg = "UNSUB " . $sid;
        $this->_Send($msg);
    }

    /**
     * Waits for messages
     *
     * @param  int $quantity Number of messages to wait for
     * @return \Exception|void
     */
    public function wait($quantity = 0)
    {
        $count = 0;
        while (!feof($this->fp)) {
            $line = $this->_Receive();

            // Debug
            if ($line) {
                echo ">>>>>>>>> " . $line . PHP_EOL;
            }

            // PING
            if (strpos($line, 'PING') === 0) {
                $this->_Send("PONG");
            }

            // INFO
            if (strpos($line, 'INFO') === 0) {
                $parts = explode(" ", $line);
                $info = json_decode($parts[1]);
                $this->server = $info;
            }

            // MSG
            if (strpos($line, 'MSG') === 0) {
                $count = $count + 1;

                $parts = explode(" ", $line);
                $length = $parts[3];
                $sid = $parts[2];

                $payload = $this->_Receive($length);

                $func = $this->subscriptions[$sid];
                if (is_callable($func)) {
                    $func($payload);
                } else {
                    return new \Exception("not callable");
                }

                if (($quantity != 0) && ($count >= $quantity)) {
                    return null;
                }
            }
        }
        $this->close();
        return $this;
    }

    /**
     * Reconnects to the server
     */
    public function reconnect()
    {
        $this->reconnects += 1;
        $this->close();
        $this->connect();
    }

    /**
     * Close will close the connection to the server.
     */
    public function close()
    {
        fclose($this->fp);
    }

}
