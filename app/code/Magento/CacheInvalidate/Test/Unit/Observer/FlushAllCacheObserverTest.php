<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Observer;

class FlushAllCacheObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\CacheInvalidate\Observer\FlushAllCacheObserver */
    protected $model;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Event\Observer */
    protected $observerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\PageCache\Model\Config */
    protected $configMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\CacheInvalidate\Model\PurgeCache */
    protected $purgeCache;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createPartialMock(\Magento\PageCache\Model\Config::class, ['getType', 'isEnabled']);
        $this->purgeCache = $this->createMock(\Magento\CacheInvalidate\Model\PurgeCache::class);
        $this->model = new \Magento\CacheInvalidate\Observer\FlushAllCacheObserver(
            $this->configMock,
            $this->purgeCache
        );
        $this->observerMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
    }

    /**
     * Test case for flushing all the cache
     */
    public function testFlushAllCache()
    {
        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->willReturn(
            \Magento\PageCache\Model\Config::VARNISH
        );

        $this->purgeCache->expects($this->once())->method('sendPurgeRequest')->with('.*');
        $this->model->execute($this->observerMock);
    }
}
