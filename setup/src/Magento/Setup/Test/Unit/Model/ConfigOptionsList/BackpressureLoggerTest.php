<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\ConfigOptionsList;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Model\ConfigOptionsList\BackpressureLogger;
use Magento\Setup\Validator\RedisConnectionValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackpressureLoggerTest extends TestCase
{
    /**
     * @var BackpressureLogger
     */
    private $configList;

    /**
     * @var RedisConnectionValidator|MockObject
     */
    private $validatorMock;
    /**
     * @var DeploymentConfig|mixed|MockObject
     */
    private $deploymentConfigMock;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(RedisConnectionValidator::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);

        $this->configList = new BackpressureLogger($this->validatorMock);
    }

    /**
     * testGetOptions
     */
    public function testGetOptions()
    {
        $options = $this->configList->getOptions();
        $this->assertCount(9, $options);
        $this->assertArrayHasKey(0, $options);
        $this->assertInstanceOf(SelectConfigOption::class, $options[0]);
        $this->assertEquals('backpressure-logger', $options[0]->getName());

        $this->assertArrayHasKey(1, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[1]);
        $this->assertEquals('backpressure-logger-redis-server', $options[1]->getName());

        $this->assertArrayHasKey(2, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[2]);
        $this->assertEquals('backpressure-logger-redis-port', $options[2]->getName());

        $this->assertArrayHasKey(3, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[3]);
        $this->assertEquals('backpressure-logger-redis-timeout', $options[3]->getName());

        $this->assertArrayHasKey(4, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[4]);
        $this->assertEquals('backpressure-logger-redis-persistent', $options[4]->getName());

        $this->assertArrayHasKey(5, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[5]);
        $this->assertEquals('backpressure-logger-redis-db', $options[5]->getName());

        $this->assertArrayHasKey(6, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[6]);
        $this->assertEquals('backpressure-logger-redis-password', $options[6]->getName());

        $this->assertArrayHasKey(7, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[7]);
        $this->assertEquals('backpressure-logger-redis-user', $options[7]->getName());

        $this->assertArrayHasKey(8, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[8]);
        $this->assertEquals('backpressure-logger-id-prefix', $options[8]->getName());
    }

    /**
     * testCreateConfigCacheRedis
     * @dataProvider dataProviderCreateConfigCacheRedis
     */
    public function testCreateConfigCacheRedis(
        array $options,
        array $deploymentConfigReturnMap,
        array $expectedConfigData
    ) {
        $this->deploymentConfigMock->method('get')->willReturnMap($deploymentConfigReturnMap);
        $configData = $this->configList->createConfig($options, $this->deploymentConfigMock);
        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function dataProviderCreateConfigCacheRedis(): array
    {
        return [
            'minimum options' => [
                'options' => ['backpressure-logger' => 'redis'],
                'deploymentConfigReturnMap' => [
                    ['backpressure/logger/options/server', null, null],
                    ['backpressure/logger/options/port', null, null],
                    ['backpressure/logger/options/timeout', null, null],
                    ['backpressure/logger/options/persistent', null, null],
                    ['backpressure/logger/options/db', null, null],
                    ['backpressure/logger/options/password', null, null],
                    ['backpressure/logger/options/user', null, null],
                    ['backpressure/logger/id-prefix', null, null],
                ],
                'expectedConfigData' => [
                    'backpressure' => [
                        'logger' => [
                            'type' => 'redis',
                            'options' => [
                                'server' => '127.0.0.1',
                                'port' => 6379,
                                'db' => 3,
                                'password' => null,
                                'timeout' => null,
                                'persistent' => '',
                                'user' => null
                            ],
                            'id-prefix' => self::expectedIdPrefix()
                        ]
                    ]
                ],
            ],
            'maximum options' => [
                'options' => [
                    'backpressure-logger' => 'redis',
                    'backpressure-logger-redis-server' => '<some-server>',
                    'backpressure-logger-redis-port' => 3344,
                    'backpressure-logger-redis-timeout' => 5,
                    'backpressure-logger-redis-persistent' => '<persistent>',
                    'backpressure-logger-redis-db' => 23,
                    'backpressure-logger-redis-password' => '<some-password>',
                    'backpressure-logger-redis-user' => '<some-user>',
                    'backpressure-logger-id-prefix' => '<some-prefix>',
                ],
                'deploymentConfigReturnMap' => [
                    ['backpressure/logger/type', null, null],
                    ['backpressure/logger/options/server', null, null],
                    ['backpressure/logger/options/port', null, null],
                    ['backpressure/logger/options/timeout', null, null],
                    ['backpressure/logger/options/persistent', null, null],
                    ['backpressure/logger/options/db', null, null],
                    ['backpressure/logger/options/password', null, null],
                    ['backpressure/logger/options/user', null, null],
                    ['backpressure/logger/id-prefix', null, null],
                ],
                'expectedConfigData' => [
                    'backpressure' => [
                        'logger' => [
                            'type' => 'redis',
                            'options' => [
                                'server' => '<some-server>',
                                'port' => 3344,
                                'db' => 23,
                                'password' => '<some-password>',
                                'timeout' => 5,
                                'persistent' => '<persistent>',
                                'user' => '<some-user>',
                            ],
                            'id-prefix' => '<some-prefix>'
                        ]
                    ]
                ],
            ],
            'update options' => [
                'options' => [
                    'backpressure-logger' => 'redis',
                    'backpressure-logger-redis-server' => '<new-server>',
                    'backpressure-logger-redis-port' => 4433,
                    'backpressure-logger-redis-timeout' => 2,
                    'backpressure-logger-redis-persistent' => '<tnetsisrep>',
                    'backpressure-logger-redis-db' => 23,
                    'backpressure-logger-redis-password' => '<new-password>',
                    'backpressure-logger-redis-user' => '<new-user>',
                    'backpressure-logger-id-prefix' => '<new-prefix>',
                ],
                'deploymentConfigReturnMap' => [
                    ['backpressure/logger/type', null, 'redis'],
                    ['backpressure/logger/options/server', null, '<current-server>'],
                    ['backpressure/logger/options/port', null, 3344],
                    ['backpressure/logger/options/timeout', null, 5],
                    ['backpressure/logger/options/persistent' => '<persistent>'],
                    ['backpressure/logger/options/db', null, 43],
                    ['backpressure/logger/options/password', null, '<current-password>'],
                    ['backpressure/logger/options/user', null, '<current-user>'],
                    ['backpressure/logger/id-prefix', null, '<current-prefix>'],
                ],
                'expectedConfigData' => [
                    'backpressure' => [
                        'logger' => [
                            'type' => 'redis',
                            'options' => [
                                'server' => '<new-server>',
                                'port' => 4433,
                                'db' => 23,
                                'password' => '<new-password>',
                                'timeout' => 2,
                                'persistent' => '<tnetsisrep>',
                                'user' => '<new-user>',
                            ],
                            'id-prefix' => '<new-prefix>'
                        ]
                    ]
                ],
            ],
            'update-part-of-configuration' => [
                'options' => [
                    'backpressure-logger-redis-port' => 4433,
                    'backpressure-logger-redis-timeout' => 2,
                    'backpressure-logger-redis-password' => '<new-password>',
                    'backpressure-logger-redis-user' => '<new-user>',
                    'backpressure-logger-id-prefix' => '<new-prefix>',
                ],
                'deploymentConfigReturnMap' => [
                    ['backpressure/logger/type', null, 'redis'],
                    ['backpressure/logger/options/server', null, '<current-server>'],
                    ['backpressure/logger/options/port', null, 3344],
                    ['backpressure/logger/options/timeout', null, 5],
                    ['backpressure/logger/options/persistent', null, '<persistent>'],
                    ['backpressure/logger/options/db', null, 43],
                    ['backpressure/logger/options/password', null, '<current-password>'],
                    ['backpressure/logger/options/user', null, '<current-user>'],
                    ['backpressure/logger/id-prefix', null, '<current-prefix>'],
                ],
                'expectedConfigData' => [
                    'backpressure' => [
                        'logger' => [
                            'type' => 'redis',
                            'options' => [
                                'server' => '<current-server>',
                                'port' => 4433,
                                'db' => 43,
                                'password' => '<new-password>',
                                'timeout' => 2,
                                'persistent' => '<persistent>',
                                'user' => '<new-user>',
                            ],
                            'id-prefix' => '<new-prefix>'
                        ]
                    ]
                ],
            ]
        ];
    }

    /**
     * The default ID prefix, based on installation directory
     *
     * @return string
     */
    private static function expectedIdPrefix(): string
    {
        return substr(\hash('sha256', dirname(__DIR__, 8)), 0, 3) . '_';
    }
}
