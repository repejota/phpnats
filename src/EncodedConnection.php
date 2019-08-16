<?php
namespace Nats;

use Nats\Encoders\Encoder;

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
     * @var Encoder|null
     */
    private $encoder = null;


    /**
     * EncodedConnection constructor.
     *
     * @param ConnectionOptions $options Connection options object.
     * @param Encoder|null      $encoder Encoder to use with the payload.
     */
    public function __construct(ConnectionOptions $options = null, Encoder $encoder = null)
    {
        $this->encoder = $encoder;
        parent::__construct($options);
    }

    /**
     * Publish publishes the data argument to the given subject.
     *
     * @param string $subject Message topic.
     * @param string $payload Message data.
     * @param string $inbox   Message inbox.
     *
     * @return void
     */
    public function publish($subject, $payload = null, $inbox = null) : void
    {
        $payload = $this->encoder->encode($payload);
        parent::publish($subject, $payload, $inbox);
    }

    /**
     * Subscribes to an specific event given a subject.
     *
     * @param string   $subject  Message topic.
     * @param \Closure $callback Closure to be executed as callback.
     *
     * @return string The SID of the subscription.
     */
    public function subscribe($subject, \Closure $callback) : string
    {
        $c = function ($message) use ($callback) {
            $message->setBody($this->encoder->decode($message->getBody()));
            $callback($message);
        };
    
        return parent::subscribe($subject, $c);
    }

    /**
     * Subscribes to an specific event given a subject and a queue.
     *
     * @param string   $subject  Message topic.
     * @param string   $queue    Queue name.
     * @param \Closure $callback Closure to be executed as callback.
     *
     * @return string The SID of the subscription.
     */
    public function queueSubscribe($subject, $queue, \Closure $callback) : string
    {
        $c = function ($message) use ($callback) {
            $message->setBody($this->encoder->decode($message->getBody()));
            $callback($message);
        };
    
        return parent::queueSubscribe($subject, $queue, $c);
    }
}
