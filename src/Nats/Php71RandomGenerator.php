<?php


namespace Nats;



class Php71RandomGenerator
{
    /**
     * A simple wrapper on random_bytes
     *
     * @param $len
     * @return string
     */
    public function generateString($len) {
        return bin2hex(random_bytes($len));
    }
}