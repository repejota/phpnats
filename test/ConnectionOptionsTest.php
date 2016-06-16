<?php
/**
 * Created by PhpStorm.
 * User: isselguberna
 * Date: 29/9/15
 * Time: 23:48
 */

namespace Nats\tests\Unit;

use Nats\ConnectionOptions;

/**
 * Class ConnectionOptionsTest
 */
class ConnectionOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Connection Options getters and setters. Only necessary for code coverage.
     *
     * @return void
     */
    public function testSettersAndGetters()
    {
        $options = new ConnectionOptions();
        $options
            ->setHost('host')
            ->setPort(4222)
            ->setUser('user')
            ->setPass('password')
            ->setLang('lang')
            ->setVersion('version')
            ->setVerbose(true)
            ->setPedantic(true)
            ->setReconnect(true);

        $this->assertEquals('host', $options->getHost());
        $this->assertEquals(4222, $options->getPort());
        $this->assertEquals('user', $options->getUser());
        $this->assertEquals('password', $options->getPass());
        $this->assertEquals('lang', $options->getLang());
        $this->assertEquals('version', $options->getVersion());
        $this->assertTrue($options->isVerbose());
        $this->assertTrue($options->isPedantic());
        $this->assertTrue($options->isReconnect());
    }
}
