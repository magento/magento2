<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoItemInterface;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemFactoryMock;

    /** @var \Magento\Sales\Model\Order\Creditmemo\Item */
    protected $item;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->orderItemFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Order\ItemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->item = $objectManager->getObject(
            'Magento\Sales\Model\Order\Creditmemo\Item',
            [
                'orderItemFactory' => $this->orderItemFactoryMock
            ]
        );
    }

    public function testGetOrderItemExist()
    {
        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->item->setOrderItem($orderItemMock);
        $result = $this->item->getOrderItem();
        $this->assertInstanceOf('Magento\Sales\Model\Order\Item', $result);
    }

    public function testGetOrderItemFromCreditmemo()
    {
        $orderItemId = 1;

        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getItemById')
            ->with($orderItemId)
            ->willReturn($orderItemMock);

        $creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->item->setData(CreditmemoItemInterface::ORDER_ITEM_ID, $orderItemId);
        $this->item->setCreditmemo($creditmemoMock);
        $result = $this->item->getOrderItem();
        $this->assertInstanceOf('Magento\Sales\Model\Order\Item', $result);
    }

    public function testGetOrderItemFromFactory()
    {
        $orderItemId = 1;

        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
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
        $this->assertInstanceOf('Magento\Sales\Model\Order\Item', $result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We found an invalid quantity to refund item "test_item_name".
     */
    public function testSetQtyDecimalException()
    {
        $qty = 100;
        $orderItemQty = 10;
        $name = 'test_item_name';

        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getIsQtyDecimal')
            ->willReturn(true);
        $orderItemMock->expects($this->once())
            ->method('getQtyToRefund')
            ->willReturn($orderItemQty);

        $this->item->setData(CreditmemoItemInterface::NAME, $name);
        $this->item->setOrderItem($orderItemMock);
        $this->item->setQty($qty);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We found an invalid quantity to refund item "test_item_name2".
     */
    public function testSetQtyNumericException()
    {
        $qty = 100;
        $orderItemQty = 10;
        $name = 'test_item_name2';

        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getIsQtyDecimal')
            ->willReturn(false);
        $orderItemMock->expects($this->once())
            ->method('getQtyToRefund')
            ->willReturn($orderItemQty);

        $this->item->setData(CreditmemoItemInterface::NAME, $name);
        $this->item->setOrderItem($orderItemMock);
        $this->item->setQty($qty);
    }

    public function testSetQty()
    {
        $qty = 10;
        $orderItemQty = 100;

        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemMock->expects($this->once())
            ->method('getIsQtyDecimal')
            ->willReturn(false);
        $orderItemMock->expects($this->once())
            ->method('getQtyToRefund')
            ->willReturn($orderItemQty);

        $this->item->setOrderItem($orderItemMock);
        $this->item->setQty($qty);
        $this->assertEquals($qty, $this->item->getQty());
    }

    public function testRegister()
    {
        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
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
        $data = [
            'qty' => 1,
            'tax_amount' => 1,
            'base_tax_amount' => 1,
            'discount_tax_compensation_amount' => 1,
            'base_discount_tax_compensation_amount' => 1,
            'row_total' => 1,
            'base_row_total' => 1,
            'discount_amount' => 1,
            'base_discount_amount' => 1
        ];
        $this->item->setOrderItem($orderItemMock);
        $this->item->setData($data);
        $result = $this->item->register();
        $this->assertInstanceOf('Magento\Sales\Model\Order\Creditmemo\Item', $result);
    }

    public function testCancel()
    {
        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setQtyRefunded',
                    'getQtyRefunded',
                    'getTaxRefunded',
                    'getBaseTaxAmount',
                    'getQtyOrdered',
                    'setTaxRefunded',
                    'setDiscountTaxCompensationRefunded',
                    'getDiscountTaxCompensationRefunded',
                    'getDiscountTaxCompensationAmount'
                ]
            )
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

        $this->item->setData('qty', 1);
        $this->item->setOrderItem($orderItemMock);
        $result = $this->item->cancel();
        $this->assertInstanceOf('Magento\Sales\Model\Order\Creditmemo\Item', $result);
    }

    /**
     * @dataProvider calcRowTotalDataProvider
     */
    public function testCalcRowTotal($qty)
    {
        $creditmemoMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->getMock();
        $creditmemoMock->expects($this->exactly(4))
            ->method('roundPrice')
            ->will($this->returnCallback(
                function ($arg) {
                    return round($arg, 2);
                }
            ));

        $qtyInvoiced = 10;
        $qtyRefunded = 2;
        $qtyAvailable = $qtyInvoiced - $qtyRefunded;

        $rowInvoiced = 5;
        $amountRefunded = 2;

        $expectedRowTotal = ($rowInvoiced - $amountRefunded) / $qtyAvailable * $qty;
        $expectedRowTotal = round($expectedRowTotal, 2);

        $orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
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

        $this->item->setData('qty', $qty);
        $this->item->setCreditmemo($creditmemoMock);
        $this->item->setOrderItem($orderItemMock);
        $result = $this->item->calcRowTotal();

        $this->assertInstanceOf('Magento\Sales\Model\Order\Creditmemo\Item', $result);
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
