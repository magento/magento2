<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Observer;

use Magento\Theme\Model\LayoutCacheTagResolverFactory;
use Magento\Theme\Observer\InvalidateLayoutCacheObserver;
use Magento\Framework\App\Cache\StateInterface as CacheState;
use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InvalidateLayoutCacheObserverTest extends TestCase
{
    /**
     * @var InvalidateLayoutCacheObserver
     */
    private $invalidateLayoutCacheObserver;

    /**
     * @var LayoutCache|MockObject
     */
    private $layoutCacheMock;

    /**
     * @var CacheState|MockObject
     */
    private $cacheStateMock;

    /**
     * @var LayoutCacheTagResolverFactory|MockObject
     */
    private $tagResolverMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var DataObject|MockObject
     */
    private $objectMock;

    protected function setUp(): void
    {
        $this->cacheStateMock = $this
            ->getMockBuilder(CacheState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutCacheMock = $this
            ->getMockBuilder(LayoutCache::class)
            ->onlyMethods(['clean'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->tagResolverMock = $this
            ->getMockBuilder(LayoutCacheTagResolverFactory::class)
            ->addMethods(['getTags'])
            ->onlyMethods(['getStrategy'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->observerMock = $this
            ->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this
            ->getMockBuilder(Event::class)
            ->addMethods(['getObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMock = $this
            ->getMockBuilder(DataObject::class)
            ->addMethods(
                [
                    'getIdentifier',
                    'dataHasChangedFor',
                    'isObjectNew'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->invalidateLayoutCacheObserver = new InvalidateLayoutCacheObserver(
            $this->layoutCacheMock,
            $this->cacheStateMock,
            $this->tagResolverMock
        );
    }

    /**
     * Test case for InvalidateLayoutCacheObserver test
     *
     * @param bool $cacheIsEnabled
     * @param bool $isDataChangedFor
     * @param bool $isObjectNew
     * @param object|null $cacheStrategy
     * @param array $tags
     * @dataProvider invalidateLayoutCacheDataProvider
     */
    public function testExecute(
        bool    $cacheIsEnabled,
        bool    $isDataChangedFor,
        bool    $isObjectNew,
        ?object $cacheStrategy,
        array   $tags
    ): void {
        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getObject')
            ->willReturn($this->objectMock);
        $this->cacheStateMock
            ->expects($this->any())
            ->method('isEnabled')
            ->willReturn($cacheIsEnabled);
        $this->objectMock
            ->expects($this->any())
            ->method('dataHasChangedFor')
            ->willReturn($isDataChangedFor);
        $this->objectMock
            ->expects($this->any())
            ->method('isObjectNew')
            ->willReturn($isObjectNew);
        $this->objectMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->willReturn(10);
        $this->tagResolverMock
            ->expects($this->any())
            ->method('getStrategy')
            ->willReturn($cacheStrategy);
        $this->tagResolverMock
            ->expects($this->any())
            ->method('getTags')
            ->with($this->objectMock)
            ->willReturn($tags);
        $this->layoutCacheMock
            ->expects($this->any())
            ->method('clean')
            ->willReturnSelf();
        $this->invalidateLayoutCacheObserver->execute($this->observerMock);
    }

    /**
     * Data provider for testcase
     *
     * @return array
     */
    public static function invalidateLayoutCacheDataProvider(): array
    {
        return [
            'when layout cache is not enabled' => [false, true, false, null, []],
            'when cache is not changed' => [true, false, false, null, []],
            'when object is new' => [true, true, false, null, []],
            'when tag is empty' => [true, true, false, null, []],
            'when tag is not empty' => [true, true, false, null, ['cms_p']],
        ];
    }
}
