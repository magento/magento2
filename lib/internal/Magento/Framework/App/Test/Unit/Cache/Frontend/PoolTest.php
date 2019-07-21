<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Cache\Frontend;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\Type\FrontendPool;

/**
 * And another docblock to make the sniff shut up.
 */
class PoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_model;

    /**
     * Array of frontend cache instances stubs, used to verify, what is stored inside the pool
     *
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $_frontendInstances = [];

    protected function setUp()
    {
        $this->_frontendInstances = [
            Pool::DEFAULT_FRONTEND_ID => $this->createMock(\Magento\Framework\Cache\FrontendInterface::class),
            'resource1' => $this->createMock(\Magento\Framework\Cache\FrontendInterface::class),
            'resource2' => $this->createMock(\Magento\Framework\Cache\FrontendInterface::class),
        ];

        $frontendFactoryMap = [
            [
                ['data1' => 'value1', 'data2' => 'value2'],
                $this->_frontendInstances[Pool::DEFAULT_FRONTEND_ID],
            ],
            [['r1d1' => 'value1', 'r1d2' => 'value2'], $this->_frontendInstances['resource1']],
            [['r2d1' => 'value1', 'r2d2' => 'value2'], $this->_frontendInstances['resource2']],
        ];
        $frontendFactory = $this->createMock(\Magento\Framework\App\Cache\Frontend\Factory::class);
        $frontendFactory->expects($this->any())->method('create')->will($this->returnValueMap($frontendFactoryMap));

        $deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfig->expects(
            $this->any()
        )->method(
            'getConfigData'
        )->with(
            FrontendPool::KEY_CACHE
        )->will(
            $this->returnValue(['frontend' => ['resource2' => ['r2d1' => 'value1', 'r2d2' => 'value2']]])
        );

        $frontendSettings = [
            Pool::DEFAULT_FRONTEND_ID => ['data1' => 'value1', 'data2' => 'value2'],
            'resource1' => ['r1d1' => 'value1', 'r1d2' => 'value2'],
        ];

        $this->_model = new \Magento\Framework\App\Cache\Frontend\Pool(
            $deploymentConfig,
            $frontendFactory,
            $frontendSettings
        );
    }

    /**
     * Test that constructor delays object initialization (does not perform any initialization of its own)
     */
    public function testConstructorNoInitialization()
    {
        $deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $frontendFactory = $this->createMock(\Magento\Framework\App\Cache\Frontend\Factory::class);
        $frontendFactory->expects($this->never())->method('create');
        new \Magento\Framework\App\Cache\Frontend\Pool($deploymentConfig, $frontendFactory);
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
    ) {
        $deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfig->expects(
            $this->once()
        )->method(
            'getConfigData'
        )->with(
            FrontendPool::KEY_CACHE
        )->will(
            $this->returnValue($fixtureCacheConfig)
        );

        $frontendFactory = $this->createMock(\Magento\Framework\App\Cache\Frontend\Factory::class);
        $frontendFactory->expects($this->at(0))->method('create')->with($expectedFactoryArg);

        $model = new \Magento\Framework\App\Cache\Frontend\Pool($deploymentConfig, $frontendFactory, $frontendSettings);
        $model->current();
    }

    /**
     * @return array
     */
    public function initializationParamsDataProvider()
    {
        return [
            'no deployment config, default settings' => [
                ['frontend' => []],
                [Pool::DEFAULT_FRONTEND_ID => ['default_option' => 'default_value']],
                ['default_option' => 'default_value'],
            ],
            'deployment config, default settings' => [
                ['frontend' => [Pool::DEFAULT_FRONTEND_ID => ['configured_option' => 'configured_value']]],
                [Pool::DEFAULT_FRONTEND_ID => ['default_option' => 'default_value']],
                ['configured_option' => 'configured_value', 'default_option' => 'default_value'],
            ],
            'deployment config, overridden settings' => [
                ['frontend' => [Pool::DEFAULT_FRONTEND_ID => ['configured_option' => 'configured_value']]],
                [Pool::DEFAULT_FRONTEND_ID => ['configured_option' => 'default_value']],
                ['configured_option' => 'configured_value'],
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
                ['configured_option' => 'configured_value', 'default_option' => 'default_value'],
            ],
            'custom deployent config, default settings, overridden settings' => [
                ['frontend' => ['custom' => ['configured_option' => 'configured_value']]],
                ['custom' => ['default_option' => 'default_value', 'configured_option' => 'default_value']],
                ['configured_option' => 'configured_value', 'default_option' => 'default_value'],
            ]
        ];
    }

    public function testCurrent()
    {
        $this->assertSame($this->_frontendInstances[Pool::DEFAULT_FRONTEND_ID], $this->_model->current());
    }

    public function testKey()
    {
        $this->assertEquals(Pool::DEFAULT_FRONTEND_ID, $this->_model->key());
    }

    public function testNext()
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

    public function testRewind()
    {
        $this->_model->next();
        $this->assertNotEquals(Pool::DEFAULT_FRONTEND_ID, $this->_model->key());

        $this->_model->rewind();
        $this->assertEquals(Pool::DEFAULT_FRONTEND_ID, $this->_model->key());
    }

    public function testValid()
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

    public function testGet()
    {
        foreach ($this->_frontendInstances as $frontendId => $frontendInstance) {
            $this->assertSame($frontendInstance, $this->_model->get($frontendId));
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cache frontend 'unknown' is not recognized
     */
    public function testGetUnknownFrontendId()
    {
        $this->_model->get('unknown');
    }
}
