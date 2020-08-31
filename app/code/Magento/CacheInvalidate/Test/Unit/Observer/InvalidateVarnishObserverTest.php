<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CacheInvalidate\Test\Unit\Observer;

use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\CacheInvalidate\Observer\InvalidateVarnishObserver;
use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver
 */
class InvalidateVarnishObserverTest extends TestCase
{
    /**
     * @var InvalidateVarnishObserver
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var PurgeCache|MockObject
     */
    private $purgeCacheMock;

    /**
     * @var Resolver|MockObject
     */
    private $tagResolverMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Store|MockObject
     */
    private $observerObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createPartialMock(Config::class, ['getType', 'isEnabled']);
        $this->purgeCacheMock = $this->createMock(PurgeCache::class);
        $this->tagResolverMock = $this->createMock(Resolver::class);

        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observerObject = $this->createMock(Store::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            InvalidateVarnishObserver::class,
            [
                'config' => $this->configMock,
                'purgeCache' => $this->purgeCacheMock,
                'tagResolver' => $this->tagResolverMock
            ]
        );
    }

    /**
     * Test case for cache invalidation
     */
    public function testInvalidateVarnish()
    {
        $tags = ['cache_1', 'cache_group'];
        $pattern = ['((^|,)cache_1(,|$))', '((^|,)cache_group(,|$))'];

        $this->configMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->willReturn(
            Config::VARNISH
        );

        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->once())->method('getObject')->willReturn($this->observerObject);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $this->tagResolverMock->expects($this->once())->method('getTags')->with($this->observerObject)
            ->willReturn($tags);
        $this->purgeCacheMock->expects($this->once())->method('sendPurgeRequest')->with($pattern);

        $this->model->execute($this->observerMock);
    }
}
