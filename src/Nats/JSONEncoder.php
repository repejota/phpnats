<?php
namespace Nats;

/**
 * Class JSONEncoder
 * @package Nats
 */
class JSONEncoder extends Encoder {

    public function encode($payload) {
        $payload = json_encode($payload);
        return $payload;
    }

    public function decode($payload) {
        $payload = json_decode($payload);
        return $payload;
    }
}