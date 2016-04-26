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
     * Connection options object.
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
     * Stream wrapper for testing purposes.
     *
     * @var mixed StreamWrapper.
     */
    private $streamWrapper;

    /**
     * Constructor.
     *
     * @param ConnectionOptions $options Connection options object.
     */
    public function __construct(ConnectionOptions $options = null)
    {
        $this->pings = 0;
        $this->pubs = 0;
        $this->subscriptions = [];
        $this->options = $options;
        $this->streamWrapper = new StreamWrapper();

        if (is_null($options)) {
            $this->options = new ConnectionOptions();
        }
    }

    /**
     * Setter for $streamWrapper. For testing purposes.
     *
     * @param StreamWrapper $streamWrapper StreamWrapper for testing purposes.
     *
     * @return void
     */
    public function setStreamWrapper(StreamWrapper $streamWrapper)
    {
        $this->streamWrapper = $streamWrapper;
    }

    /**
     * Sends data thought the stream.
     *
     * @param string $payload Message data.
     *
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
            $line = fgets($this->streamSocket, $len + 1);
        } else {
            $line = fgets($this->streamSocket);
        }

        if ($line === false) {
            return $line;
        } else {
            return trim($line);
        }
    }

    /**
     * Returns an stream socket to the desired server.
     *
     * @param string  $address Server url string.
     * @param integer $timeout Number of seconds until the connect() system call should timeout.
     *
     * @return resource
     * @throws \Exception Exception raised if connection fails.
     */
    private function getStream($address, $timeout = null)
    {
        if (is_null($timeout)) {
            $timeout = intval(ini_get('default_socket_timeout'));
        }
        $errno = null;
        $errstr = null;
        
        $fp = $this->streamWrapper->getStreamSocketClient($address, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

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
     *
     * @throws \Exception Exception raised if connection fails.
     * @return void
     */
    public function connect($timeout = null)
    {
        $this->streamSocket = $this->getStream($this->options->getAddress(), $timeout);
        $msg = 'CONNECT '.$this->options;
        $this->send($msg);

        $response = $this->receive();

        $this->ping();
        $response = $this->receive();

        if ($response !== "PONG") {
            if (strpos($response, '-ERR')!== false) {
                throw new \Exception("Failing connection: $response");
            }
        }
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
     * Request does a request and executes a callback with the response.
     *
     * @param string  $subject  Message topic.
     * @param string  $payload  Message data.
     * @param mixed   $callback Closure to be executed as callback.
     * @param integer $wait     Number of messages to wait for.
     *
     * @return void
     */
    public function request($subject, $payload, $callback, $wait = 1)
    {
        $inbox = uniqid('_INBOX.');
        $this->subscribe($inbox, $callback);

        $msg = 'PUB '.$subject.' '.$inbox.' '.strlen($payload);
        $this->send($msg . "\r\n" . $payload);
        $this->pubs += 1;

        $this->wait($wait);
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param string $subject Message topic.
     * @param string $payload Message data.
     *
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
     * @param \Closure $callback Closure to be executed as callback.
     *
     * @return string
     */
    public function subscribe($subject, \Closure $callback)
    {
        $sid = uniqid();
        $msg = 'SUB '.$subject.' '.$sid;
        $this->send($msg);
        $this->subscriptions[$sid] = $callback;

        return $sid;
    }

    /**
     * Subscribes to an specific event given a subject and a queue.
     *
     * @param string   $subject  Message topic.
     * @param string   $queue    Queue name.
     * @param \Closure $callback Closure to be executed as callback.
     *
     * @return string
     */
    public function queueSubscribe($subject, $queue, \Closure $callback)
    {
        $sid = uniqid();
        $msg = 'SUB '.$subject.' '.$queue.' '. $sid;
        $this->send($msg);
        $this->subscriptions[$sid] = $callback;

        return $sid;
    }

    /**
     * Unsubscribe from a event given a subject.
     *
     * @param string $sid Subscription ID.
     *
     * @return void
     */
    public function unsubscribe($sid)
    {
        $msg = 'UNSUB '.$sid;
        $this->send($msg);

        unset($this->subscriptions[$sid]);
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
     * @codeCoverageIgnore
     */
    private function handleMSG($line)
    {
        $parts = explode(' ', $line);
        $subject = null;
        $length = $parts[3];
        $sid = $parts[2];

        if (count($parts) == 5) {
            $length = $parts[4];
            $subject = $parts[3];
        } elseif (count($parts) == 4) {
            $length = $parts[3];
            $subject = $parts[1];
        }

        $payload = $this->receive($length);
        $msg = new Message($subject, $payload, $sid, $this);

        $func = $this->subscriptions[$sid];
        if (is_callable($func)) {
            $func($msg);
        } else {
            return new \Exception('not callable');
        }

        return;
    }

    /**
     * Waits for messages.
     *
     * @param integer $quantity Number of messages to wait for.
     *
     * @return resource $connection Connection object
     */
    public function wait($quantity = 0)
    {
        $count = 0;
        while (!feof($this->streamSocket)) {
            $line = $this->receive();
            if ($line === false) {
                return null;
            }

            if (strpos($line, 'PING') === 0) {
                $this->handlePING();
            }

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
     * Set Stream Timeout.
     *
     * @param integer $seconds Before timeout on stream.
     *
     * @return boolean
     */
    public function setStreamTimeout($seconds)
    {
        if ($this->isConnected()) {
            if (is_int($seconds)) {
                try {
                    return $this->streamWrapper->setStreamTimeout($this->streamSocket, $seconds);
                } catch (\Exception $e) {
                    return false;
                }
            }
        }

        return false;
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
