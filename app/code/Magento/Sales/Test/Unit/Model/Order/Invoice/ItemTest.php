<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Invoice;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;
    /**
     * @var \Magento\Sales\Model\Order\Invoice\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemFactoryMock;
    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMock;
    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;
    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderItemFactoryMock = $this->getMock(
            'Magento\Sales\Model\Order\ItemFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->invoiceMock = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            [],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );
        $this->orderItemMock = $this->getMock(
            'Magento\Sales\Model\Order\Item',
            [
                'load', 'isDummy', 'getIsQtyDecimal', 'getQtyToInvoice', 'getQtyInvoiced', 'getTaxInvoiced',
                'getBaseTaxInvoiced', 'getDiscountTaxCompensationInvoiced',
                'getBaseDiscountTaxCompensationInvoiced', 'getDiscountInvoiced',
                'getBaseDiscountInvoiced', 'getRowInvoiced', 'getBaseRowInvoiced', 'setQtyInvoiced', 'setTaxInvoiced',
                'setBaseTaxInvoiced', 'setDiscountTaxCompensationInvoiced',
                'setBaseDiscountTaxCompensationInvoiced', 'setDiscountInvoiced',
                'setBaseDiscountInvoiced', 'setRowInvoiced', 'setBaseRowInvoiced', 'getQtyOrdered', 'getRowTotal',
                'getBaseRowTotal', 'getRowTotalInclTax', 'getBaseRowTotalInclTax'
            ],
            [],
            '',
            false
        );
        $this->item = $this->objectManager->getObject(
            'Magento\Sales\Model\Order\Invoice\Item',
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
