<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Cache\Type;

use \Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Config\ConfigOptionsList;

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
     * @var \Magento\Framework\App\DeploymentConfig\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reader;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cachePool;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->reader = $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false);
        $this->_cachePool = $this->getMock('Magento\Framework\App\Cache\Frontend\Pool', [], [], '', false);
        $this->_model = new FrontendPool(
            $this->_objectManager,
            $this->reader,
            $this->_cachePool,
            ['fixture_cache_type' => 'fixture_frontend_id']
        );
    }

    /**
     * @param string|null $fixtureconfigData
     * @param string $inputCacheType
     * @param string $expectedFrontendId
     *
     * @dataProvider getDataProvider
     */
    public function testGet($fixtureconfigData, $inputCacheType, $expectedFrontendId)
    {
        $this->reader->expects(
            $this->once()
        )->method(
            'getConfigData'
        )->with(
                ConfigOptionsList::KEY_CACHE
        )->will(
            $this->returnValue($fixtureconfigData)
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
