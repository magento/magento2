<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

/**
 * Class DiscountTest
 */
class DiscountTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Total\Cost
     */
    protected $total;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoItemMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    protected function setUp()
    {
        $this->orderMock = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['getBaseShippingDiscountAmount', 'getBaseShippingAmount', 'getShippingAmount']
        );
        $this->orderItemMock = $this->createPartialMock(\Magento\Sales\Model\Order::class, [
                'isDummy', 'getDiscountInvoiced', 'getBaseDiscountInvoiced', 'getQtyInvoiced', 'getQty',
                'getDiscountRefunded', 'getQtyRefunded'
            ]);
        $this->creditmemoMock = $this->createPartialMock(\Magento\Sales\Model\Order\Creditmemo::class, [
                'setBaseCost', 'getAllItems', 'getOrder', 'getBaseShippingAmount', 'roundPrice',
                'setDiscountAmount', 'setBaseDiscountAmount', 'getBaseShippingInclTax', 'getBaseShippingTaxAmount'
            ]);
        $this->creditmemoItemMock = $this->createPartialMock(\Magento\Sales\Model\Order\Creditmemo\Item::class, [
                'getHasChildren', 'getBaseCost', 'getQty', 'getOrderItem', 'setDiscountAmount',
                'setBaseDiscountAmount', 'isLast'
            ]);
        $this->total = new \Magento\Sales\Model\Order\Creditmemo\Total\Discount();
    }

    public function testCollect()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingDiscountAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->exactly(2))
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    public function testCollectNoBaseShippingAmount()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn(0);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingInclTax')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingTaxAmount')
            ->willReturn(0);
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingDiscountAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->exactly(2))
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    public function testCollectZeroShipping()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn('0.0000');
        $this->orderMock->expects($this->never())
            ->method('getBaseShippingDiscountAmount');
        $this->orderMock->expects($this->never())
            ->method('getBaseShippingAmount');
        $this->orderMock->expects($this->never())
            ->method('getShippingAmount');
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }
}
