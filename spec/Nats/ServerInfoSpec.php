<?php
namespace spec\Nats;

use PhpSpec\ObjectBehavior;

class ServerInfoSpec extends ObjectBehavior
{
    function let()
    {
        $message = 'INFO {"server_id":"68mIHHvevtmp5b6AzxcBfn","version":"0.9.6","go":"go1.7.3","host":"0.0.0.0","port":4222,"auth_required":false,"ssl_required":false,"tls_required":false,"tls_verify":false,"max_payload":1048576}';
        $this->beConstructedWith($message);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Nats\ServerInfo');
    }
}