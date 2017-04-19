<?php
namespace Nats;

/**
 * Class EncodedConnection
 * @package Nats
 */
class EncodedConnection extends Connection {

    /**
     * @var Encoder|null
     */
    private $encoder = null;

    /**
     * EncodedConnection constructor.
     * @param ConnectionOptions|null $options
     * @param Encoder|null $encoder
     */
    public function __construct(ConnectionOptions $options = null, Encoder $encoder = null) {
        $this->encoder = $encoder;
        parent::__construct($options);
    }

    /**
     * @param string $subject
     * @param string $payload
     * @param mixed $callback
     * @param int $wait
     */
    public function request($subject, $payload, $callback, $wait = 1) {
        $payload = $this->encoder->encode($payload);
        $decode_callback = function ($payload) use ($callback) {
            $callback(json_decode($payload));
        };
        parent::request($subject, $payload, $decode_callback, $wait);
    }

    /**
     * @param string $subject
     * @param null $payload
     */
    public function publish($subject, $payload = null) {
        $payload = $this->encoder->encode($payload);
        parent::publish($subject, $payload);
    }

    /**
     * @param string $subject
     * @param \Closure $callback
     */
    public function subscribe($subject, \Closure $callback) {
        $decode_callback = function ($payload) use ($callback) {
            $callback(json_decode($payload));
        };
        parent::subscribe($subject, $decode_callback);
    }

}