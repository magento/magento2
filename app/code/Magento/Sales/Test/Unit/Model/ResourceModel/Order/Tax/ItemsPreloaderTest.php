<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Tax;

use Magento\Sales\Model\ResourceModel\Order\Tax\Item;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemsPreloader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemsPreloaderTest extends TestCase
{
    /**
     * @var Item|MockObject
     */
    private $itemResource;

    /**
     * @var ItemsPreloader
     */
    private $preloader;

    protected function setUp(): void
    {
        $this->itemResource = $this->createMock(Item::class);
        $itemFactory = $this->createStub(ItemFactory::class);
        $itemFactory->method('create')->willReturn($this->itemResource);
        $this->preloader = new ItemsPreloader($itemFactory);
    }

    public function testPreloadGroupsRowsByOrderInOneQuery(): void
    {
        $rows = [
            ['tax_id' => 1, 'item_id' => 11, 'code' => 'US', 'order_id' => '1'],
            ['tax_id' => 2, 'item_id' => 21, 'code' => 'US', 'order_id' => '2'],
            ['tax_id' => 1, 'item_id' => null, 'code' => 'US', 'order_id' => '1'],
        ];
        $this->itemResource->expects($this->once())
            ->method('getTaxItemsByOrderIds')
            ->with([1, 2, 3])
            ->willReturn($rows);

        $this->preloader->preload(['1', '2', '3']);

        $this->assertSame([$rows[0], $rows[2]], $this->preloader->take(1));
        $this->assertSame([$rows[1]], $this->preloader->take(2));
        $this->assertSame([], $this->preloader->take(3));
    }

    public function testTakeHandsRowsOutOnce(): void
    {
        $row = ['tax_id' => 1, 'item_id' => 11, 'code' => 'US', 'order_id' => '1'];
        $this->itemResource->expects($this->once())->method('getTaxItemsByOrderIds')->willReturn([$row]);

        $this->preloader->preload([1]);

        $this->assertSame([$row], $this->preloader->take(1));
        $this->assertNull($this->preloader->take(1));
    }

    public function testTakeReturnsNullForOrderThatWasNotPreloaded(): void
    {
        $this->itemResource->expects($this->once())->method('getTaxItemsByOrderIds')->willReturn([]);

        $this->preloader->preload([1]);

        $this->assertNull($this->preloader->take(2));
    }

    public function testPreloadWithoutOrdersRunsNoQuery(): void
    {
        $this->itemResource->expects($this->never())->method('getTaxItemsByOrderIds');

        $this->preloader->preload([]);

        $this->assertNull($this->preloader->take(1));
    }

    public function testClearAndResetStateForgetPreloadedRows(): void
    {
        $this->itemResource->expects($this->exactly(2))->method('getTaxItemsByOrderIds')->willReturn([]);

        $this->preloader->preload([1]);
        $this->preloader->clear();
        $this->assertNull($this->preloader->take(1));

        $this->preloader->preload([1]);
        $this->preloader->_resetState();
        $this->assertNull($this->preloader->take(1));
    }
}
