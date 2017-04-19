<?php
namespace Nats;

/**
 * Class EncodedConnection
 *
 * @package Nats
 */
class EncodedConnection extends Connection
{

    /**
     * Encoder for this connection.
     *
     * @var \Nats\Encoders\Encoder|null
     */
    private $encoder = null;


    /**
     * EncodedConnection constructor.
     *
     * @param ConnectionOptions           $options Connection options object.
     * @param \Nats\Encoders\Encoder|null $encoder Encoder to use with the payload.
     */
    public function __construct(ConnectionOptions $options = null, \Nats\Encoders\Encoder $encoder = null)
    {
        $this->encoder = $encoder;
        parent::__construct($options);
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
        $payload        = $this->encoder->encode($payload);
        $decodeCallback = function ($payload) use ($callback) {
            $callback($this->encoder->decode($payload));
        };
        parent::request($subject, $payload, $decodeCallback, $wait);
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
        $payload = $this->encoder->encode($payload);
        parent::publish($subject, $payload);
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
        $decodeCallback = function ($payload) use ($callback) {
            $callback($this->encoder->decode($payload));
        };
        return parent::subscribe($subject, $decodeCallback);
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
        $decodeCallback = function ($payload) use ($callback) {
            $callback($this->encoder->decode($payload));
        };
        return parent::queueSubscribe($subject, $queue, $decodeCallback);
    }
}
