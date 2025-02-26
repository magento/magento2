<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\ConfigOptionsList;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Setup\Option\FlagConfigOption;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Model\ConfigOptionsList\Cache as CacheConfigOptionsList;
use Magento\Setup\Validator\RedisConnectionValidator;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    /**
     * @var \Magento\Setup\Model\ConfigOptionsList\Cache
     */
    private $configOptionsList;

    /**
     * @var RedisConnectionValidator
     */
    private $validatorMock;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfigMock;

    /**
     * Tests setup
     */
    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(RedisConnectionValidator::class);
        $this->deploymentConfigMock = $this->createMock(DeploymentConfig::class);

        $this->configOptionsList = new CacheConfigOptionsList($this->validatorMock);
    }

    /**
     * testGetOptions
     */
    public function testGetOptions()
    {
        $options = $this->configOptionsList->getOptions();
        $this->assertCount(11, $options);

        $this->assertArrayHasKey(0, $options);
        $this->assertInstanceOf(SelectConfigOption::class, $options[0]);
        $this->assertEquals('cache-backend', $options[0]->getName());

        $this->assertArrayHasKey(1, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[1]);
        $this->assertEquals('cache-backend-redis-server', $options[1]->getName());

        $this->assertArrayHasKey(2, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[2]);
        $this->assertEquals('cache-backend-redis-db', $options[2]->getName());

        $this->assertArrayHasKey(3, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[3]);
        $this->assertEquals('cache-backend-redis-port', $options[3]->getName());

        $this->assertArrayHasKey(4, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[4]);
        $this->assertEquals('cache-backend-redis-password', $options[4]->getName());

        $this->assertArrayHasKey(5, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[5]);
        $this->assertEquals('cache-backend-redis-compress-data', $options[5]->getName());

        $this->assertArrayHasKey(6, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[6]);
        $this->assertEquals('cache-backend-redis-compression-lib', $options[6]->getName());

        $this->assertArrayHasKey(7, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[7]);
        $this->assertEquals('cache-backend-redis-use-lua', $options[7]->getName());

        $this->assertArrayHasKey(8, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[8]);
        $this->assertEquals('cache-backend-redis-use-lua-on-gc', $options[8]->getName());

        $this->assertArrayHasKey(9, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[9]);
        $this->assertEquals('cache-id-prefix', $options[9]->getName());

        $this->assertArrayHasKey(10, $options);
        $this->assertInstanceOf(FlagConfigOption::class, $options[10]);
        $this->assertEquals('allow-parallel-generation', $options[10]->getName());
    }

    /**
     * testCreateConfigCacheRedis
     */
    public function testCreateConfigCacheRedis()
    {
        // Return default value for all config options
        $this->deploymentConfigMock->method('get')->willReturnArgument(1);

        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'default' => [
                        'backend' => \Magento\Framework\Cache\Backend\Redis::class,
                        'backend_options' => [
                            'server' => '127.0.0.1',
                            'port' => '6379',
                            'database' => '0',
                            'password' => '',
                            'compress_data' => '1',
                            'compression_lib' => '',
                            'use_lua' => '0',
                            'use_lua_on_gc' => '1'
                        ],
                        'id_prefix' => $this->expectedIdPrefix(),
                    ]
                ],
                'allow_parallel_generation' => 'false',
            ]
        ];

        $configData = $this->configOptionsList
            ->createConfig(['cache-backend' => 'redis'], $this->deploymentConfigMock);

        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testCreateConfigWithRedisConfig
     */
    public function testCreateConfigWithRedisConfig()
    {
        $this->deploymentConfigMock->method('get')
            ->willReturnCallback(
                function ($arg1, $arg2 = null) {
                    if ($arg1 === CacheConfigOptionsList::CONFIG_PATH_CACHE_ID_PREFIX) {
                        return 'XXX_';
                    } else {
                        return $arg2;
                    }
                }
            );

        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'default' => [
                        'backend' => \Magento\Framework\Cache\Backend\Redis::class,
                        'backend_options' => [
                            'server' => 'localhost',
                            'port' => '1234',
                            'database' => '5',
                            'password' => '',
                            'compress_data' => '1',
                            'compression_lib' => 'gzip',
                            'use_lua' => '0',
                            'use_lua_on_gc' => '1'
                        ],
                    ]
                ],
                'allow_parallel_generation' => 'false',
            ]
        ];

        $options = [
            'cache-backend' => 'redis',
            'cache-backend-redis-server' => 'localhost',
            'cache-backend-redis-port' => '1234',
            'cache-backend-redis-db' => '5',
            'cache-backend-redis-compress-data' => '1',
            'cache-backend-redis-compression-lib' => 'gzip'
        ];

        $configData = $this->configOptionsList->createConfig($options, $this->deploymentConfigMock);

        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testCreateConfigCacheRedis
     */
    public function testCreateConfigWithFileCache()
    {
        $this->deploymentConfigMock->method('get')->willReturn('');

        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'default' => [
                        'id_prefix' => $this->expectedIdPrefix(),
                    ]
                ]
            ]
        ];

        $configData = $this->configOptionsList->createConfig([], $this->deploymentConfigMock);

        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testCreateConfigCacheRedis
     */
    public function testCreateConfigWithIdPrefix()
    {
        $this->deploymentConfigMock->method('get')->willReturn('');

        $explicitPrefix = 'XXX_';
        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'default' => [
                        'id_prefix' => $explicitPrefix,
                    ]
                ]
            ]
        ];

        $configData = $this->configOptionsList->createConfig(
            ['cache-id-prefix' => $explicitPrefix],
            $this->deploymentConfigMock
        );

        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testValidateWithValidInput
     */
    public function testValidateWithValidInput()
    {
        $options = [
            'cache-backend' => 'redis',
            'cache-backend-redis-server' => 'localhost',
        ];
        $this->validatorMock->expects($this->once())
            ->method('isValidConnection')
            ->with([
                'host' => 'localhost',
                'db' => '',
                'port' => '',
                'password' => '',
            ])
            ->willReturn(true);

        $errors = $this->configOptionsList->validate($options, $this->deploymentConfigMock);

        $this->assertEmpty($errors);
    }

    /**
     * testValidateWithInvalidInput
     */
    public function testValidateWithInvalidInput()
    {
        $invalidCacheOption = 'clay-tablet';
        $options = ['cache-backend' => $invalidCacheOption];

        $errors = $this->configOptionsList->validate($options, $this->deploymentConfigMock);

        $this->assertCount(1, $errors);
        $this->assertEquals("Invalid cache handler 'clay-tablet'", $errors[0]);
    }

    /**
     * The default ID prefix, based on installation directory
     *
     * @return string
     */
    private function expectedIdPrefix(): string
    {
        return substr(\hash('sha256', dirname(__DIR__, 8)), 0, 3) . '_';
    }
}
