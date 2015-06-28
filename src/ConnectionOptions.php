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
    public $version = "0.0.0";

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
     * Constructor
     */
    public function __construct() 
    {
        $this->version = trim(file_get_contents("./VERSION"));
    }
}