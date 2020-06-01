<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\ItemFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $orderItemFactoryMock;

    /** @var Item */
    protected $item;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->orderItemFactoryMock = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->item = $objectManager->getObject(
            Item::class,
            [
                'orderItemFactory' => $this->orderItemFactoryMock
            ]
        );
    }

    public function testGetOrderItemExist()
    {
        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->item->setOrderItem($orderItemMock);
        $result = $this->item->getOrderItem();
        $this->assertInstanceOf(\Magento\Sales\Model\Order\Item::class, $result);
    }

    public function testGetOrderItemFromCreditmemo()
    {
        $orderItemId = 1;

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);

        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->item->setData(CreditmemoItemInterface::ORDER_ITEM_ID, $orderItemId);
        $this->item->setCreditmemo($creditmemoMock);
        $result = $this->item->getOrderItem();
        $this->assertInstanceOf(\Magento\Sales\Model\Order\Item::class, $result);
    }

    public function testGetOrderItemFromFactory()
    {
        $orderItemId = 1;

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('load')
            ->with($orderItemId)
            ->willReturnSelf();

        $this->orderItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderItemMock);

        $this->item->setData(CreditmemoItemInterface::ORDER_ITEM_ID, $orderItemId);
        $result = $this->item->getOrderItem();
        $this->assertInstanceOf(\Magento\Sales\Model\Order\Item::class, $result);
    }

    public function testSetQty()
    {
        $qty = 10;
        $this->item->setQty($qty);
        $this->assertEquals($qty, $this->item->getQty());
    }

    public function testRegister()
    {
        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getTaxRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getBaseTaxRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getDiscountTaxCompensationRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getBaseDiscountTaxCompensationRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getAmountRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getBaseAmountRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getBaseDiscountRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getQtyToRefund')
            ->willReturn(1);
        $this->item->setQty(1);
        $this->item->setTaxAmount(1);
        $this->item->setBaseTaxAmount(1);
        $this->item->setDiscountTaxCompensationAmount(1);
        $this->item->setBaseDiscountTaxCompensationAmount(1);
        $this->item->setRowTotal(1);
        $this->item->setBaseRowTotal(1);
        $this->item->setDiscountAmount(1);
        $this->item->setBaseDiscountAmount(1);
        $this->item->setOrderItem($orderItemMock);
        $result = $this->item->register();
        $this->assertInstanceOf(Item::class, $result);
    }

    public function testRegisterWithException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('We found an invalid quantity to refund item "test".');
        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQtyRefunded'])
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(1);
        $this->item->setQty(2);
        $this->item->setOrderItem($orderItemMock);
        $this->item->setName('test');
        $result = $this->item->register();
        $this->assertInstanceOf(Item::class, $result);
    }

    public function testCancel()
    {
        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('setQtyRefunded')
            ->with(0);
        $orderItemMock->expects($this->once())
            ->method('getTaxRefunded')
            ->willReturn(10);
        $orderItemMock->expects($this->once())
            ->method('getBaseTaxAmount')
            ->willReturn(5);
        $orderItemMock->expects($this->exactly(2))
            ->method('getQtyOrdered')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('setTaxRefunded')
            ->with(5);

        $orderItemMock->expects($this->once())
            ->method('setDiscountTaxCompensationRefunded')
            ->with(0);
        $orderItemMock->expects($this->once())
            ->method('getDiscountTaxCompensationRefunded')
            ->willReturn(10);
        $orderItemMock->expects($this->once())
            ->method('getDiscountTaxCompensationAmount')
            ->willReturn(10);
        $orderItemMock->expects($this->once())
            ->method('getQtyToRefund')
            ->willReturn(1);

        $this->item->setQty(1);
        $this->item->setOrderItem($orderItemMock);
        $result = $this->item->cancel();
        $this->assertInstanceOf(Item::class, $result);
    }

    /**
     * @dataProvider calcRowTotalDataProvider
     */
    public function testCalcRowTotal($qty)
    {
        $creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->exactly(4))
            ->method('roundPrice')
            ->willReturnCallback(function ($arg) {
                return round($arg, 2);
            });

        $qtyInvoiced = 10;
        $qtyRefunded = 2;
        $qtyAvailable = $qtyInvoiced - $qtyRefunded;

        $rowInvoiced = 5;
        $amountRefunded = 2;

        $expectedRowTotal = ($rowInvoiced - $amountRefunded) / $qtyAvailable * $qty;
        $expectedRowTotal = round($expectedRowTotal, 2);

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn($qtyInvoiced);
        $orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn($qtyRefunded);
        $orderItemMock->expects($this->once())
            ->method('getRowInvoiced')
            ->willReturn($rowInvoiced);
        $orderItemMock->expects($this->once())
            ->method('getAmountRefunded')
            ->willReturn($amountRefunded);
        $orderItemMock->expects($this->once())
            ->method('getBaseRowInvoiced')
            ->willReturn($rowInvoiced);
        $orderItemMock->expects($this->once())
            ->method('getBaseAmountRefunded')
            ->willReturn($amountRefunded);
        $orderItemMock->expects($this->once())
            ->method('getRowTotalInclTax')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getBaseRowTotalInclTax')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(1);
        $orderItemMock->expects($this->once())
            ->method('getQtyOrdered')
            ->willReturn(1);
        $orderItemMock->expects($this->any())
            ->method('getQtyToRefund')
            ->willReturn($qtyAvailable);

        $this->item->setQty($qty);
        $this->item->setCreditmemo($creditmemoMock);
        $this->item->setOrderItem($orderItemMock);
        $result = $this->item->calcRowTotal();

        $this->assertInstanceOf(Item::class, $result);
        $this->assertEquals($expectedRowTotal, $this->item->getData('row_total'));
        $this->assertEquals($expectedRowTotal, $this->item->getData('base_row_total'));
    }

    /**
     * @return array
     */
    public function calcRowTotalDataProvider()
    {
        return [
            'qty 1' => [1],
            'qty 0' => [0],
        ];
    }
}
