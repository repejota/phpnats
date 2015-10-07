<?php
namespace Nats;

/**
 * Message Class.
 */
class Message
{
    /**
     * Message Subject
     *
     * @var string
     */
    private $subject;

    /**
     * Message Body
     *
     * @var string
     */
    private $body;

    /**
     * Message Ssid
     *
     * @var string
     */
    private $sid;

    /**
     * Message related connection
     *
     * @var string
     */
    private $conn;

    /**
     * Message constructor
     * @param string $subject
     * @param string $body
     * @param string $sid
     * @param Connection $conn
     */
    public function __construct($subject, $body, $sid, $conn)
    {
        $this->setSubject($subject);
        $this->setBody($body);
        $this->setSid($sid);
        $this->setConn($conn);
    }

    /**
     * Set subject
     *
     * @param string $subject Subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body Body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set Ssid
     *
     * @param string $sid Ssid
     * @return $this
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
        return $this;
    }

    /**
     * Get Ssid
     *
     * @return string
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * String representation of a message
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getBody();
    }

    /**
     * Set Conn
     *
     * @param Connection $conn
     * @return $this
     */
    public function setConn($conn)
    {
        $this->conn = $conn;
        return $this;
    }

    /**
     * Get Conn
     *
     * @return string
     */
    public function getConn()
    {
        return $this->conn;
    }

    /**
     * Allows you reply the message with a specific body
     *
     * @param string $body
     */
    public function reply($body)
    {
        $this->getConn()->publish(
            $this->getSubject(),
            $body
        );
    }
}
