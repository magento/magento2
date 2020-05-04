<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Creditmemo\Total\Subtotal;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubtotalTest extends TestCase
{
    /**
     * @var Subtotal
     */
    protected $total;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemoMock;

    /**
     * @var Item|MockObject
     */
    protected $creditmemoItemMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|MockObject
     */
    protected $orderItemMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->createPartialMock(
            Order::class,
            ['getBaseShippingDiscountAmount', 'getBaseShippingAmount', 'getShippingAmount']
        );
        $this->orderItemMock = $this->getMockBuilder(Order::class)
            ->addMethods(['isDummy', 'getQtyInvoiced', 'getQty', 'getQtyRefunded'])
            ->onlyMethods(['getDiscountInvoiced', 'getBaseDiscountInvoiced', 'getDiscountRefunded'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->addMethods(['setBaseCost'])
            ->onlyMethods(
                [
                    'getAllItems',
                    'getOrder',
                    'getBaseShippingAmount',
                    'roundPrice',
                    'setDiscountAmount',
                    'setBaseDiscountAmount',
                    'setSubtotal',
                    'setBaseSubtotal',
                    'setSubtotalInclTax',
                    'setBaseSubtotalInclTax',
                    'getGrandTotal',
                    'setGrandTotal',
                    'getBaseGrandTotal',
                    'setBaseGrandTotal'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getHasChildren'])
            ->onlyMethods(
                [
                    'getBaseCost',
                    'getQty',
                    'getOrderItem',
                    'setDiscountAmount',
                    'setBaseDiscountAmount',
                    'isLast',
                    'getRowTotalInclTax',
                    'getBaseRowTotalInclTax',
                    'getRowTotal',
                    'getBaseRowTotal',
                    'calcRowTotal'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->total = new Subtotal();
    }

    public function testCollect()
    {
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->once())
            ->method('calcRowTotal')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->once())
            ->method('getRowTotal')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->once())
            ->method('getRowTotalInclTax')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->once())
            ->method('getBaseRowTotalInclTax')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('setSubtotal')
            ->with(1)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseSubtotal')
            ->with(1)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setSubtotalInclTax')
            ->with(1)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseSubtotalInclTax')
            ->with(1)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('setGrandTotal')
            ->with(2)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseGrandTotal')
            ->with(2)
            ->willReturnSelf();
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }
}
