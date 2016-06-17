<?php
namespace spec\Nats;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConnectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Nats\Connection');
    }

    function it_has_ping_count_to_zero()
    {
        $this->pingsCount()->shouldBe(0);
    }

    function it_has_pubs_count_to_zero()
    {
        $this->pubsCount()->shouldBe(0);
    }

    function it_has_reconnects_count_to_zero()
    {
        $this->reconnectsCount()->shouldBe(0);
    }

    function it_has_subscriptions_count_to_zero()
    {
        $this->subscriptionsCount()->shouldBe(0);
    }

    function it_subscriptions_array_is_empty()
    {
        $this->getSubscriptions()->shouldHaveCount(0);
    }

    function it_is_disconnected()
    {
        $this->isConnected()->shouldBe(false);
    }

    function it_can_connect_and_disconnect_with_default_options()
    {
        $this->connect();
        $this->shouldHaveType('Nats\Connection');
        $this->isConnected()->shouldBe(true);
        $this->close();
        $this->isConnected()->shouldBe(false);
    }
}