<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CacheInvalidate\Test\Unit\Observer;

use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\CacheInvalidate\Observer\FlushAllCacheObserver;
use Magento\Framework\Event\Observer;
use Magento\PageCache\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FlushAllCacheObserverTest extends TestCase
{
    /** @var MockObject|FlushAllCacheObserver */
    protected $model;

    /** @var MockObject|Observer */
    protected $observerMock;

    /** @var MockObject|Config */
    protected $configMock;

    /** @var MockObject|PurgeCache */
    protected $purgeCache;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createPartialMock(Config::class, ['getType', 'isEnabled']);
        $this->purgeCache = $this->createMock(PurgeCache::class);
        $this->model = new FlushAllCacheObserver(
            $this->configMock,
            $this->purgeCache
        );
        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
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
            Config::VARNISH
        );

        $this->purgeCache->expects($this->once())->method('sendPurgeRequest')->with('.*');
        $this->model->execute($this->observerMock);
    }
}
