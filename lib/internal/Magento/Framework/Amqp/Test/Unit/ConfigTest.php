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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private const DEFAULT_CONFIG = [
        Config::HOST => 'localhost',
        Config::PORT => '5672',
        Config::USERNAME => 'user',
        Config::PASSWORD => 'pass',
    ];

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
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                ConnectionFactory::class,
                $this->createMock(ConnectionFactory::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigData'])
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
    public static function configDataProvider(): array
    {
        return [
            [
                self::DEFAULT_CONFIG,
                [
                    'isSslEnabled' => false
                ]
            ],
            [
                self::DEFAULT_CONFIG + [Config::SSL => ' true '],
                [
                    'isSslEnabled' => true
                ]
            ]
        ];
    }

    public function testGetChannel(): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn([Config::AMQP_CONFIG => self::DEFAULT_CONFIG]);
        $connectionMock = $this->createMock(AbstractConnection::class);
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $channelMock = $this->createMock(AMQPChannel::class);
        $connectionMock->expects($this->once())
            ->method('channel')
            ->willReturn($channelMock);
        $channelMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->atLeastOnce())
            ->method('isConnected')
            ->willReturn(true);

        $this->assertEquals($channelMock, $this->amqpConfig->getChannel());
        $this->assertEquals($channelMock, $this->amqpConfig->getChannel());
    }

    public function testGetChannelWithoutConnection(): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn([Config::AMQP_CONFIG => self::DEFAULT_CONFIG]);
        $connectionMock = $this->createMock(AbstractConnection::class);
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $channel1Mock = $this->createMock(AMQPChannel::class);
        $channel2Mock = $this->createMock(AMQPChannel::class);
        $connectionMock->expects($this->exactly(2))
            ->method('channel')
            ->willReturnOnConsecutiveCalls($channel1Mock, $channel2Mock);
        $this->amqpConfig->getChannel();
        $channel1Mock->expects($this->once())
            ->method('getConnection')
            ->willReturn(null);

        $this->assertEquals($channel2Mock, $this->amqpConfig->getChannel());
    }

    public function testGetChannelWithDisconnectedConnection(): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn([Config::AMQP_CONFIG => self::DEFAULT_CONFIG]);
        $connectionMock = $this->createMock(AbstractConnection::class);
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $channel1Mock = $this->createMock(AMQPChannel::class);
        $channel2Mock = $this->createMock(AMQPChannel::class);
        $connectionMock->expects($this->exactly(2))
            ->method('channel')
            ->willReturnOnConsecutiveCalls($channel1Mock, $channel2Mock);
        $this->amqpConfig->getChannel();
        $channel1Mock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->atLeastOnce())
            ->method('isConnected')
            ->willReturn(false);

        $this->assertEquals($channel2Mock, $this->amqpConfig->getChannel());
    }
}
