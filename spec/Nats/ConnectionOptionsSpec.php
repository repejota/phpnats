<?php

namespace spec\Nats;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConnectionOptionsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Nats\ConnectionOptions');
    }
}
