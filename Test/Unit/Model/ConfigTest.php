<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Test\Unit\Model;

use Magento\Amqp\Model\Config;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfigMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $amqpConfig;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->deploymentConfigMock = $this->getMockBuilder('Magento\Framework\App\DeploymentConfig')
            ->disableOriginalConstructor()
            ->setMethods(['getConfigData'])
            ->getMock();
        $this->amqpConfig = $this->objectManager->getObject(
            'Magento\Amqp\Model\Config',
            [
                'config' => $this->deploymentConfigMock,
            ]
        );
    }

    public function testGetNullConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->will($this->returnValue(null));

        $this->assertNull($this->amqpConfig->getValue(Config::HOST));
        $this->assertNull($this->amqpConfig->getValue(Config::PORT));
        $this->assertNull($this->amqpConfig->getValue(Config::USERNAME));
        $this->assertNull($this->amqpConfig->getValue(Config::PASSWORD));
        $this->assertNull($this->amqpConfig->getValue(Config::VIRTUALHOST));
        $this->assertNull($this->amqpConfig->getValue(Config::SSL));
    }

    public function testGetEmptyConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->will($this->returnValue([]));

        $this->assertNull($this->amqpConfig->getValue(Config::HOST));
        $this->assertNull($this->amqpConfig->getValue(Config::PORT));
        $this->assertNull($this->amqpConfig->getValue(Config::USERNAME));
        $this->assertNull($this->amqpConfig->getValue(Config::PASSWORD));
        $this->assertNull($this->amqpConfig->getValue(Config::VIRTUALHOST));
        $this->assertNull($this->amqpConfig->getValue(Config::SSL));
    }

    public function testGetStandardConfig()
    {
        $expectedHost = 'example.com';
        $expectedPort = 5672;
        $expectedUsername = 'guest_username';
        $expectedPassword = 'guest_password';
        $expectedVirtualHost = '/';
        $expectedSsl = ['some' => 'value'];

        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->will($this->returnValue(
                [
                    Config::AMQP_CONFIG => [
                        'host' => $expectedHost,
                        'port' => $expectedPort,
                        'user' => $expectedUsername,
                        'password' => $expectedPassword,
                        'virtualhost' => $expectedVirtualHost,
                        'ssl' => $expectedSsl,
                        'randomKey' => 'randomValue',
                    ]
                ]
            ));

        $this->assertEquals($expectedHost, $this->amqpConfig->getValue(Config::HOST));
        $this->assertEquals($expectedPort, $this->amqpConfig->getValue(Config::PORT));
        $this->assertEquals($expectedUsername, $this->amqpConfig->getValue(Config::USERNAME));
        $this->assertEquals($expectedPassword, $this->amqpConfig->getValue(Config::PASSWORD));
        $this->assertEquals($expectedVirtualHost, $this->amqpConfig->getValue(Config::VIRTUALHOST));
        $this->assertEquals($expectedSsl, $this->amqpConfig->getValue(Config::SSL));
        $this->assertEquals('randomValue', $this->amqpConfig->getValue('randomKey'));
    }
}
