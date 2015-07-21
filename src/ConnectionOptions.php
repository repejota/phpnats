<?php
namespace Nats;

/**
 * ConnectionOptions Class
 */
class ConnectionOptions
{

    /**
     * Hostname or IP to connect
     *
     * @var string
     */
    public $host = "localhost";

    /**
     * Port number to connect
     *
     * @var integer
     */
    public $port = 4222;

    /**
     * Username to connect
     *
     * @var string
     */
    public $user = null;

    /**
     * Password to connect
     *
     * @var string
     */
    public $pass = null;

    /**
     * Language of this client
     *
     * @var string
     */
    public $lang = "php";

    /**
     * Version of this client
     *
     * @var string
     */
    public $version = "0.0.5";

    /**
     * If verbose mode is enabled
     *
     * @var boolean
     */
    public $verbose = false;

    /**
     * If pedantic mode is enabled
     *
     * @var boolean
     */
    public $pedantic = false;

    /**
     * If reconnect mode is enabled
     *
     * @var boolean
     */
    public $reconnect = true;

    /**
     * Get the URI for a server
     *
     * @return string
     */
    public function getAddress()
    {
        return "tcp://" . $this->host . ":" . $this->port;
    }

    /**
     * Get the options JSON string
     *
     * @return string
     */
    public function toJSON()
    {
        $a = [
            "lang" => $this->lang,
            "version" => $this->version,
            "verbose" => $this->verbose,
            "pedantic" => $this->pedantic
        ];
        if (!is_null($this->user)) {
            $a["user"] = $this->user;
        }
        if (!is_null($this->pass)) {
            $a["pass"] = $this->pass;
        }
        return json_encode($a);
    }
}
