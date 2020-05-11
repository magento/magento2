<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Type;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\Type\AccessProxy;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendPoolTest extends TestCase
{
    /**
     * @var FrontendPool
     */
    protected $_model;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManager;

    /**
     * @var DeploymentConfig|MockObject
     */
    protected $deploymentConfig;

    /**
     * @var Pool|MockObject
     */
    protected $_cachePool;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->_cachePool = $this->createMock(Pool::class);
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
        )->willReturn(
            $fixtureConfigData
        );

        $cacheFrontend = $this->getMockForAbstractClass(FrontendInterface::class);
        $this->_cachePool->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $expectedFrontendId
        )->willReturn(
            $cacheFrontend
        );

        $accessProxy = $this->createMock(AccessProxy::class);
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            AccessProxy::class,
            $this->identicalTo(['frontend' => $cacheFrontend, 'identifier' => $inputCacheType])
        )->willReturn(
            $accessProxy
        );

        $this->assertSame($accessProxy, $this->_model->get($inputCacheType));
        // Result has to be cached in memory
        $this->assertSame($accessProxy, $this->_model->get($inputCacheType));
    }

    /**
     * @return array
     */
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
                Pool::DEFAULT_FRONTEND_ID,
            ]
        ];
    }
}
