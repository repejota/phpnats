<?php
namespace Nats;

/**
 * Connection Class.
 */
class Connection
{
    /**
     * Number of PINGS.
     *
     * @var int number of pings
     */
    private $pings = 0;

    /**
     * Return the number of pings.
     *
     * @return int Number of pings
     */
    public function pingsCount()
    {
        return $this->pings;
    }

    /**
     * Number of messages published.
     *
     * @var int number of messages
     */
    private $pubs = 0;

    /**
     * Return the number of messages published.
     *
     * @return int number of messages published
     */
    public function pubsCount()
    {
        return $this->pubs;
    }

    /**
     * Number of reconnects to the server.
     *
     * @var int Number of reconnects
     */
    private $reconnects = 0;

    /**
     * Return the number of reconnects to the server.
     *
     * @return int number of reconnects
     */
    public function reconnectsCount()
    {
        return $this->reconnects;
    }

    /**
     * List of available subscriptions.
     *
     * @var array list of subscriptions
     */
    private $subscriptions = [];

    /**
     * Return the number of subscriptions available.
     *
     * @return int number of subscription
     */
    public function subscriptionsCount()
    {
        return count($this->subscriptions);
    }

    /**
     * Return subscriptions list.
     *
     * @return array list of subscription ids
     */
    public function getSubscriptions()
    {
        return array_keys($this->subscriptions);
    }

    /**
     * Connection options object
     *
     * @var ConnectionOptions|null
     */
    private $options = null;

    /**
     * Stream File Pointer.
     *
     * @var mixed Socket file pointer
     */
    private $streamSocket;

    /**
     * Constructor.
     *
     * @param ConnectionOptions $options Connection options object.
     */
    public function __construct(ConnectionOptions $options = null)
    {
        $this->pings = 0;
        $this->pubs = 0;
        $this->subscriptions = 0;
        $this->subscriptions = [];
        $this->options = $options;
        if (is_null($options)) {
            $this->options = new ConnectionOptions();
        }
    }

    /**
     * Sends data thought the stream.
     *
     * @param string $payload Message data.
     * @return void
     */
    private function send($payload)
    {
        $msg = $payload."\r\n";
        fwrite($this->streamSocket, $msg, strlen($msg));
    }

    /**
     * Receives a message thought the stream.
     *
     * @param integer $len Number of bytes to receive.
     *
     * @return string
     */
    private function receive($len = null)
    {
        if ($len) {
            return trim(fgets($this->streamSocket, $len + 1));
        } else {
            return trim(fgets($this->streamSocket));
        }
    }

    /**
     * Returns an stream socket to the desired server.
     *
     * @param string  $address Server url string.
     * @param integer $timeout Number of seconds until the connect() system call should timeout.
     * @return resource
     * @throws \Exception Exception raised if connection fails.
     */
    private function getStream($address, $timeout = null)
    {
        if (is_null($timeout)) {
            $timeout = intval(ini_get('default_socket_timeout'));
        }
        $fp = stream_socket_client($address, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!$fp) {
            throw new \Exception($errstr, $errno);
        }
        //stream_set_blocking($fp, 0);
        return $fp;
    }

    /**
     * Checks if the client is connected to a server.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return isset($this->streamSocket);
    }

    /**
     * Connect to server.
     *
     * @param integer $timeout Number of seconds until the connect() system call should timeout.
     * @throws \Exception Exception raised if connection fails.
     * @return void
     */
    public function connect($timeout = null)
    {
        $this->streamSocket = $this->getStream($this->options->getAddress(), $timeout);
        $msg = 'CONNECT '.$this->options;
        $this->send($msg);
    }

    /**
     * Sends PING message.
     *
     * @return void
     */
    public function ping()
    {
        $msg = 'PING';
        $this->send($msg);
        $this->pings += 1;
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param string $subject Message topic.
     * @param string $payload Message data.
     * @return void
     */
    public function publish($subject, $payload)
    {
        $msg = 'PUB '.$subject.' '.strlen($payload);
        $this->send($msg . "\r\n" . $payload);
        $this->pubs += 1;
    }

    /**
     * Subscribes to an specific event given a subject.
     *
     * @param string   $subject  Message topic.
     * @param resource $callback Closure to be executed as callback.
     * @return string
     */
    public function subscribe($subject, $callback)
    {
        $sid = uniqid();
        $msg = 'SUB '.$subject.' '.$sid;
        $this->send($msg);
        $this->subscriptions[$sid] = $callback;

        return $sid;
    }

    /**
     * Unsubscribe from a event given a subject.
     *
     * @param string $sid Subscription ID.
     * @return void
     */
    public function unsubscribe($sid)
    {
        $msg = 'UNSUB '.$sid;
        $this->send($msg);
    }

    /**
     * Handles PING command.
     *
     * @return void
     */
    private function handlePING()
    {
        $this->send('PONG');
    }

    /**
     * Handles MSG command.
     *
     * @param string $line Message command from NATS.
     *
     * @return \Exception|void
     */
    private function handleMSG($line)
    {
        $parts = explode(' ', $line);
        $length = $parts[3];
        $sid = $parts[2];

        $payload = $this->receive($length);

        $func = $this->subscriptions[$sid];
        if (is_callable($func)) {
            $func($payload);
        } else {
            return new \Exception('not callable');
        }
        
        return;
    }

    /**
     * Waits for messages.
     *
     * @param integer $quantity Number of messages to wait for.
     * @return resource $connection Connection object
     */
    public function wait($quantity = 0)
    {
        $count = 0;
        while (!feof($this->streamSocket)) {
            $line = $this->receive();

            // PING
            if (strpos($line, 'PING') === 0) {
                $this->handlePING();
            }

            // MSG
            if (strpos($line, 'MSG') === 0) {
                $count = $count + 1;
                $this->handleMSG($line);
                if (($quantity != 0) && ($count >= $quantity)) {
                    return $this;
                }
            }
        }
        $this->close();

        return $this;
    }

    /**
     * Reconnects to the server.
     *
     * @return void
     */
    public function reconnect()
    {
        $this->reconnects += 1;
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
        fclose($this->streamSocket);
        $this->streamSocket = null;
    }
}
