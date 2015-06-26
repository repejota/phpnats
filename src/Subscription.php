<?php
namespace Nats;

/**
 * Class Subscription
 *
 * @package Nats
 */
class Subscription {

    /**
     * @var
     */
    private $id;

    /**
     * @var
     */
    private $subject;

    /**
     * @var
     */
    private $queue;

    /**
     * @var
     */
    private $connetion;

    /**
     * @var
     */
    private $callback;

    /**
     * @var
     */
    private $received;

    /**
     * @var
     */
    private $delivered;

    /**
     * @var
     */
    private $bytes;

    /**
     * @var
     */
    private $max;

    /**
     * @param $sid
     * @param $subject
     * @param $queue
     * @param $callback
     * @param $connection
     */
    public function __construct($sid, $subject, $queue, $callback, $connection) {
        $this->sid = $sid;
        $this->subject = $subject;
        $this->queue = $queue;
        $this->callback = $callback;
        $this->connection = $connection;
        $this->received = 0;
        $this->delivered = 0;
        $this->bytes = 0;
        $this->max = 0;
    }

    /**
     * @param $msg
     * @return mixed
     */
    public function handle_msg($msg) {
        return $this->callback($msg);
    }

}