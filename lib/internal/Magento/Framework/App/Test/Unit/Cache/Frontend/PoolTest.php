<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Frontend;

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * And another docblock to make the sniff shut up.
 */
class PoolTest extends TestCase
{
    /**
     * @var Pool
     */
    protected $_model;

    /**
     * Array of frontend cache instances stubs, used to verify, what is stored inside the pool
     *
     * @var MockObject[]
     */
    protected $_frontendInstances = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_frontendInstances = [
            Pool::DEFAULT_FRONTEND_ID => $this->getMockForAbstractClass(FrontendInterface::class),
            'resource1' => $this->getMockForAbstractClass(FrontendInterface::class),
            'resource2' => $this->getMockForAbstractClass(FrontendInterface::class)
        ];

        $frontendFactoryMap = [
            [
                ['data1' => 'value1', 'data2' => 'value2'],
                $this->_frontendInstances[Pool::DEFAULT_FRONTEND_ID],
            ],
            [['r1d1' => 'value1', 'r1d2' => 'value2'], $this->_frontendInstances['resource1']],
            [['r2d1' => 'value1', 'r2d2' => 'value2'], $this->_frontendInstances['resource2']]
        ];
        $frontendFactory = $this->createMock(Factory::class);
        $frontendFactory->expects($this->any())->method('create')->willReturnMap($frontendFactoryMap);

        $deploymentConfig = $this->createMock(DeploymentConfig::class);
        $deploymentConfig->expects($this->any())
            ->method('getConfigData')
            ->with(FrontendPool::KEY_CACHE)
            ->willReturn(['frontend' => ['resource2' => ['r2d1' => 'value1', 'r2d2' => 'value2']]]);

        $frontendSettings = [
            Pool::DEFAULT_FRONTEND_ID => ['data1' => 'value1', 'data2' => 'value2'],
            'resource1' => ['r1d1' => 'value1', 'r1d2' => 'value2']
        ];

        $this->_model = new Pool(
            $deploymentConfig,
            $frontendFactory,
            $frontendSettings
        );
    }

    /**
     * Test that constructor delays object initialization (does not perform any initialization of its own).
     *
     * @return void
     */
    public function testConstructorNoInitialization(): void
    {
        $deploymentConfig = $this->createMock(DeploymentConfig::class);
        $frontendFactory = $this->createMock(Factory::class);
        $frontendFactory->expects($this->never())->method('create');
        new Pool($deploymentConfig, $frontendFactory);
    }

    /**
     * @param array $fixtureCacheConfig
     * @param array $frontendSettings
     * @param array $expectedFactoryArg
     *
     * @dataProvider initializationParamsDataProvider
     */
    public function testInitializationParams(
        array $fixtureCacheConfig,
        array $frontendSettings,
        array $expectedFactoryArg
    ): void {
        $deploymentConfig = $this->createMock(DeploymentConfig::class);
        $deploymentConfig->expects($this->once())
            ->method('getConfigData')
            ->with(FrontendPool::KEY_CACHE)
            ->willReturn($fixtureCacheConfig);

        $frontendFactory = $this->createMock(Factory::class);
        $frontendFactory
            ->method('create')
            ->withConsecutive([$expectedFactoryArg]);

        $model = new Pool($deploymentConfig, $frontendFactory, $frontendSettings);
        $model->current();
    }

    /**
     * @return array
     */
    public function initializationParamsDataProvider(): array
    {
        return [
            'no deployment config, default settings' => [
                ['frontend' => []],
                [Pool::DEFAULT_FRONTEND_ID => ['default_option' => 'default_value']],
                ['default_option' => 'default_value']
            ],
            'deployment config, default settings' => [
                ['frontend' => [Pool::DEFAULT_FRONTEND_ID => ['configured_option' => 'configured_value']]],
                [Pool::DEFAULT_FRONTEND_ID => ['default_option' => 'default_value']],
                ['configured_option' => 'configured_value', 'default_option' => 'default_value']
            ],
            'deployment config, overridden settings' => [
                ['frontend' => [Pool::DEFAULT_FRONTEND_ID => ['configured_option' => 'configured_value']]],
                [Pool::DEFAULT_FRONTEND_ID => ['configured_option' => 'default_value']],
                ['configured_option' => 'configured_value']
            ],
            'deployment config, default settings, overridden settings' => [
                ['frontend' => [Pool::DEFAULT_FRONTEND_ID => ['configured_option' => 'configured_value']]],
                [Pool::DEFAULT_FRONTEND_ID => [
                    'configured_option' => 'default_value',
                    'default_setting' => 'default_value'
                ]],
                ['configured_option' => 'configured_value', 'default_setting' => 'default_value'],
            ],
            'custom deployent config, default settings' => [
                ['frontend' => ['custom' => ['configured_option' => 'configured_value']]],
                ['custom' => ['default_option' => 'default_value']],
                ['configured_option' => 'configured_value', 'default_option' => 'default_value']
            ],
            'custom deployent config, default settings, overridden settings' => [
                ['frontend' => ['custom' => ['configured_option' => 'configured_value']]],
                ['custom' => ['default_option' => 'default_value', 'configured_option' => 'default_value']],
                ['configured_option' => 'configured_value', 'default_option' => 'default_value']
            ]
        ];
    }

    /**
     * @return void
     */
    public function testCurrent(): void
    {
        $this->assertSame($this->_frontendInstances[Pool::DEFAULT_FRONTEND_ID], $this->_model->current());
    }

    /**
     * @return void
     */
    public function testKey(): void
    {
        $this->assertEquals(Pool::DEFAULT_FRONTEND_ID, $this->_model->key());
    }

    /**
     * @return void
     */
    public function testNext(): void
    {
        $this->assertEquals(Pool::DEFAULT_FRONTEND_ID, $this->_model->key());

        $this->_model->next();
        $this->assertEquals('resource1', $this->_model->key());
        $this->assertSame($this->_frontendInstances['resource1'], $this->_model->current());

        $this->_model->next();
        $this->assertEquals('resource2', $this->_model->key());
        $this->assertSame($this->_frontendInstances['resource2'], $this->_model->current());

        $this->_model->next();
        $this->assertNull($this->_model->key());
        $this->assertFalse($this->_model->current());
    }

    /**
     * @return void
     */
    public function testRewind(): void
    {
        $this->_model->next();
        $this->assertNotEquals(Pool::DEFAULT_FRONTEND_ID, $this->_model->key());

        $this->_model->rewind();
        $this->assertEquals(Pool::DEFAULT_FRONTEND_ID, $this->_model->key());
    }

    /**
     * @return void
     */
    public function testValid(): void
    {
        $this->assertTrue($this->_model->valid());

        $this->_model->next();
        $this->assertTrue($this->_model->valid());

        $this->_model->next();
        $this->_model->next();
        $this->assertFalse($this->_model->valid());

        $this->_model->rewind();
        $this->assertTrue($this->_model->valid());
    }

    /**
     * @return void
     */
    public function testGet(): void
    {
        foreach ($this->_frontendInstances as $frontendId => $frontendInstance) {
            $this->assertSame($frontendInstance, $this->_model->get($frontendId));
        }
    }

    /**
     * @return void
     */
    public function testFallbackOnDefault(): void
    {
        $this->assertSame($this->_frontendInstances[Pool::DEFAULT_FRONTEND_ID], $this->_model->get('unknown'));
    }
}
