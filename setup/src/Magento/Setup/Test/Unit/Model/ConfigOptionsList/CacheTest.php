<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\ConfigOptionsList;

use Magento\Setup\Model\ConfigOptionsList\Cache as CacheConfigOptionsList;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Setup\Validator\RedisConnectionValidator;

class CacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Model\ConfigOptionsList\Cache
     */
    private $configOptionsList;

    /**
     * @var \Magento\Setup\Validator\RedisConnectionValidator
     */
    private $validatorMock;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfigMock;

    /**
     * Tests setup
     */
    protected function setUp()
    {
        $this->validatorMock = $this->createMock(RedisConnectionValidator::class);
        $this->deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

        $this->configOptionsList = new CacheConfigOptionsList($this->validatorMock);
    }

    /**
     * testGetOptions
     */
    public function testGetOptions()
    {
        $options = $this->configOptionsList->getOptions();
        $this->assertCount(6, $options);

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
        $this->assertEquals('cache-id-prefix', $options[5]->getName());
    }

    /**
     * testCreateConfigCacheRedis
     */
    public function testCreateConfigCacheRedis()
    {
        $this->deploymentConfigMock->method('get')->willReturn('');

        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'default' => [
                        'backend' => 'Cm_Cache_Backend_Redis',
                        'backend_options' => [
                            'server' => '',
                            'port' => '',
                            'database' => '',
                            'password' => ''
                        ],
                        'id_prefix' => $this->expectedIdPrefix(),
                    ]
                ]
            ]
        ];

        $configData = $this->configOptionsList->createConfig(['cache-backend'=>'redis'], $this->deploymentConfigMock);

        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testCreateConfigWithRedisConfig
     */
    public function testCreateConfigWithRedisConfig()
    {
        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'default' => [
                        'backend' => 'Cm_Cache_Backend_Redis',
                        'backend_options' => [
                            'server' => 'localhost',
                            'port' => '1234',
                            'database' => '5',
                            'password' => ''
                        ],
                        'id_prefix' => $this->expectedIdPrefix(),
                    ]
                ]
            ]
        ];
        $options = [
            'cache-backend' => 'redis',
            'cache-backend-redis-server' => 'localhost',
            'cache-backend-redis-port' => '1234',
            'cache-backend-redis-db' => '5'
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
            ->with(['host'=>'localhost', 'db'=>'', 'port'=>'', 'password'=>''])
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
        return substr(\md5(dirname(__DIR__, 8)), 0, 3) . '_';
    }
}
