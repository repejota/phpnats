<?php
namespace Nats;

/**
 * Class Encoder
 */
abstract class Encoder
{
    /**
     * @return mixed
     */
    abstract public function encode($payload);

    /**
     * @return mixed
     */
    abstract public function decode($payload);
}
