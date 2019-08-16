<?php
/** @noinspection PhpUnused */
declare(strict_types = 1);


namespace Nats;

use Nats\Error\ConnectionLostException;
use RandomLib\Factory;
use RandomLib\Generator;

/**
 * Connection Class.
 *
 * Handles the connection to a NATS server or cluster of servers.
 *
 * @package Nats
 */
class Connection
{
    
    // FIXME: Missing method type-hints!
    
    /**
     * Show DEBUG info?
     *
     * @var bool
     */
    private $debug = false;
    
    /**
     * Enable or disable debug mode.
     *
     * @param bool $debug If debug is enabled.
     */
    public function setDebug($debug) : void
    {
        $this->debug = $debug;
    }
    
    /**
     * Number of PINGs.
     *
     * @var int
     */
    private $pings = 0;
    
    /**
     * Return the number of pings.
     *
     * @return int Number of pings.
     */
    public function pingsCount() : int
    {
        return $this->pings;
    }
    
    /**
     * Chunk size in bytes to use when reading an stream of data.
     *
     * @var int
     */
    private $chunkSize = 1500;
    
    /**
     * Number of messages published.
     *
     * @var int
     */
    private $pubs = 0;
    
    /**
     * Return the number of messages published.
     *
     * @return int Number of messages published.
     */
    public function pubsCount() : int
    {
        return $this->pubs;
    }
    
    /**
     * Number of reconnects to the server.
     *
     * @var int
     */
    private $reconnects = 0;
    
    /**
     * Return the number of reconnects to the server.
     *
     * @return int Number of reconnects.
     */
    public function reconnectsCount() : int
    {
        return $this->reconnects;
    }
    
    /**
     * List of available subscriptions.
     *
     * @var array
     */
    private $subscriptions = [];
    
    /**
     * Return the number of subscriptions available.
     *
     * @return int Number of subscriptions.
     */
    public function subscriptionsCount() : int
    {
        return \count($this->subscriptions);
    }
    
    /**
     * Return subscriptions list.
     *
     * @return array List of subscription IDs.
     */
    public function getSubscriptions() : array
    {
        return \array_keys($this->subscriptions);
    }
    
    /**
     * Connection options.
     *
     * @var ConnectionOptions|null
     */
    private $options;
    
    /**
     * Connection timeout.
     *
     * @var float|int|null
     */
    private $timeout;
    
    /**
     * Socket File Pointer.
     *
     * @var resource|null
     */
    private $streamSocket;
    
    /**
     * Generator object.
     *
     * @var Generator|Php71RandomGenerator
     */
    private $randomGenerator;
    
    /**
     * Sets the chunk size in bytes to be processed when reading.
     *
     * @param integer $chunkSize Set byte chunk length to read when reading from wire.
     */
    public function setChunkSize($chunkSize) : void
    {
        $this->chunkSize = $chunkSize;
    }
    
    /**
     * Set Stream Timeout.
     *
     * @param float $seconds Before timeout on stream.
     *
     * @return bool **TRUE** on success or **FALSE** on failure.
     */
    public function setStreamTimeout($seconds) : bool
    {
        $result = false;
        
        if (\is_numeric($seconds) === true && $this->isConnected() === true) {
            try {
                $timeout = \number_format($seconds, 3);
                $seconds = \floor($timeout);
                $microseconds = (($timeout - $seconds) * 1000);
    
                return \stream_set_timeout($this->streamSocket, (int)$seconds, (int)$microseconds);
            } catch (\Exception $exception) {
                $result = false;
            }
        }
    
        return $result;
    }
    
    /**
     * Returns an stream socket for this connection.
     *
     * @return resource|null
     */
    public function getStreamSocket()
    {
        return $this->streamSocket;
    }
    
    /**
     * Indicates whether `$response` is an error response.
     *
     * @param string $response The NATS Server response.
     *
     * @return boolean
     */
    private function isErrorResponse($response) : bool
    {
        return \strpos($response, '-ERR') === 0;
    }
    
    /**
     * Checks if the client is connected to a server.
     *
     * @return boolean
     */
    public function isConnected() : bool
    {
        return isset($this->streamSocket);
    }
    
    /**
     * Returns a stream socket to the desired server.
     *
     * @param string $address Server url string.
     * @param float  $timeout Number of seconds until the connect() system call should timeout.
     * @param mixed  $context
     *
     * @return resource
     *
     * @throws Exception
     */
    private function getStream($address, $timeout, $context)
    {
        $errno = null;
        $errstr = null;
        
        \set_error_handler(
            static function () {
                return true;
            }
        );
        
        $fp = \stream_socket_client($address, $errno, $errstr, $timeout, \STREAM_CLIENT_CONNECT, $context);
        \restore_error_handler();
        
        if ($fp === false) {
            throw Exception::forStreamSocketClientError($errstr, $errno);
        }
        
        $timeout = \number_format($timeout, 3);
        $seconds = \floor($timeout);
        $microseconds = (($timeout - $seconds) * 1000);
        \stream_set_timeout($fp, $seconds, $microseconds);
        
        return $fp;
    }
    
    /**
     * Server information.
     *
     * @var mixed
     */
    private $serverInfo;
    
    /**
     * Process information returned by the server after connection.
     *
     * @param string $connectionResponse INFO message.
     */
    private function processServerInfo($connectionResponse) : void
    {
        $this->serverInfo = new ServerInfo($connectionResponse);
    }
    
    /**
     * Returns current connected server ID.
     *
     * @return string Server ID.
     */
    public function connectedServerID() : string
    {
        return $this->serverInfo->getServerID();
    }
    
    /**
     * Constructor.
     *
     * @param ConnectionOptions $options Connection options object.
     */
    public function __construct(ConnectionOptions $options = null)
    {
        $this->options = $options;
        
        // FIXME: Remove this redundancy, as we now require PHP ^7.1 for the package.
        if (\PHP_VERSION_ID > 70000 === true) {
            $this->randomGenerator = new Php71RandomGenerator();
        } else {
            $randomFactory = new Factory();
            $this->randomGenerator = $randomFactory->getLowStrengthGenerator();
        }
        
        if ($options === null) {
            $this->options = new ConnectionOptions();
        }
    }
    
    /**
     * Sends data thought the stream.
     *
     * @param string $payload Message data.
     *
     * @throws \RuntimeException Occurs if sending data fails.
     */
    private function send($payload) : void
    {
        $msg = $payload . "\r\n";
        $len = \strlen($msg);
        
        while (true) {
            $written = @\fwrite($this->streamSocket, $msg);
            
            if ($written === false) {
                throw new \RuntimeException('Error sending data');
            }
            
            if ($written === 0) {
                throw new \RuntimeException('Broken pipe or closed connection');
            }
            
            $len -= $written;
            
            if ($len > 0) {
                $msg = \substr($msg, 0 - $len);
            } else {
                break;
            }
        }
        
        if ($this->debug === true) {
            \printf('>>>> %s', $msg);
        }
    }
    
    /**
     * Receives a message through the stream.
     *
     * @param int $len Number of bytes to receive.
     *
     * @return string
     */
    private function receive(int $len = 0) : string
    {
        if ($len > 0) {
            $chunkSize = $this->chunkSize;
            $line = null;
            $receivedBytes = 0;
            
            while ($receivedBytes < $len) {
                $bytesLeft = ($len - $receivedBytes);
                
                if ($bytesLeft < $this->chunkSize) {
                    $chunkSize = $bytesLeft;
                }
                
                $readChunk = \fread($this->streamSocket, $chunkSize);
                $receivedBytes += \strlen($readChunk);
                $line .= $readChunk;
            }
        } else {
            $line = \fgets($this->streamSocket);
        }
        
        if ($this->debug === true) {
            \printf('<<<< %s\r\n', $line);
        }
        
        return $line;
    }
    
    /**
     * Handles PING command.
     *
     * @throws \RuntimeException Occurs if sending data fails.
     */
    private function handlePING() : void
    {
        $this->send('PONG');
    }
    
    /**
     * Handles MSG command.
     *
     * @param string $line Message command from Nats.
     *
     * @throws Exception Occurs if subscription not found.
     *
     * @codeCoverageIgnore
     */
    private function handleMSG($line) : void
    {
        $parts = \explode(' ', $line);
        $subject = null;
        $length = \trim($parts[3]);
        $sid = $parts[2];
        
        if (\count($parts) === 5) {
            $length = \trim($parts[4]);
            $subject = $parts[3];
        } elseif (\count($parts) === 4) {
            $length = \trim($parts[3]);
            $subject = $parts[1];
        }
        
        $payload = $this->receive((int)$length);
        $msg = new Message($subject, $payload, $sid, $this);
        
        if (isset($this->subscriptions[$sid]) === false) {
            throw Exception::forSubscriptionNotFound($sid);
        }
        
        $func = $this->subscriptions[$sid];
        
        if (\is_callable($func) === true) {
            $func($msg);
        } else {
            throw Exception::forSubscriptionCallbackInvalid($sid);
        }
    }
    
    /**
     * Connect to server.
     *
     * @param float|int $timeout Number of seconds until the connect() system call should timeout.
     *
     * @throws \RuntimeException Occurs if sending data fails.
     * @throws \Exception Occurs if connection fails.
     */
    public function connect($timeout = null) : void
    {
        if ($timeout === null) {
            $timeout = (int)\ini_get('default_socket_timeout');
        }
        
        $this->timeout = $timeout;
        
        $this->streamSocket = $this->getStream(
            $this->options->getAddress(),
            $timeout,
            $this->options->getStreamContext()
        );
        
        $this->setStreamTimeout($timeout);
        
        $infoResponse = $this->receive();
        
        if ($this->isErrorResponse($infoResponse) === true) {
            throw Exception::forFailedConnection($infoResponse);
        }
        
        $this->processServerInfo($infoResponse);
        
        if ($this->serverInfo->isTLSRequired()) {
            \set_error_handler(
                static function ($errNumber, $errString, $errFile, $errLine) {
                    \restore_error_handler();
                    throw Exception::forFailedConnection($errString);
                }
            );
            
            if (!\stream_socket_enable_crypto(
                $this->streamSocket,
                true,
                \STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
            )) {
                throw Exception::forFailedConnection('Error negotiating crypto');
            }
            
            \restore_error_handler();
        }
        
        $msg = "CONNECT {$this->options}";
        $this->send($msg);
        $this->ping();
        $pingResponse = $this->receive();
        
        if ($this->isErrorResponse($pingResponse) === true) {
            throw Exception::forFailedPing($pingResponse);
        }
    }
    
    /**
     * Sends PING message.
     *
     * @throws \RuntimeException Occurs if sending data fails.
     */
    public function ping() : void
    {
        $msg = 'PING';
        $this->send($msg);
        
        ++$this->pings;
    }
    
    /**
     * Request does a request and executes a callback with the response.
     *
     * @param string   $subject  Message topic.
     * @param string   $payload  Message data.
     * @param \Closure $callback Closure to be executed as callback.
     *
     * @throws Exception Occurs if subscription not found.
     * @throws \RuntimeException Occurs if sending data fails.
     */
    public function request($subject, $payload, \Closure $callback) : void
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $inbox = \uniqid('_INBOX.');
        
        $sid = $this->subscribe(
            $inbox,
            $callback
        );
        
        $this->unsubscribe($sid, 1);
        $this->publish($subject, $payload, $inbox);
        $this->wait(1);
    }
    
    /**
     * Subscribes to an specific event given a subject.
     *
     * @param string   $subject  Message topic.
     * @param \Closure $callback Closure to be executed as callback.
     *
     * @return string The SID of the subscription.
     *
     * @throws \RuntimeException Occurs if sending data fails.
     */
    public function subscribe($subject, \Closure $callback) : string
    {
        $sid = $this->randomGenerator->generateString(16);
        $msg = "SUB {$subject} {$sid}";
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
     * @return string The SID of the subscription.
     *
     * @throws \RuntimeException Occurs if sending data fails.
     */
    public function queueSubscribe($subject, $queue, \Closure $callback) : string
    {
        $sid = $this->randomGenerator->generateString(16);
        $msg = "SUB {$subject} {$queue} {$sid}";
        $this->send($msg);
        $this->subscriptions[$sid] = $callback;
        
        return $sid;
    }
    
    /**
     * Unsubscribe from a event given a subject.
     *
     * @param string  $sid      Subscription ID.
     * @param integer $quantity Quantity of messages.
     *
     * @return void
     *
     * @throws \RuntimeException Occurs if sending data fails.
     */
    public function unsubscribe($sid, $quantity = null) : void
    {
        $msg = "UNSUB {$sid}";
        if ($quantity !== null) {
            $msg .= " {$quantity}";
        }
        
        $this->send($msg);
        
        if ($quantity === null) {
            unset($this->subscriptions[$sid]);
        }
    }
    
    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param string $subject Message topic.
     * @param string $payload Message data.
     * @param string $inbox   Message inbox.
     *
     * @throws \RuntimeException Occurs if sending data fails.
     */
    public function publish($subject, $payload = null, $inbox = null) : void
    {
        $msg = "PUB {$subject}";
        
        if ($inbox !== null) {
            $msg .= " {$inbox}";
        }
        
        $msg .= ' ' . \strlen($payload) . "\r\n" . $payload;
        
        $this->send($msg);
        
        ++$this->pubs;
    }
    
    /**
     * Waits for messages.
     *
     * @param int $quantity Number of messages to wait for.
     *
     * @return Connection|null $connection Connection object.
     *
     * @throws Exception Occurs if subscription not found.
     * @throws ConnectionLostException Occurs if the underlying stream disappears.
     * @throws \RuntimeException Occurs if sending data fails.
     */
    public function wait(int $quantity = 0) : ?Connection
    {
        $count = 0;
        $info = $this->getStreamMetaData();
        
        while (
            \is_resource($this->streamSocket) === true
            && \feof($this->streamSocket) === false
            && empty($info['timed_out']) === true
        ) {
            $line = $this->receive();
            
            if ($line === false) {
                return null;
            }
            
            if (\strpos($line, 'PING') === 0) {
                $this->handlePING();
            }
            
            if (\strpos($line, 'MSG') === 0) {
                $count++;
                $this->handleMSG($line);
                
                if (($quantity !== 0) && ($count >= $quantity)) {
                    return $this;
                }
            }
            
            $info = $this->getStreamMetaData();
        }
        
        $this->close();
        
        return $this;
    }
    
    /**
     * Get the meta-data associated with the underlying stream.
     *
     * @return array
     */
    private function getStreamMetaData() : array
    {
        if ($this->streamSocket === null) {
            throw new ConnectionLostException();
        }
        
        return \stream_get_meta_data($this->streamSocket);
    }
    
    /**
     * Reconnects to the server.
     *
     * @throws \RuntimeException Occurs if sending data fails.
     * @throws \Exception Occurs if connection fails.
     */
    public function reconnect() : void
    {
        ++$this->reconnects;
        
        $this->close();
        $this->connect($this->timeout);
    }
    
    /**
     * Close will close the connection to the server.
     */
    public function close() : void
    {
        if ($this->streamSocket === null) {
            return;
        }
        
        \fclose($this->streamSocket);
        $this->streamSocket = null;
    }
}
