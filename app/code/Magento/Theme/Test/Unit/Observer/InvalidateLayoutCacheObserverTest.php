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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class InvalidateLayoutCacheObserverTest extends TestCase
{
    use MockCreationTrait;

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
        $this->cacheStateMock = $this->createMock(CacheState::class);
        
        $this->layoutCacheMock = $this->createPartialMock(LayoutCache::class, ['clean']);
        
        $this->tagResolverMock = $this->createPartialMockWithReflection(
            LayoutCacheTagResolverFactory::class,
            ['getTags', 'getStrategy']
        );
        
        $this->observerMock = $this->createMock(Observer::class);
        
        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getObject']
        );
        
        $this->objectMock = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getIdentifier', 'dataHasChangedFor', 'isObjectNew']
        );
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
     */
    #[DataProvider('invalidateLayoutCacheDataProvider')]
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
