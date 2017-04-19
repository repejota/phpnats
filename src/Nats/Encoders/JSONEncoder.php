<?php
namespace Nats\Encoders;

/**
 * Class JSONEncoder
 * @package Nats
 */
class JSONEncoder implements \Nats\Encoders\Encoder {

    public function encode($payload) {
        $payload = json_encode($payload);
        return $payload;
    }

    public function decode($payload) {
        $payload = json_decode($payload);
        return $payload;
    }
}