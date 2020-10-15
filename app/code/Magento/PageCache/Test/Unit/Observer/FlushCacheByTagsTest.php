<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Observer;

use Magento\Framework\App\Cache\Tag\Resolver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Observer\FlushCacheByTags;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\PageCache\Observer\FlushCacheByTags
 */
class FlushCacheByTagsTest extends TestCase
{
    /**
     * @var FlushCacheByTags
     */
    private $model;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Type|MockObject
     */
    private $fullPageCacheMock;

    /**
     * @var Resolver|MockObject
     */
    private $tagResolverMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createPartialMock(Config::class, ['getType', 'isEnabled']);
        $this->fullPageCacheMock = $this->createPartialMock(Type::class, ['clean']);

        $this->tagResolverMock = $this->createMock(Resolver::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            FlushCacheByTags::class,
            [
                'config' => $this->configMock,
                'fullPageCache' => $this->fullPageCacheMock,
                'tagResolver' => $this->tagResolverMock
            ]
        );
    }

    /**
     * Test case for cache invalidation
     *
     * @dataProvider flushCacheByTagsDataProvider
     * @param $cacheState
     */
    public function testExecute($cacheState)
    {
        $this->configMock->method('isEnabled')->willReturn($cacheState);
        $observerObject = $this->createMock(Observer::class);
        $observedObject = $this->createMock(Store::class);

        if ($cacheState) {
            $tags = ['cache_1', 'cache_group'];
            $expectedTags = ['cache_1', 'cache_group'];

            $eventMock = $this->getMockBuilder(Event::class)
                ->addMethods(['getObject'])
                ->disableOriginalConstructor()
                ->getMock();
            $eventMock->expects($this->once())->method('getObject')->willReturn($observedObject);
            $observerObject->expects($this->once())->method('getEvent')->willReturn($eventMock);
            $this->configMock->expects($this->once())
                ->method('getType')
                ->willReturn(Config::BUILT_IN);
            $this->tagResolverMock->expects($this->once())->method('getTags')->willReturn($tags);

            $this->fullPageCacheMock->expects($this->once())
                ->method('clean')
                ->with(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $expectedTags);
        }

        $result = $this->model->execute($observerObject);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function flushCacheByTagsDataProvider()
    {
        return [
            'full_page cache type is enabled' => [true],
            'full_page cache type is disabled' => [false]
        ];
    }

    /**
     * Test case for cache invalidation with empty tags
     */
    public function testExecuteWithEmptyTags()
    {
        $this->configMock->method('isEnabled')->willReturn(true);
        $observerObject = $this->createMock(Observer::class);
        $observedObject = $this->createMock(Store::class);

        $tags = [];

        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->once())->method('getObject')->willReturn($observedObject);
        $observerObject->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $this->configMock->expects(
            $this->once()
        )->method(
            'getType'
        )->willReturn(
            Config::BUILT_IN
        );
        $this->tagResolverMock->expects($this->once())->method('getTags')->willReturn($tags);

        $this->fullPageCacheMock->expects($this->never())->method('clean');

        $this->model->execute($observerObject);
    }
}
