<?php

/**
 * Connection Class.
 *
 * PHP version 5
 *
 * @category Class
 *
 * @author  Raül Përez <repejota@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link https://github.com/repejota/phpnats
 */

namespace Nats;

/**
 * Connection Class.
 *
 * @category Class
 *
 * @author  Raül Përez <repejota@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link https://github.com/repejota/phpnats
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
     * @param string $host name, by default "localhost"
     * @param int    $port number, by default 4222
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
     * @param string $payload message data
     */
    private function send($payload)
    {
        $msg = $payload."\r\n";
        fwrite($this->streamSocket, $msg, strlen($msg));
    }

    /**
     * Receives a message thought the stream.
     *
     * @param int $len Number of bytes to receive
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
     * @param string $address Server url string
     *
     * @return resource
     */
    private function getStream($address)
    {
        $fp = stream_socket_client($address, $errno, $errstr, STREAM_CLIENT_CONNECT);
        if (!$fp) {
            echo '!!!!!!! '.$errstr.' - '.$errno;
        }
        //stream_set_blocking($fp, 0);
        return $fp;
    }

    /**
     * Checks if the client is connected to a server.
     *
     * @return bool
     */
    public function isConnected()
    {
        return isset($this->streamSocket);
    }

    /**
     * Connect to server.
     */
    public function connect()
    {
        $this->streamSocket = $this->getStream($this->options->getAddress());
        $msg = 'CONNECT '.$this->options->toJSON();
        $this->send($msg);
    }

    /**
     * Sends PING message.
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
     * @param string $subject message topic
     * @param string $payload message data
     *
     * @return string
     */
    public function publish($subject, $payload)
    {
        $msg = 'PUB '.$subject.' '.strlen($payload);
        $this->send($msg);
        $this->send($payload);
        $this->pubs += 1;
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
        $msg = 'SUB '.$subject.' '.$sid;
        $this->send($msg);
        $this->subscriptions[$sid] = $callback;

        return $sid;
    }

    /**
     * Unsubscribe from a event given a subject.
     *
     * @param string $sid Subscription ID
     */
    public function unsubscribe($sid)
    {
        $msg = 'UNSUB '.$sid;
        $this->send($msg);
    }

    /**
     * Handles PING command.
     */
    private function handlePING()
    {
        $this->send('PONG');
    }

    /**
     * Handles MSG command.
     *
     * @param string $line Message command from NATS
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
     * @param int $quantity Number of messages to wait for
     *
     * @return \Exception|void
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
                    return;
                }
            }
        }
        $this->close();

        return $this;
    }

    /**
     * Reconnects to the server.
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
        fclose($this->streamSocket);
        $this->streamSocket = null;
    }
}
