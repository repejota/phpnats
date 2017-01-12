<?php
namespace Nats;

use RandomLib\Factory;
use RandomLib\Generator;

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
     * Chunk size in bytes to use when reading with fread.
     * @var integer
     */
    private $chunkSize = 1500;

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
     * Connection timeout
     *
     * @var float
     */
    private $timeout = null;

    /**
     * Stream File Pointer.
     *
     * @var mixed Socket file pointer
     */
    private $streamSocket;

    /**
     * @var Generator|Php71RandomGenerator
     */
    private $randomGenerator;

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
        if(version_compare(phpversion(), '7.0', '>')){
            $this->randomGenerator = new Php71RandomGenerator();
        } else {
            $randomFactory = new Factory();
            $this->randomGenerator = $randomFactory->getLowStrengthGenerator();
        }

        if (is_null($options)) {
            $this->options = new ConnectionOptions();
        }
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
            $chunkSize = $this->chunkSize;
            $line = null;
            $receivedBytes = 0;
            while ($receivedBytes < $len) {
                $bytesLeft = $len - $receivedBytes;
                if ($bytesLeft < $this->chunkSize) {
                    $chunkSize = $bytesLeft;
                }

                $readChunk = fread($this->streamSocket, $chunkSize);
                $receivedBytes += strlen($readChunk);
                $line .= $readChunk;
            }
        } else {
            $line = fgets($this->streamSocket);
        }
        return $line;
    }

    /**
     * Returns an stream socket to the desired server.
     *
     * @param string $address Server url string.
     * @param float  $timeout Number of seconds until the connect() system call should timeout.
     *
     * @return resource
     * @throws \Exception Exception raised if connection fails.
     */
    private function getStream($address, $timeout)
    {
        $errno = null;
        $errstr = null;

        $fp = stream_socket_client($address, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        $timeout = number_format($timeout, 3);
        $seconds = floor($timeout);
        $microseconds = ($timeout - $seconds) * 1000;
        stream_set_timeout($fp, $seconds, $microseconds);

        if (!$fp) {
            throw new \Exception($errstr, $errno);
        }

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
     * @param float $timeout Number of seconds until the connect() system call should timeout.
     *
     * @throws \Exception Exception raised if connection fails.
     * @return void
     */
    public function connect($timeout = null)
    {
        if ($timeout === null) {
            $timeout = intval(ini_get('default_socket_timeout'));
        }

        $this->timeout = $timeout;
        $this->streamSocket = $this->getStream($this->options->getAddress(), $timeout);
        $this->setStreamTimeout($timeout);

        $msg = 'CONNECT '.$this->options;
        $this->send($msg);
        $connect_response = $this->receive();
        if (strpos($connect_response, '-ERR')!== false) {
            throw new \Exception("Failing connection: $connect_response");
        }

        $this->ping();
        $ping_response = $this->receive();
        if ($ping_response !== "PONG") {
            if (strpos($ping_response, '-ERR')!== false) {
                throw new \Exception("Failing on first ping: $ping_response");
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
    public function publish($subject, $payload = null)
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
        $sid = $this->randomGenerator->generateString(16);
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
        $sid = $this->randomGenerator->generateString(16);
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
     * @param string $line Message command from Nats.
     *
     * @return void
     * @throws Exception If subscription not found.
     * @codeCoverageIgnore
     */
    private function handleMSG($line)
    {
        $parts = explode(' ', $line);
        $subject = null;
        $length = trim($parts[3]);
        $sid = $parts[2];

        if (count($parts) == 5) {
            $length = trim($parts[4]);
            $subject = $parts[3];
        } elseif (count($parts) == 4) {
            $length = trim($parts[3]);
            $subject = $parts[1];
        }

        $payload = $this->receive($length);
        $msg = new Message($subject, $payload, $sid, $this);

        if (!isset($this->subscriptions[$sid])) {
            throw new Exception('subscription not found');
        }

        $func = $this->subscriptions[$sid];
        if (is_callable($func)) {
            $func($msg);
        } else {
            throw new Exception('not callable');
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
        $info = stream_get_meta_data($this->streamSocket);
        while (is_resource($this->streamSocket) && !feof($this->streamSocket) && !$info['timed_out']) {
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
            $info = stream_get_meta_data($this->streamSocket);
        }
        $this->close();

        return $this;
    }

    /**
     * Set Stream Timeout.
     *
     * @param float $seconds Before timeout on stream.
     *
     * @return boolean
     */
    public function setStreamTimeout($seconds)
    {
        if ($this->isConnected()) {
            if (is_numeric($seconds)) {
                try {
                    $timeout = number_format($seconds, 3);
                    $seconds = floor($timeout);
                    $microseconds = ($timeout - $seconds) * 1000;
                    return stream_set_timeout($this->streamSocket, $seconds, $microseconds);
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
        $this->connect($this->timeout);
    }

    /**
     * @param integer $chunkSize Set byte chunk len to read when reading from wire.
     * @return void
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;
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


    /**
     * @return resource
     */
    public function streamSocket()
    {
        return $this->streamSocket;
    }
}
