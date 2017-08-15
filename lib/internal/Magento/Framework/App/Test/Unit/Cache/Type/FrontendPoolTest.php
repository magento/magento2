<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Cache\Type;

use \Magento\Framework\App\Cache\Type\FrontendPool;

class FrontendPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentConfig;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cachePool;

    protected function setUp()
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->deploymentConfig = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);
        $this->_cachePool = $this->createMock(\Magento\Framework\App\Cache\Frontend\Pool::class);
        $this->_model = new FrontendPool(
            $this->_objectManager,
            $this->deploymentConfig,
            $this->_cachePool,
            ['fixture_cache_type' => 'fixture_frontend_id']
        );
    }

    /**
     * @param string|null $fixtureConfigData
     * @param string $inputCacheType
     * @param string $expectedFrontendId
     *
     * @dataProvider getDataProvider
     */
    public function testGet($fixtureConfigData, $inputCacheType, $expectedFrontendId)
    {
        $this->deploymentConfig->expects(
            $this->once()
        )->method(
            'getConfigData'
        )->with(
            FrontendPool::KEY_CACHE
        )->will(
            $this->returnValue($fixtureConfigData)
        );

        $cacheFrontend = $this->createMock(\Magento\Framework\Cache\FrontendInterface::class);
        $this->_cachePool->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $expectedFrontendId
        )->will(
            $this->returnValue($cacheFrontend)
        );

        $accessProxy = $this->createMock(\Magento\Framework\App\Cache\Type\AccessProxy::class);
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Framework\App\Cache\Type\AccessProxy::class,
            $this->identicalTo(['frontend' => $cacheFrontend, 'identifier' => $inputCacheType])
        )->will(
            $this->returnValue($accessProxy)
        );

        $this->assertSame($accessProxy, $this->_model->get($inputCacheType));
        // Result has to be cached in memory
        $this->assertSame($accessProxy, $this->_model->get($inputCacheType));
    }

    public function getDataProvider()
    {
        $configData1 = [
            'frontend' => [],
            'type' => ['fixture_cache_type' => ['frontend' => 'configured_frontend_id']],
        ];
        $configData2 = ['frontend' => [], 'type' => ['fixture_cache_type' => ['frontend' => null]]];
        $configData3 = ['frontend' => [], 'type' => ['unknown_cache_type' => ['frontend' => null]]];
        return [
            'retrieval from config' => [$configData1, 'fixture_cache_type', 'configured_frontend_id'],
            'retrieval from map' => [$configData2, 'fixture_cache_type', 'fixture_frontend_id'],
            'fallback to default id' => [
                $configData3,
                'unknown_cache_type',
                \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID,
            ]
        ];
    }
}
