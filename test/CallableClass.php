<?php
namespace Nats\tests\Unit;

class CallableClass
{
    private $tmpStorage = [];
    private $tmpCallbacks = [];

    public function requestSubTest($res)
    {
        $res->reply('Hello, '.$res->getBody());
    }
    public function __invoke($res)
    {
        $msg = $res->getBody();
        $p = explode(' ', $msg);
        $count = array_pop($p);
        if (isset($this->tmpCallbacks[$count])) {
            $cb = $this->tmpCallbacks[$count];
            $cb($msg, $count);
            unset($this->tmpCallbacks[$count]);
        } else {
            $this->tmpStorage[$count] = $msg;
        }
    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function test($i, callable $param)
    {
        if (isset($this->tmpStorage[$i])) {
            $param($this->tmpStorage[$i], $i);
        } else {
            $this->tmpCallbacks[$i] = $param;
        }
    }
}
