<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\Config;
use Magento\Framework\Amqp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Amqp\Connection\FactoryOptions;
use Magento\Framework\App\DeploymentConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var Config
     */
    private $amqpConfig;

    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigData'])
            ->getMock();
        $this->connectionFactory = $this->createMock(ConnectionFactory::class);
        $this->amqpConfig = new Config($this->deploymentConfigMock, 'amqp', $this->connectionFactory);
    }

    public function testGetNullConfig()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unknown connection name amqp');
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn(null);

        $this->amqpConfig->getValue(Config::HOST);
    }

    public function testGetEmptyConfig()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unknown connection name amqp');
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn([]);

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
            ->willReturn([
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
            ]);

        $this->assertEquals($expectedHost, $this->amqpConfig->getValue(Config::HOST));
        $this->assertEquals($expectedPort, $this->amqpConfig->getValue(Config::PORT));
        $this->assertEquals($expectedUsername, $this->amqpConfig->getValue(Config::USERNAME));
        $this->assertEquals($expectedPassword, $this->amqpConfig->getValue(Config::PASSWORD));
        $this->assertEquals($expectedVirtualHost, $this->amqpConfig->getValue(Config::VIRTUALHOST));
        $this->assertEquals($expectedSsl, $this->amqpConfig->getValue(Config::SSL));
        $this->assertEquals($expectedSslOptions, $this->amqpConfig->getValue(Config::SSL_OPTIONS));
        $this->assertEquals('randomValue', $this->amqpConfig->getValue('randomKey'));
    }

    public function testGetCustomConfig()
    {
        $amqpConfig = new Config($this->deploymentConfigMock, 'connection-01');
        $expectedHost = 'example.com';
        $expectedPort = 5672;
        $expectedUsername = 'guest_username';
        $expectedPassword = 'guest_password';
        $expectedVirtualHost = '/';
        $expectedSsl = ['some' => 'value'];

        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn([
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
            ]);

        $this->assertEquals($expectedHost, $amqpConfig->getValue(Config::HOST));
        $this->assertEquals($expectedPort, $amqpConfig->getValue(Config::PORT));
        $this->assertEquals($expectedUsername, $amqpConfig->getValue(Config::USERNAME));
        $this->assertEquals($expectedPassword, $amqpConfig->getValue(Config::PASSWORD));
        $this->assertEquals($expectedVirtualHost, $amqpConfig->getValue(Config::VIRTUALHOST));
        $this->assertEquals($expectedSsl, $amqpConfig->getValue(Config::SSL));
        $this->assertEquals('randomValue', $amqpConfig->getValue('randomKey'));
    }

    /**
     * @param array $config
     * @param array $expected
     * @return void
     * @dataProvider configDataProvider
     */
    public function testCreateConnection(array $config, array $expected): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn(
                [
                    Config::AMQP_CONFIG => $config
                ]
            );
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(
                    function (FactoryOptions $factoryOptions) use ($expected) {
                        $actual = [];
                        foreach (array_keys($expected) as $method) {
                            $actual[$method] = $factoryOptions->$method();
                        }
                        return $actual === $expected;
                    }
                )
            );
        $this->amqpConfig->getChannel();
    }

    /**
     * @return array
     */
    public function configDataProvider(): array
    {
        return [
            [
                [
                    Config::HOST => 'localhost',
                    Config::PORT => '5672',
                    Config::USERNAME => 'user',
                    Config::PASSWORD => 'pass',
                    Config::VIRTUALHOST => '/',
                ],
                [
                    'isSslEnabled' => false
                ]
            ],
            [
                [
                    Config::HOST => 'localhost',
                    Config::PORT => '5672',
                    Config::USERNAME => 'user',
                    Config::PASSWORD => 'pass',
                    Config::VIRTUALHOST => '/',
                    Config::SSL => ' true ',
                ],
                [
                    'isSslEnabled' => true
                ]
            ]
        ];
    }
}
