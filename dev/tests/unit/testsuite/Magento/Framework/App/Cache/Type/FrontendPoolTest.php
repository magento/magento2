<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache\Type;

class FrontendPoolTest extends \PHPUnit_Framework_TestCase
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
    protected $_deploymentConfig;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cachePool;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_deploymentConfig = $this->getMock(
            'Magento\Framework\App\DeploymentConfig',
            [],
            [],
            '',
            false
        );
        $this->_cachePool = $this->getMock('Magento\Framework\App\Cache\Frontend\Pool', [], [], '', false);
        $this->_model = new FrontendPool(
            $this->_objectManager,
            $this->_deploymentConfig,
            $this->_cachePool,
            ['fixture_cache_type' => 'fixture_frontend_id']
        );
    }

    /**
     * @param string|null $fixtureSegment
     * @param string $inputCacheType
     * @param string $expectedFrontendId
     *
     * @dataProvider getDataProvider
     */
    public function testGet($fixtureSegment, $inputCacheType, $expectedFrontendId)
    {
        $this->_deploymentConfig->expects(
            $this->once()
        )->method(
            'getSegment'
        )->with(
            \Magento\Framework\App\DeploymentConfig\CacheConfig::CONFIG_KEY
        )->will(
            $this->returnValue($fixtureSegment)
        );

        $cacheFrontend = $this->getMock('Magento\Framework\Cache\FrontendInterface');
        $this->_cachePool->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $expectedFrontendId
        )->will(
            $this->returnValue($cacheFrontend)
        );

        $accessProxy = $this->getMock('Magento\Framework\App\Cache\Type\AccessProxy', [], [], '', false);
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Cache\Type\AccessProxy',
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
        $segment1 = [
            'frontend' => [],
            'type' => ['fixture_cache_type' => ['frontend' => 'configured_frontend_id']],
        ];
        $segment2 = ['frontend' => [], 'type' => ['fixture_cache_type' => ['frontend' => null]]];
        $segment3 = ['frontend' => [], 'type' => ['unknown_cache_type' => ['frontend' => null]]];
        return [
            'retrieval from config' => [$segment1, 'fixture_cache_type', 'configured_frontend_id'],
            'retrieval from map' => [$segment2, 'fixture_cache_type', 'fixture_frontend_id'],
            'fallback to default id' => [
                $segment3,
                'unknown_cache_type',
                \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID,
            ]
        ];
    }
}
