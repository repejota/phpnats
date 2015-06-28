<?php
/**
 * Connection Class
 *
 * PHP version 5
 *
 * @category Class
 * @package  Nats
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
namespace Nats;

/**
 * Connection Class
 *
 * @category Class
 * @package  Nats\Tests\Unit
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
class Connection
{
    /**
     * Version number
     */
    public $VERSION = "0.0.0";

    /**
     * Number of PINGS
     *
     * @var int number of pings
     */
    private $_pings = 0;

    /**
     * Return the number of pings
     *
     * @return int Number of pings
     */
    public function pingsCount()
    {
        return $this->_pings;
    }

    /**
     * Number of messages published
     *
     * @var int number of messages
     */
    private $_pubs = 0;

    /**
     * Return the number of messages published
     *
     * @return int number of messages published
     */
    public function pubsCount()
    {
        return $this->_pubs;
    }

    /**
     * Number of reconnects to the server
     *
     * @var int Number of reconnects
     */
    private $_reconnects = 0;

    /**
     * Return the number of reconnects to the server
     *
     * @return int number of reconnects
     */
    public function reconnectsCount()
    {
        return $this->_reconnects;
    }

    /**
     * List of available subscriptions
     *
     * @var array list of subscriptions
     */
    private $_subscriptions = [];

    /**
     * Return the number of subscriptions available
     *
     * @return int number of subscription
     */
    public function subscriptionsCount()
    {
        return count($this->_subscriptions);
    }

    /**
     * Return subscriptions list
     *
     * @return array list of subscription ids
     */
    public function getSubscriptions()
    {
        return array_keys($this->_subscriptions);
    }

    /**
     * Hostname of the server
     *
     * @var string hostname
     */
    private $_host;

    /**
     * Por number of the server
     *
     * @var integer port number
     */
    private $_port;

    /**
     * Stream File Pointer
     *
     * @var mixed Socket file pointer
     */
    private $_streamSocket;

    /**
     * Server address
     *
     * @var string Server address
     */
    private $_address = "nats://";

    /**
     * Constructor
     *
     * @param string $host name, by default "localhost"
     * @param int    $port number, by default 4222
     */
    public function __construct($host = "localhost", $port = 4222)
    {
        $this->VERSION = file_get_contents("./VERSION");

        $this->_pings = 0;
        $this->_pubs = 0;
        $this->_subscriptions = 0;
        $this->_subscriptions = [];

        $this->_host = $host;
        $this->_port = $port;
        $this->_address = "tcp://" . $this->_host . ":" . $this->_port;
    }

    /**
     * Sends data thought the stream
     *
     * @param string $payload message data
     *
     * @return void
     */
    private function _send($payload)
    {
        $msg = $payload . "\r\n";
        fwrite($this->_streamSocket, $msg, strlen($msg));
    }

    /**
     * Receives a message thought the stream
     *
     * @param int $len Number of bytes to receive
     *
     * @return string
     */
    private function _receive($len = null)
    {
        if ($len) {
            return trim(fgets($this->_streamSocket, $len + 1));
        } else {
            return trim(fgets($this->_streamSocket));
        }
    }

    /**
     * Returns an stream socket to the desired server.
     *
     * @param string $address Server url string
     *
     * @return resource
     */
    private function _getStream($address)
    {
        $fp = stream_socket_client($address, $errno, $errstr, STREAM_CLIENT_CONNECT);
        if (!$fp) {
            echo "!!!!!!! " . $errstr . " - " . $errno;
        }
        stream_set_blocking($fp, 0);
        return $fp;
    }

    /**
     * Checks if the client is connected to a server
     *
     * @return bool
     */
    public function isConnected()
    {
        return isset($this->_streamSocket);
    }

    /**
     * Connect to server.
     *
     * Connect will attempt to connect to the NATS server specified by address.
     *
     * Example:
     *   nats://localhost:4222
     *
     * The url can contain username/password semantics.
     *
     * Example:
     *   nats://user:pass@localhost:4222
     *
     * @param null $host      host name to connect
     * @param null $port      host port to connect
     * @param bool $verbose   if verbose mode is enabled
     * @param bool $pedantic  if pedantic mode is enabled
     * @param bool $reconnect if reconnect mode is enabled
     *
     * @return void
     */
    public function connect($host = null,
        $port = null,
        $verbose = false,
        $pedantic = false,
        $reconnect = true
    ) {
        if (isset($host)) {
            $this->_host = $host;
            $this->_address = "tcp://" . $this->_host . ":" . $this->_port;
        }
        if (isset($port)) {
            $this->_port = $port;
            $this->_address = "tcp://" . $this->_host . ":" . $this->_port;
        }
        $verbose = ($verbose) ? 'true' : 'false';
        $pedantic = ($pedantic) ? 'true' : 'false';
        $reconnect = ($reconnect) ? 'true' : 'false';

        $options = '{ ';
        $options .= ' "verbose": ' . $verbose . ', ';
        $options .= ' "pedantic": ' . $pedantic . ', ';
        $options .= ' "reconnect": ' . $reconnect;
        $options .= ' }';

        $this->_streamSocket = $this->_getStream($this->_address);
        $msg = 'CONNECT ' . $options;
        $this->_send($msg);
    }

    /**
     * Sends PING message
     *
     * @return void
     */
    public function ping()
    {
        $msg = "PING";
        $this->_send($msg);
        $this->_pings += 1;
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param string $subject message topic
     * @param string $payload message data
     *
     * @return string
     */
    public function publish($subject, $payload)
    {
        $msg = "PUB " . $subject . " " . strlen($payload);
        $this->_send($msg);
        $this->_send($payload);
        $this->_pubs += 1;
    }

    /**
     * Subscribes to an specific event given a subject.
     *
     * @param string $subject  message topic
     * @param mixed  $callback closure to be executed as callback
     *
     * @return string
     */
    public function subscribe($subject, $callback)
    {
        $sid = uniqid();
        $msg = "SUB " . $subject . " " . $sid;
        $this->_send($msg);
        $this->_subscriptions[$sid] = $callback;
        return $sid;
    }

    /**
     * Unsubscribe from a event given a subject.
     *
     * @param string $sid Subscription ID
     *
     * @return void
     */
    public function unsubscribe($sid)
    {
        $msg = "UNSUB " . $sid;
        $this->_send($msg);
    }

    /**
     * Handles PING command
     *
     * @return void
     */
    private function _handlePING()
    {
        $this->_send("PONG");
    }

    /**
     * Handles MSG command
     *
     * @param string $line Message command from NATS
     *
     * @return \Exception|void
     */
    private function _handleMSG($line)
    {
        $parts = explode(" ", $line);
        $length = $parts[3];
        $sid = $parts[2];

        $payload = $this->_receive($length);

        $func = $this->_subscriptions[$sid];
        if (is_callable($func)) {
            $func($payload);
        } else {
            return new \Exception("not callable");
        }
    }

    /**
     * Waits for messages
     *
     * @param int $quantity Number of messages to wait for
     *
     * @return \Exception|void
     */
    public function wait($quantity = 0)
    {
        $count = 0;
        while (!feof($this->_streamSocket)) {
            $line = $this->_receive();

            // PING
            if (strpos($line, 'PING') === 0) {
                $this->_handlePing();
            }

            // MSG
            if (strpos($line, 'MSG') === 0) {
                $count = $count + 1;
                $this->_handleMSG($line);
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
     *
     * @return void
     */
    public function reconnect()
    {
        $this->_reconnects += 1;
        $this->close();
        $this->connect();
    }

    /**
     * Close will close the connection to the server.
     *
     * @return void
     */
    public function close()
    {
        fclose($this->_streamSocket);
        $this->_streamSocket = null;
    }

}
