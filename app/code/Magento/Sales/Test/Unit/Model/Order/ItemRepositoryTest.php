<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Api\Data\OrderItemSearchResultInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\ItemRepository;
use Magento\Sales\Model\Order\ProductOption;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Model\ResourceModel\Order\Item as ItemResource;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for order item repository registry cache.
 */
class ItemRepositoryTest extends TestCase
{
    /**
     * @var ItemRepository
     */
    private $subject;

    /**
     * @var Metadata|MockObject
     */
    private $metadata;

    /**
     * @var ItemResource|MockObject
     */
    private $mapper;

    protected function setUp(): void
    {
        $this->metadata = $this->createPartialMock(Metadata::class, ['getNewInstance', 'getMapper']);
        $this->mapper = $this->createMock(ItemResource::class);
        $this->metadata->method('getMapper')->willReturn($this->mapper);

        $this->subject = new ItemRepository(
            $this->createStub(DataObjectFactory::class),
            $this->metadata,
            $this->createStub(OrderItemSearchResultInterfaceFactory::class),
            $this->createStub(CollectionProcessorInterface::class),
            $this->createStub(ProductOption::class)
        );
    }

    /**
     * Saving a distinct object with an already cached item ID must replace the cached object.
     */
    public function testSaveDistinctObjectReplacesCachedItem(): void
    {
        $cachedItem = $this->createOrderItem(1);
        $this->metadata->expects($this->once())->method('getNewInstance')->willReturn($cachedItem);
        $this->assertSame($cachedItem, $this->subject->get(1));

        $savedItem = $this->createOrderItem(1);
        $this->mapper->expects($this->once())->method('save')->with($savedItem);

        $this->assertSame($savedItem, $this->subject->save($savedItem));
        $this->assertSame($savedItem, $this->subject->get(1));
    }

    /**
     * Saving several items must cache each one under its own item ID.
     */
    public function testSaveMultipleItemsCachesEachByItemId(): void
    {
        $firstItem = $this->createOrderItem(1);
        $secondItem = $this->createOrderItem(2);
        $this->metadata->expects($this->never())->method('getNewInstance');
        $this->mapper->expects($this->exactly(2))->method('save');

        $this->assertSame($firstItem, $this->subject->save($firstItem));
        $this->assertSame($secondItem, $this->subject->save($secondItem));
        $this->assertSame($firstItem, $this->subject->get(1));
        $this->assertSame($secondItem, $this->subject->get(2));
    }

    /**
     * Reading an item after deleting it must not return the deleted object from the cache.
     */
    public function testGetAfterDeleteDoesNotReturnDeletedItem(): void
    {
        $deletedItem = $this->createOrderItem(1);
        $reloadedItem = $this->createOrderItem(1);
        $this->metadata->expects($this->exactly(2))
            ->method('getNewInstance')
            ->willReturnOnConsecutiveCalls($deletedItem, $reloadedItem);
        $this->mapper->expects($this->once())->method('delete')->with($deletedItem);

        $this->assertSame($deletedItem, $this->subject->get(1));
        $this->assertTrue($this->subject->delete($deletedItem));
        $this->assertSame($reloadedItem, $this->subject->get(1));
    }

    /**
     * Create order item which, like a real one, has an item ID but no entity ID.
     *
     * @param int $itemId
     * @return Item
     */
    private function createOrderItem(int $itemId): Item
    {
        $itemCollection = $this->createStub(ItemCollection::class);
        $itemCollection->method('filterByParent')->willReturnSelf();
        $itemCollection->method('getItems')->willReturn([]);
        $order = $this->createStub(Order::class);
        $order->method('getItemsCollection')->willReturn($itemCollection);

        $item = $this->createStub(Item::class);
        $item->method('load')->willReturnSelf();
        $item->method('getOrder')->willReturn($order);
        $item->method('getItemId')->willReturn($itemId);
        $item->method('getEntityId')->willReturn(null);

        return $item;
    }
}
