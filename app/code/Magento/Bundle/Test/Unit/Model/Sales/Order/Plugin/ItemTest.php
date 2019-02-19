<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Plugin;

class ItemTest extends \PHPUnit\Framework\TestCase
{
    private $plugin;

    private $itemMock;

    protected function setUp()
    {
        $this->itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->plugin = new \Magento\Bundle\Model\Sales\Order\Plugin\Item();
    }

    public function testAfterGetQtyToCancelIfProductIsBundle()
    {
        $qtyToCancel = 10;
        $result = 5;

        $this->itemMock
            ->expects($this->once())
            ->method('getProductType')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->itemMock->expects($this->once())->method('isDummy')->willReturn(true);
        $this->itemMock->expects($this->once())->method('getQtyToInvoice')->willReturn(15);
        $this->itemMock->expects($this->once())->method('getSimpleQtyToShip')->willReturn($qtyToCancel);
        $this->assertEquals($qtyToCancel, $this->plugin->afterGetQtyToCancel($this->itemMock, $result));
    }

    public function testAfterGetQtyToCancelIfParentProductIsBundle()
    {
        $qtyToCancel = 10;
        $result = 5;
        $parentItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMock
            ->expects($this->once())
            ->method('getProductType')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->itemMock->expects($this->any())->method('getParentItem')->willReturn($parentItemMock);
        $parentItemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
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
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->itemMock->expects($this->any())->method('getParentItem')->willReturn(false);
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
        $parentItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMock->expects($this->any())->method('getParentItem')->willReturn($parentItemMock);
        $parentItemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->itemMock->expects($this->once())->method('getSimpleQtyToShip')->willReturn(10);
        $this->itemMock->expects($this->once())->method('getQtyToCancel')->willReturn(5);
        $this->assertTrue($this->plugin->afterIsProcessingAvailable($this->itemMock, false));
    }

    public function testAfterIsProcessingAvailableForBundleProduct()
    {
        $this->itemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->itemMock->expects($this->once())->method('getSimpleQtyToShip')->willReturn(10);
        $this->itemMock->expects($this->once())->method('getQtyToCancel')->willReturn(5);
        $this->assertTrue($this->plugin->afterIsProcessingAvailable($this->itemMock, false));
    }
}
