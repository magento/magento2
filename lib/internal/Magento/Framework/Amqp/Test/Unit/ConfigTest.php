<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\Config;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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
        $this->deploymentConfigMock = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigData'])
            ->getMock();
        $this->amqpConfig = $this->objectManager->getObject(
            \Magento\Framework\Amqp\Config::class,
            [
                'config' => $this->deploymentConfigMock,
            ]
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown connection name amqp
     */
    public function testGetNullConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->will($this->returnValue(null));

        $this->amqpConfig->getValue(Config::HOST);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown connection name amqp
     */
    public function testGetEmptyConfig()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->will($this->returnValue([]));

        $this->amqpConfig->getValue(Config::HOST);
    }

    public function testGetStandardConfig()
    {
        $expectedHost = 'example.com';
        $expectedPort = 5672;
        $expectedUsername = 'guest_username';
        $expectedPassword = 'guest_password';
        $expectedVirtualHost = '/';
        $expectedSsl = false;
        $expectedSslOptions = ['some' => 'value'];

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
                        'ssl_options' => $expectedSslOptions,
                        'randomKey' => 'randomValue',
                    ]
                ]
            ));

        $this->assertSame($expectedHost, $this->amqpConfig->getValue(Config::HOST));
        $this->assertSame($expectedPort, $this->amqpConfig->getValue(Config::PORT));
        $this->assertSame($expectedUsername, $this->amqpConfig->getValue(Config::USERNAME));
        $this->assertSame($expectedPassword, $this->amqpConfig->getValue(Config::PASSWORD));
        $this->assertSame($expectedVirtualHost, $this->amqpConfig->getValue(Config::VIRTUALHOST));
        $this->assertSame($expectedSsl, $this->amqpConfig->getValue(Config::SSL));
        $this->assertSame($expectedSslOptions, $this->amqpConfig->getValue(Config::SSL_OPTIONS));
        $this->assertSame('randomValue', $this->amqpConfig->getValue('randomKey'));
    }

    public function testGetCustomConfig()
    {
        $amqpConfig = new \Magento\Framework\Amqp\Config($this->deploymentConfigMock, 'connection-01');
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
                    'connections' => [
                        'connection-01' => [
                            'host' => $expectedHost,
                            'port' => $expectedPort,
                            'user' => $expectedUsername,
                            'password' => $expectedPassword,
                            'virtualhost' => $expectedVirtualHost,
                            'ssl' => $expectedSsl,
                            'randomKey' => 'randomValue',
                        ]
                    ]
                ]
            ));

        $this->assertSame($expectedHost, $amqpConfig->getValue(Config::HOST));
        $this->assertSame($expectedPort, $amqpConfig->getValue(Config::PORT));
        $this->assertSame($expectedUsername, $amqpConfig->getValue(Config::USERNAME));
        $this->assertSame($expectedPassword, $amqpConfig->getValue(Config::PASSWORD));
        $this->assertSame($expectedVirtualHost, $amqpConfig->getValue(Config::VIRTUALHOST));
        $this->assertSame($expectedSsl, $amqpConfig->getValue(Config::SSL));
        $this->assertSame('randomValue', $amqpConfig->getValue('randomKey'));
    }
}
