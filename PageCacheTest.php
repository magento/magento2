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
<<<<<<< HEAD
        $this->assertCount(8, $options);
        
=======
        $this->assertCount(7, $options);

>>>>>>> 12b7e08c2f2... Set cache id prefix on installation
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
        $this->assertEquals('page-cache-redis-password', $options[4]->getName());
        
        $this->assertArrayHasKey(5, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[5]);
<<<<<<< HEAD
        $this->assertEquals('page-cache-redis-compress-data', $options[5]->getName());
        
        $this->assertArrayHasKey(6, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[6]);
        $this->assertEquals('page-cache-redis-compression-lib', $options[6]->getName());
        
        $this->assertArrayHasKey(7, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[7]);
        $this->assertEquals('page-cache-id-prefix', $options[7]->getName());
=======
        $this->assertEquals('page-cache-redis-password', $options[5]->getName());

        $this->assertArrayHasKey(6, $options);
        $this->assertInstanceOf(TextConfigOption::class, $options[6]);
        $this->assertEquals('page-cache-id-prefix', $options[6]->getName());
>>>>>>> 12b7e08c2f2... Set cache id prefix on installation
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
<<<<<<< HEAD
                            'password' => '',
                            'compression_lib' => '',
=======
                            'password' => ''
>>>>>>> 12b7e08c2f2... Set cache id prefix on installation
                        ],
                        'id_prefix' => $this->expectedIdPrefix(),
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
                            'password' => '',
                            'compress_data' => '1',
<<<<<<< HEAD
                            'compression_lib' => 'gzip',
=======
                            'password' => ''
>>>>>>> 12b7e08c2f2... Set cache id prefix on installation
                        ],
                        'id_prefix' => $this->expectedIdPrefix(),
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
     * testCreateConfigWithRedis
     */
    public function testCreateConfigWithFileCache()
    {
        $this->deploymentConfigMock->method('get')->willReturn('');
        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'page_cache' => [
                        'id_prefix' => $this->expectedIdPrefix(),
                    ]
                ]
            ]
        ];
        $configData = $this->configList->createConfig([], $this->deploymentConfigMock);
        $this->assertEquals($expectedConfigData, $configData->getData());
    }
    
    /**
<<<<<<< HEAD
     * testCreateConfigCacheRedis
=======
     * testCreateConfigWithRedis
     */
    public function testCreateConfigWithFileCache()
    {
        $this->deploymentConfigMock->method('get')->willReturn('');

        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'page_cache' => [
                        'id_prefix' => $this->expectedIdPrefix(),
                    ]
                ]
            ]
        ];

        $configData = $this->configList->createConfig([], $this->deploymentConfigMock);

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
                    'page_cache' => [
                        'id_prefix' => $explicitPrefix,
                    ]
                ]
            ]
        ];

        $configData = $this->configList->createConfig(
            ['page-cache-id-prefix' => $explicitPrefix],
            $this->deploymentConfigMock
        );

        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testValidationWithValidData
>>>>>>> 12b7e08c2f2... Set cache id prefix on installation
     */
    public function testCreateConfigWithIdPrefix()
    {
        $this->deploymentConfigMock->method('get')->willReturn('');
        
        $explicitPrefix = 'XXX_';
        $expectedConfigData = [
            'cache' => [
                'frontend' => [
                    'page_cache' => [
                        'id_prefix' => $explicitPrefix,
                    ]
                ]
            ]
        ];
        
        $configData = $this->configList->createConfig(
            ['page-cache-id-prefix' => $explicitPrefix],
            $this->deploymentConfigMock
        );
        
        $this->assertEquals($expectedConfigData, $configData->getData());
    }

    /**
     * testValidationWithValidData
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
<<<<<<< HEAD
    
=======

>>>>>>> 12b7e08c2f2... Set cache id prefix on installation
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
