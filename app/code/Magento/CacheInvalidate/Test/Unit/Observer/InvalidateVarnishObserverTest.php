<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class InvalidateVarnishObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver */
    protected $model;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\Event\Observer */
    protected $observerMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\PageCache\Model\Config */
    protected $configMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\CacheInvalidate\Model\PurgeCache */
    protected $purgeCache;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\DataObject\ */
    protected $observerObject;

    /** @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Framework\App\Cache\Tag\Resolver */
    private $tagResolver;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configMock = $this->createPartialMock(\Magento\PageCache\Model\Config::class, ['getType', 'isEnabled']);
        $this->purgeCache = $this->createMock(\Magento\CacheInvalidate\Model\PurgeCache::class);
        $this->model = new \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver(
            $this->configMock,
            $this->purgeCache
        );

        $this->tagResolver = $this->createMock(\Magento\Framework\App\Cache\Tag\Resolver::class);
        $helper->setBackwardCompatibleProperty($this->model, 'tagResolver', $this->tagResolver);

        $this->observerMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $this->observerObject = $this->createMock(\Magento\Store\Model\Store::class);
    }

    /**
     * Test case for cache invalidation
     */
    public function testInvalidateVarnish()
    {
        $tags = ['cache_1', 'cache_group'];
        $pattern = '((^|,)cache_1(,|$))|((^|,)cache_group(,|$))';

        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->willReturn(
            \Magento\PageCache\Model\Config::VARNISH
        );

        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getObject']);
        $eventMock->expects($this->once())->method('getObject')->willReturn($this->observerObject);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $this->tagResolver->expects($this->once())->method('getTags')->with($this->observerObject)
            ->willReturn($tags);
        $this->purgeCache->expects($this->once())->method('sendPurgeRequest')->with($pattern);

        $this->model->execute($this->observerMock);
    }
}
