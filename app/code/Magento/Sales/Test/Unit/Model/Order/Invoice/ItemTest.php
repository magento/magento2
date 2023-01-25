<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Invoice;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item;
use Magento\Sales\Model\Order\ItemFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Item|MockObject
     */
    protected $item;

    /**
     * @var ItemFactory|MockObject
     */
    protected $orderItemFactoryMock;

    /**
     * @var Invoice|MockObject
     */
    protected $invoiceMock;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|MockObject
     */
    protected $orderItemMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->orderItemFactoryMock = $this->createPartialMock(
            ItemFactory::class,
            ['create']
        );
        $this->invoiceMock = $this->createMock(Invoice::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->orderItemMock = $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, [
            'load', 'isDummy', 'getIsQtyDecimal', 'getQtyToInvoice', 'getQtyInvoiced', 'getTaxInvoiced',
            'getBaseTaxInvoiced', 'getDiscountTaxCompensationInvoiced',
            'getBaseDiscountTaxCompensationInvoiced', 'getDiscountInvoiced',
            'getBaseDiscountInvoiced', 'getRowInvoiced', 'getBaseRowInvoiced', 'setQtyInvoiced', 'setTaxInvoiced',
            'setBaseTaxInvoiced', 'setDiscountTaxCompensationInvoiced',
            'setBaseDiscountTaxCompensationInvoiced', 'setDiscountInvoiced',
            'setBaseDiscountInvoiced', 'setRowInvoiced', 'setBaseRowInvoiced', 'getQtyOrdered', 'getRowTotal',
            'getBaseRowTotal', 'getRowTotalInclTax', 'getBaseRowTotalInclTax'
        ]);
        $this->item = $this->objectManager->getObject(
            Item::class,
            [
                'orderItemFactory' => $this->orderItemFactoryMock
            ]
        );
    }

    public function testGetOrderItemFromOrder()
    {
        $this->invoiceMock->expects($this->once())->method('getOrder')->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('getItemById')->with(1)->willReturn($this->orderItemMock);
        $this->item->setInvoice($this->invoiceMock);
        $this->item->setOrderItemId(1);
        $this->assertEquals($this->orderItemMock, $this->item->getOrderItem());
    }

    public function testGetOrderItemFromFactory()
    {
        $this->orderItemFactoryMock->expects($this->once())->method('create')->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())->method('load')->with(1)->willReturn($this->orderItemMock);
        $this->item->setOrderItemId(1);
        $this->assertEquals($this->orderItemMock, $this->item->getOrderItem());
    }

    public function testSetQty()
    {
        $qty = 3;
        $this->assertEquals($this->item->setQty($qty), $this->item);
        $this->assertEquals($this->item->getQty(), $qty);
    }

    public function testRegister()
    {
        $this->orderItemFactoryMock->expects($this->once())->method('create')->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())->method('load')->with(1)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('getQtyInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getTaxInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseTaxInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getDiscountTaxCompensationInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseDiscountTaxCompensationInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getDiscountInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseDiscountInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getRowInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseRowInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('setQtyInvoiced')->with(2)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setTaxInvoiced')->with(2)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setBaseTaxInvoiced')->with(2)->willReturnSelf();
        $this->orderItemMock->expects($this->once())
            ->method('setDiscountTaxCompensationInvoiced')->with(2)->willReturnSelf();
        $this->orderItemMock->expects($this->once())
            ->method('setBaseDiscountTaxCompensationInvoiced')->with(2)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setDiscountInvoiced')->with(2)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setBaseDiscountInvoiced')->with(2)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setRowInvoiced')->with(2)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setBaseRowInvoiced')->with(2)->willReturnSelf();

        $this->item->setData(
            [
                'order_item_id' => 1,
                'qty' => 1,
                'tax_amount' => 1,
                'base_tax_amount' => 1,
                'discount_tax_compensation_amount' => 1,
                'base_discount_tax_compensation_amount' => 1,
                'discount_amount' => 1,
                'base_discount_amount' => 1,
                'row_total' => 1,
                'base_row_total' => 1
            ]
        );
        $this->assertEquals($this->item->register(), $this->item);
    }

    public function testCancel()
    {
        $this->orderItemFactoryMock->expects($this->once())->method('create')->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())->method('load')->with(1)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('getQtyInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getTaxInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseTaxInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getDiscountTaxCompensationInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseDiscountTaxCompensationInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getDiscountInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseDiscountInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getRowInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseRowInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('setQtyInvoiced')->with(0)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setTaxInvoiced')->with(0)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setBaseTaxInvoiced')->with(0)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setDiscountTaxCompensationInvoiced')
            ->with(0)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setBaseDiscountTaxCompensationInvoiced')
            ->with(0)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setDiscountInvoiced')->with(0)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setBaseDiscountInvoiced')->with(0)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setRowInvoiced')->with(0)->willReturnSelf();
        $this->orderItemMock->expects($this->once())->method('setBaseRowInvoiced')->with(0)->willReturnSelf();

        $this->item->setData(
            [
                'order_item_id' => 1,
                'qty' => 1,
                'tax_amount' => 1,
                'base_tax_amount' => 1,
                'discount_tax_compensation_amount' => 1,
                'base_discount_tax_compensation_amount' => 1,
                'discount_amount' => 1,
                'base_discount_amount' => 1,
                'row_total' => 1,
                'base_row_total' => 1
            ]
        );
        $this->assertEquals($this->item->cancel(), $this->item);
    }

    public function testCalcRowTotal()
    {
        $this->item->setData('order_item_id', 1);
        $this->item->setData('qty', 2);
        $this->item->setInvoice($this->invoiceMock);
        $this->invoiceMock->expects($this->once())->method('getOrder')->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('getItemById')->with(1)->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())->method('getQtyOrdered')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getRowTotal')->willReturn(2);
        $this->orderItemMock->expects($this->once())->method('getRowInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseRowTotal')->willReturn(2);
        $this->orderItemMock->expects($this->once())->method('getBaseRowInvoiced')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getRowTotalInclTax')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getBaseRowTotalInclTax')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getQtyToInvoice')->willReturn(1);
        $this->orderItemMock->expects($this->once())->method('getQtyInvoiced')->willReturn(0);
        $this->invoiceMock->expects($this->exactly(4))
            ->method('roundPrice')
            ->willReturnMap([
                [2, 'regular', false, 2],
                [2, 'base', false, 2],
                [2, 'including', false, 2],
                [2, 'including_base', false, 2],
            ]);
        $this->assertEquals($this->item->calcRowTotal(), $this->item);
    }
}
