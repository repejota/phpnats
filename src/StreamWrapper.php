<?php

namespace Nats;

/**
 * StreamWrapper class.
 */
class StreamWrapper
{
    /**
     * Wrapper for stream_socket_client
     *
     * @param string $address Address to connect the socket.
     * @param integer $errno Number of error.
     * @param string $errstr Description of error.
     * @param float $timeout Timeout.
     * @param integer $typeStream Type of stream.
     *
     * @return resource
     */
    public function getStreamSocketClient($address, &$errno, &$errstr, $timeout, $typeStream)
    {
        $stream = stream_socket_client($address, $errno, $errstr, $timeout, $typeStream);
        $this->setStreamTimeout($stream, $timeout);
        return $stream;
    }

    /**
     * Wrapper for stream_set_timeout
     *
     * @param mixed $stream Stream.
     * @param float $seconds Seconds for timeout.
     *
     * @return boolean
     *
     */
    public function setStreamTimeout($stream, $seconds)
    {
        $timeout = number_format($seconds, 3);
        $seconds = floor($timeout);
        $microseconds = ($timeout - $seconds) * 1000;
        return stream_set_timeout($stream, $seconds, $microseconds);
    }
}
