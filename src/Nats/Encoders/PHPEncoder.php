<?php
namespace Nats\Encoders;

/**
 * Class PHPEncoder
 *
 * Encodes and decodes messages in PHP format.
 *
 * @package Nats
 */
class PHPEncoder implements Encoder
{


    /**
     * Encodes a message.
     *
     * @param string $payload Message to decode.
     * @param array $headers
     *
     * @return array - encoded payload and headers values
     */
    public function encode($payload, $headers = [])
    {
        $payload = serialize($payload);
        return [$payload, $headers];
    }

    /**
     * Decodes a message.
     *
     * @param string $payload Message to decode.
     * @param array $headers
     *
     * @return mixed
     */
    public function decode($payload, $headers = [])
    {
        $payload = unserialize($payload);
        return $payload;
    }
}
