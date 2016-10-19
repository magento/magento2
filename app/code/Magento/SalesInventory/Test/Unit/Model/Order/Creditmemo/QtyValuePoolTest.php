<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Test\Unit\Model\Order\Creditmemo;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\SalesInventory\Model\Order\Creditmemo\BundleQtyValue;
use Magento\SalesInventory\Model\Order\Creditmemo\QtyValueInterface;
use Magento\SalesInventory\Model\Order\Creditmemo\QtyValuePool;
use Magento\SalesInventory\Model\Order\Creditmemo\SimpleQtyValue;

/**
 * Class QtyValuePoolTest
 */
class QtyValuePoolTest extends \PHPUnit_Framework_TestCase
{
    /** @var  QtyValuePool */
    private $qtyValuePool;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|ProductRepositoryInterface */
    private $productRepositoryMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|OrderItemRepositoryInterface */
    private $orderItemRepositoryMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|QtyValueInterface */
    private $bundleQtyValueMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|QtyValueInterface */
    private $simpleQtyValueMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|CreditmemoInterface */
    private $creditmemoMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|CreditmemoItemInterface */
    private $creditmemoItemMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|ProductInterface */
    private $productMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|OrderItemInterface */
    private $orderItemMock;

    /** @var  array */
    private $qtyVelues;

    public function setUp()
    {
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItemRepositoryMock = $this->getMockBuilder(OrderItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleQtyValueMock = $this->getMockBuilder(BundleQtyValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->simpleQtyValueMock = $this->getMockBuilder(SimpleQtyValue::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoItemMock = $this->getMockBuilder(CreditmemoItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceType', 'getTypeId', 'getProductId'])
            ->getMockForAbstractClass();

        $this->qtyVelues = ['bundle' => $this->bundleQtyValueMock, 'simple' => $this->simpleQtyValueMock];

        $this->qtyValuePool = new QtyValuePool(
            $this->productRepositoryMock,
            $this->orderItemRepositoryMock,
            $this->qtyVelues
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGet($productType, $parentId = null)
    {
        $productId = 2;
        $this->productMock->expects($this->any())
            ->method('getTypeId')
            ->willReturn($productType);
        $this->qtyVelues[$productType]->expects($this->once())
            ->method('get')
            ->willReturn(42);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($this->productMock);
        if ($productType === 'simple') {
            $this->creditmemoItemMock->expects($this->once())
                ->method('getProductId')
                ->willReturn($productId);
        } elseif ($productType === 'bundle') {
            $this->orderItemRepositoryMock->expects($this->once())
                ->method('get')
                ->with($parentId)
                ->willReturn($this->orderItemMock);
            $this->orderItemMock->expects($this->once())
                ->method('getProductId')
                ->willReturn($productId);
        }
        $qty = $this->qtyValuePool->get($this->creditmemoItemMock, $this->creditmemoMock, $parentId);
        $this->assertEquals(42, $qty);
    }

    public function dataProvider()
    {
        return [
            'simple' => ['simple'],
            'bundle' => ['bundle', 2],
        ];
    }
}
