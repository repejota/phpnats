<?php
namespace Nats\Encoders;

/**
 * Class YAMLEncoder
 *
 * Encodes and decodes messages in YAML format.
 *
 * @package Nats
 */
class YAMLEncoder implements Encoder
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
        $payload = yaml_emit($payload);
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
        $payload = yaml_parse($payload);
        return $payload;
    }
}
