<?php
/**
 * ConnectionOptions Class
 *
 * PHP version 5
 *
 * @category Class
 * @package  Nats
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
 */
namespace Nats;

/**
 * ConnectionOptions Class
 *
 * @category Class
 * @package  Nats\Tests\Unit
 * @author   Raül Përez <repejota@gmail.com>
 * @license  http://opensource.org/licenses/MIT The MIT License (MIT)
 * @link     https://github.com/repejota/phpnats
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
     * @var int
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
     * @var bool
     */
    public $verbose = false;

    /**
     * If pedantic mode is enabled
     *
     * @var bool
     */
    public $pedantic = false;

    /**
     * If reconnect mode is enabled
     *
     * @var bool
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
