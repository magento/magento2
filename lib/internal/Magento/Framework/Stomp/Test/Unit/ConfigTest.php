<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Test\Unit;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Stomp\Config;
use Magento\Framework\Stomp\Connection\Factory as ConnectionFactory;
use Magento\Framework\Stomp\Connection\FactoryOptions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Stomp\Network\Connection;

class ConfigTest extends TestCase
{
    private const DEFAULT_CONFIG = [
        Config::HOST => 'localhost',
        Config::PORT => '61613',
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
    private $stompConfig;

    /**
     * @return void
     * @throws Exception
     */
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
        $this->stompConfig = new Config($this->deploymentConfigMock, 'stomp', $this->connectionFactory);
    }

    /**
     * @return void
     */
    public function testGetNullConfig()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unknown connection name stomp');
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn(null);

        $this->stompConfig->getValue(Config::HOST);
    }

    /**
     * @return void
     */
    public function testGetEmptyConfig()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Unknown connection name stomp');
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn([]);

        $this->stompConfig->getValue(Config::HOST);
    }

    /**
     * @return void
     */
    public function testGetStandardConfig()
    {
        $expectedHost = 'example.com';
        $expectedPort = 61613;
        $expectedUsername = 'guest_username';
        $expectedPassword = 'guest_password';
        $expectedSsl = false;
        $expectedSslOptions = ['some' => 'value'];

        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn([
                Config::STOMP_CONFIG => [
                    'host' => $expectedHost,
                    'port' => $expectedPort,
                    'user' => $expectedUsername,
                    'password' => $expectedPassword,
                    'ssl' => $expectedSsl,
                    'ssl_options' => $expectedSslOptions,
                    'randomKey' => 'randomValue',
                ]
            ]);

        $this->assertEquals($expectedHost, $this->stompConfig->getValue(Config::HOST));
        $this->assertEquals($expectedPort, $this->stompConfig->getValue(Config::PORT));
        $this->assertEquals($expectedUsername, $this->stompConfig->getValue(Config::USERNAME));
        $this->assertEquals($expectedPassword, $this->stompConfig->getValue(Config::PASSWORD));
        $this->assertEquals($expectedSsl, $this->stompConfig->getValue(Config::SSL));
        $this->assertEquals($expectedSslOptions, $this->stompConfig->getValue(Config::SSL_OPTIONS));
        $this->assertEquals('randomValue', $this->stompConfig->getValue('randomKey'));
    }

    /**
     * @return void
     */
    public function testGetCustomConfig()
    {
        $stompConfig = new Config($this->deploymentConfigMock, 'connection-01');
        $expectedHost = 'example.com';
        $expectedPort = 61613;
        $expectedUsername = 'guest_username';
        $expectedPassword = 'guest_password';
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
                        'ssl' => $expectedSsl,
                        'randomKey' => 'randomValue',
                    ]
                ]
            ]);

        $this->assertEquals($expectedHost, $stompConfig->getValue(Config::HOST));
        $this->assertEquals($expectedPort, $stompConfig->getValue(Config::PORT));
        $this->assertEquals($expectedUsername, $stompConfig->getValue(Config::USERNAME));
        $this->assertEquals($expectedPassword, $stompConfig->getValue(Config::PASSWORD));
        $this->assertEquals($expectedSsl, $stompConfig->getValue(Config::SSL));
        $this->assertEquals('randomValue', $stompConfig->getValue('randomKey'));
    }

    /**
     * @param array $config
     * @param array $expected
     * @return void     */
    #[DataProvider('configDataProvider')]
    public function testCreateConnection(array $config, array $expected): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn(
                [
                    Config::STOMP_CONFIG => $config
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
        $this->stompConfig->getConnection();
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

    /**
     * @return void
     * @throws Exception
     */
    public function testGetConnection(): void
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with(Config::QUEUE_CONFIG)
            ->willReturn([Config::STOMP_CONFIG => self::DEFAULT_CONFIG]);
        $connectionMock = $this->createMock(Connection::class);
        $this->connectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($connectionMock);

        $this->assertEquals($connectionMock, $this->stompConfig->getConnection());
    }
}
