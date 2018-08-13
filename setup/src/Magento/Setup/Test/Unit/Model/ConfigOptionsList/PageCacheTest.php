<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\ConfigOptionsList;

use Magento\Setup\Model\ConfigOptionsList\PageCache;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Setup\Validator\RedisConnectionValidator;

class PageCacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Model\ConfigOptionsList\PageCache
     */
    private $configList;

    /**
     * @var \Magento\Setup\Validator\RedisConnectionValidator
     */
    private $validatorMock;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfigMock;

    /**
     * Test setup
     */
    protected function setUp()
    {
        $this->validatorMock = $this->createMock(RedisConnectionValidator::class, [], [], '', false);
        $this->deploymentConfigMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

        $this->configList = new PageCache($this->validatorMock);
    }

    /**
     * testGetOptions
     */
    public function testGetOptions()
    {
        $options = $this->configList->getOptions();
        $this->assertCount(6, $options);

        $this->assertArrayHasKey(0, $options);
        $this->assertInstanceOf(SelectConfigOption::class, $options[0]);
        $this->assertEquals('page-cache', $options[0]->getName());

        $this->assertArrayHasKey(1, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[1]);
        $this->assertEquals('page-cache-redis-server', $options[1]->getName());

        $this->assertArrayHasKey(2, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[2]);
        $this->assertEquals('page-cache-redis-db', $options[2]->getName());

        $this->assertArrayHasKey(3, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[3]);
        $this->assertEquals('page-cache-redis-port', $options[3]->getName());

        $this->assertArrayHasKey(4, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[4]);
        $this->assertEquals('page-cache-redis-compress-data', $options[4]->getName());

        $this->assertArrayHasKey(5, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[5]);
        $this->assertEquals('page-cache-redis-password', $options[5]->getName());
    }

    /**
     * testCreateConfigWithRedis
     */
    public function testCreateConfigWithRedis()
    {
        $this->deploymentConfigMock->method('get')->willReturn('');

        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'page_cache' => [
                        'backend' => 'Cm_Cache_Backend_Redis',
                        'backend_options' => [
                            'server'=> '',
                            'port' => '',
                            'database' => '',
                            'compress_data' => '',
                            'password' => ''
                        ]
                    ]
                ]
            ]
        ];

        $configData = $this->configList->createConfig(['page-cache' => 'redis'], $this->deploymentConfigMock);

        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testCreateConfigWithRedisConfiguration
     */
    public function testCreateConfigWithRedisConfiguration()
    {
        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'page_cache' => [
                        'backend' => 'Cm_Cache_Backend_Redis',
                        'backend_options' => [
                            'server' => 'foo.bar',
                            'port' => '9000',
                            'database' => '6',
                            'compress_data' => '1',
                            'password' => ''
                        ]
                    ]
                ]
            ]
        ];

        $options = [
            'page-cache' => 'redis',
            'page-cache-redis-server' => 'foo.bar',
            'page-cache-redis-port' => '9000',
            'page-cache-redis-db' => '6',
            'page-cache-redis-compress-data' => '1'
        ];

        $configData = $this->configList->createConfig($options, $this->deploymentConfigMock);

        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testValidationWithValidData
     */
    public function testValidationWithValidData()
    {
        $this->validatorMock->expects($this->once())
            ->method('isValidConnection')
            ->willReturn(true);

        $options = [
            'page-cache' => 'redis',
            'page-cache-redis-db' => '2'
        ];

        $errors = $this->configList->validate($options, $this->deploymentConfigMock);

        $this->assertEmpty($errors);
    }

    /**
     * testValidationWithInvalidData
     */
    public function testValidationWithInvalidData()
    {
        $options = [
            'page-cache' => 'foobar'
        ];

        $errors = $this->configList->validate($options, $this->deploymentConfigMock);

        $this->assertCount(1, $errors);
        $this->assertEquals('Invalid cache handler \'foobar\'', $errors[0]);
    }
}
