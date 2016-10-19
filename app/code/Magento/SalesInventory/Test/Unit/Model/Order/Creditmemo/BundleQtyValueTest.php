<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Test\Unit\Model\Order\Creditmemo;

use Magento\Sales\Api\CreditmemoItemRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\SalesInventory\Model\Order\Creditmemo\BundleQtyValue;

/**
 * Class ReturnProcessorTest
 */
class BundleQtyValueTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject|CreditmemoItemRepositoryInterface */
    private $creditmemoItemRepositoryMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|OrderItemRepositoryInterface */
    private $orderItemRepositoryMock;

    /** @var  BundleQtyValue */
    private $bundleQtyValue;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|CreditmemoItemInterface */
    private $creditmemoItemMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|CreditmemoInterface */
    private $creditmemoMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|OrderItemInterface */
    private $parentOrderItemMock;

    public function setUp()
    {
        $this->creditmemoItemRepositoryMock = $this->getMockBuilder(CreditmemoItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItemRepositoryMock = $this->getMockBuilder(OrderItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItemMock = $this->getMockBuilder(CreditmemoItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->parentOrderItemMock = $this->getMockBuilder(OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getQtyOrdered'])
            ->getMockForAbstractClass();

        $this->bundleQtyValue = new BundleQtyValue(
            $this->creditmemoItemRepositoryMock,
            $this->orderItemRepositoryMock
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGet($priceType, $result)
    {
        $parentCreditMemoItem = clone $this->creditmemoItemMock;
        $this->creditmemoItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($priceType === 1 ? 1 : $result);

        if ($priceType === 1) {
            $orderItemId = 101;
            $qtyOrdered = 200;
            $parentQtyOrdered = 2;
            $parentItemId = 42;
            $this->creditmemoItemMock->expects($this->once())
                ->method('getOrderItemId')
                ->willReturn($orderItemId);
            $orderItemMock = clone $this->parentOrderItemMock;

            $this->orderItemRepositoryMock->expects($this->once())
                ->method('get')
                ->with($orderItemId)
                ->willReturn($orderItemMock);

            $orderItemMock->expects($this->once())
                ->method('getQtyOrdered')
                ->willReturn($qtyOrdered);

            $this->parentOrderItemMock->expects($this->once())
                ->method('getQtyOrdered')
                ->willReturn($parentQtyOrdered);

            $this->parentOrderItemMock->expects($this->once())
                ->method('getId')
                ->willReturn($parentItemId);

            $this->creditmemoMock->expects($this->once())
                ->method('getItems')
                ->willReturn([$parentCreditMemoItem]);

            $parentCreditMemoItem->expects($this->once())
                ->method('getOrderItemId')
                ->willReturn($parentItemId);

            $parentCreditMemoItem->expects($this->once())
                ->method('getQty')
                ->willReturn(2);
        }

        $resultQty = $this->bundleQtyValue->get(
            $this->creditmemoItemMock,
            $this->creditmemoMock,
            $this->parentOrderItemMock, $priceType
        );
        $this->assertEquals($resultQty, $result);
    }

    public function dataProvider()
    {
        return [
            'dynamic' => [2, 20],
            'fixed' => [1, 200],
        ];
    }
}
