<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Plugin;

use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var \Magento\Bundle\Model\Sales\Order\Plugin\Item
     */
    private $plugin;

    /**
     * @var (Item&MockObject)|MockObject
     */
    private $itemMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->itemMock = $this->createMock(Item::class);
        $this->plugin = new \Magento\Bundle\Model\Sales\Order\Plugin\Item();
    }

    /**
     * @return void
     */
    public function testAfterGetQtyToCancelIfProductIsBundle()
    {
        $qtyToCancel = 10;
        $result = 5;

        $this->itemMock
            ->expects($this->once())
            ->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->itemMock->expects($this->once())->method('isDummy')->willReturn(true);
        $this->itemMock->expects($this->once())->method('getQtyToInvoice')->willReturn(15);
        $this->itemMock->expects($this->once())->method('getSimpleQtyToShip')->willReturn($qtyToCancel);
        $this->assertEquals($qtyToCancel, $this->plugin->afterGetQtyToCancel($this->itemMock, $result));
    }

    /**
     * @return void
     */
    public function testAfterGetQtyToCancelIfParentProductIsBundle()
    {
        $qtyToCancel = 10;
        $result = 5;
        $parentItemMock = $this->createMock(Item::class);
        $this->itemMock
            ->expects($this->once())
            ->method('getProductType')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->itemMock->method('getParentItem')->willReturn($parentItemMock);
        $parentItemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->itemMock->expects($this->once())->method('isDummy')->willReturn(false);
        $this->itemMock->expects($this->once())->method('getQtyToInvoice')->willReturn(15);
        $this->itemMock->expects($this->once())->method('getQtyToShip')->willReturn($qtyToCancel);
        $this->assertEquals($qtyToCancel, $this->plugin->afterGetQtyToCancel($this->itemMock, $result));
    }
    public function testAfterGetQtyToCancelForSimpleProduct()
    {
        $result = 5;
        $this->itemMock
            ->expects($this->once())
            ->method('getProductType')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->itemMock->method('getParentItem')->willReturn(false);
        $this->itemMock->expects($this->never())->method('isDummy');
        $this->itemMock->expects($this->never())->method('getQtyToInvoice');
        $this->assertEquals($result, $this->plugin->afterGetQtyToCancel($this->itemMock, $result));
    }

    public function testAfterIsProcessingAvailableForProductWithoutParent()
    {
        $this->itemMock->expects($this->once())->method('getParentItem')->willReturn(false);
        $this->assertFalse($this->plugin->afterIsProcessingAvailable($this->itemMock, false));
    }

    public function testAfterIsProcessingAvailableForProductWhenParentIsBundle()
    {
        $parentItemMock = $this->createMock(Item::class);
        $this->itemMock->method('getParentItem')->willReturn($parentItemMock);
        $parentItemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->itemMock->expects($this->once())->method('getSimpleQtyToShip')->willReturn(10);
        $this->itemMock->expects($this->once())->method('getQtyToCancel')->willReturn(5);
        $this->assertTrue($this->plugin->afterIsProcessingAvailable($this->itemMock, false));
    }

    public function testAfterIsProcessingAvailableForBundleProduct()
    {
        $this->itemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn(Type::TYPE_BUNDLE);
        $this->itemMock->expects($this->once())->method('getSimpleQtyToShip')->willReturn(10);
        $this->itemMock->expects($this->once())->method('getQtyToCancel')->willReturn(5);
        $this->assertTrue($this->plugin->afterIsProcessingAvailable($this->itemMock, false));
    }
}
