<?php
namespace Nats\Encoders;

/**
 * Interface Encoder
 *
 * @package Nats\Encoders
 */
interface Encoder
{


    /**
     * Encodes a message.
     *
     * @param string $payload Message to decode.
     * @param array $headers
     *
     * @return array - encoded payload and headers values
     */
    public function encode($payload, $headers = []);

    /**
     * Decodes a message.
     *
     * @param string $payload Message to decode.
     * @param array $headers
     *
     * @return mixed
     */
    public function decode($payload, $headers = []);
}
