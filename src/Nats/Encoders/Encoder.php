<?php
namespace Nats\Encoders;

/**
 * Interface Encoder
 * @package Nats\Encoders
 */
interface Encoder
{
    /**
     * @return mixed
     */
    public function encode($payload);

    /**
     * @return mixed
     */
    public function decode($payload);
}
