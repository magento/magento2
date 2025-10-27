<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Helper\Action;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Review\Helper\Action\Pager;
use Magento\Review\Model\ResourceModel\Review\Collection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Review\Helper\Action\Pager
 */
class PagerTest extends TestCase
{
    /** @var Pager */
    private $pager;

    /** @var CollectionFactory|MockObject */
    private $collectionFactory;

    /** @var Collection|MockObject */
    private $collection;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->collection = $this->createMock(Collection::class);

        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->collectionFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->collection);

        $context = $this->createMock(Context::class);

        $this->pager = new Pager($context, $this->collectionFactory);
    }

    /**
     * Test getting next review ID
     *
     * @return void
     */
    public function testGetNextItemId()
    {
        $item = new DataObject(['id' => 10]);

        $this->collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.review_id', ['gt' => 5])
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setOrder')
            ->with('main_table.review_id', 'ASC')
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setPageSize')
            ->with(1)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setCurPage')
            ->with(1)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($item);

        $this->assertEquals(10, $this->pager->getNextItemId(5));
    }

    /**
     * Test that getNextItemId returns false when no item exists
     *
     * @return void
     */
    public function testGetNextItemIdReturnsFalse()
    {
        $item = new DataObject([]);

        $this->collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.review_id', ['gt' => 99])
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setOrder')
            ->with('main_table.review_id', 'ASC')
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setPageSize')
            ->with(1)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setCurPage')
            ->with(1)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($item);

        $this->assertFalse($this->pager->getNextItemId(99));
    }

    /**
     * Test getting previous review ID
     *
     * @return void
     */
    public function testGetPreviousItemId()
    {
        $item = new DataObject(['id' => 4]);

        $this->collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.review_id', ['lt' => 5])
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setOrder')
            ->with('main_table.review_id', 'DESC')
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setPageSize')
            ->with(1)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setCurPage')
            ->with(1)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($item);

        $this->assertEquals(4, $this->pager->getPreviousItemId(5));
    }

    /**
     * Test that getPreviousItemId returns false when no item exists
     *
     * @return void
     */
    public function testGetPreviousItemIdReturnsFalse()
    {
        $item = new DataObject([]);

        $this->collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('main_table.review_id', ['lt' => 1])
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setOrder')
            ->with('main_table.review_id', 'DESC')
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setPageSize')
            ->with(1)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('setCurPage')
            ->with(1)
            ->willReturnSelf();
        $this->collection->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($item);

        $this->assertFalse($this->pager->getPreviousItemId(1));
    }
}
