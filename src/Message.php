<?php
namespace Nats;


/**
 * Class Message
 *
 * @package Nats
 */
class Message {

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
    private $size;

    /**
     * @var
     */
    private $data;

    /**
     * @var
     */
    private $reply;

    /**
     * @param $sid
     * @param $subject
     * @param $size
     * @param $data
     * @param null $reply
     */
    public function __construct($sid, $subject, $size, $data, $reply=null) {
        $this->id = $id;
        $this->subject = $subject;
        $this->size = $size;
        $this->data = $data;
        $this->reply = $reply;
    }

}