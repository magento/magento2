<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\InvoiceQuantityValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Model\Order\InvoiceValidator class
 */
class InvoiceQuantityValidatorTest extends TestCase
{
    /**
     * @var InvoiceQuantityValidator|MockObject
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var InvoiceInterface|MockObject
     */
    private $invoiceMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTotalQty', 'getItems'])
            ->getMockForAbstractClass();
        $this->orderRepositoryMock = $this->getMockBuilder(
            OrderRepositoryInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->orderRepositoryMock->expects($this->any())->method('get')->willReturn($this->orderMock);
        $this->model = $this->objectManager->getObject(
            InvoiceQuantityValidator::class,
            ['orderRepository' => $this->orderRepositoryMock]
        );
    }

    public function testValidate()
    {
        $expectedResult = [];
        $invoiceItemMock = $this->getInvoiceItemMock(1, 1);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $orderItemMock = $this->getOrderItemMock(1, 1, true);
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItemMock]);
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getOrderId')
            ->willReturn(1);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock)
        );
    }

    public function testValidateInvoiceQtyBiggerThanOrder()
    {
        $orderItemId = 1;
        $message = 'The quantity to invoice must not be greater than the uninvoiced quantity for product SKU "%1".';
        $expectedResult = [__($message, $orderItemId)];
        $invoiceItemMock = $this->getInvoiceItemMock($orderItemId, 2);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $orderItemMock = $this->getOrderItemMock($orderItemId, 1, false);
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItemMock]);
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getOrderId')
            ->willReturn(1);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock)
        );
    }

    public function testValidateNoOrderItems()
    {
        $expectedResult = [__('The invoice contains one or more items that are not part of the original order.')];
        $invoiceItemMock = $this->getInvoiceItemMock(1, 1);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getOrderId')
            ->willReturn(1);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock)
        );
    }

    public function testValidateNoOrder()
    {
        $expectedResult = [__('Order Id is required for invoice document')];
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock)
        );
    }

    public function testValidateNoInvoiceItems()
    {
        $expectedResult = [__("The invoice can't be created without products. Add products and try again.")];
        $orderItemId = 1;
        $invoiceItemMock = $this->getInvoiceItemMock($orderItemId, 0);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $orderItemMock = $this->getOrderItemMock($orderItemId, 1, false);
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItemMock]);
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getOrderId')
            ->willReturn(1);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock)
        );
    }

    /**
     * @param $orderItemId
     * @param $qty
     * @return MockObject
     */
    private function getInvoiceItemMock($orderItemId, $qty)
    {
        $invoiceItemMock = $this->getMockBuilder(InvoiceItemInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrderItemId', 'getQty'])
            ->getMockForAbstractClass();
        $invoiceItemMock->expects($this->once())->method('getOrderItemId')->willReturn($orderItemId);
        $invoiceItemMock->expects($this->once())->method('getQty')->willReturn($qty);
        return $invoiceItemMock;
    }

    /**
     * @param $id
     * @param $qtyToInvoice
     * @param $isDummy
     * @return MockObject
     */
    private function getOrderItemMock($id, $qtyToInvoice, $isDummy)
    {
        $orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSku'])
            ->addMethods(['getId', 'getQtyToInvoice', 'isDummy'])
            ->getMockForAbstractClass();
        $orderItemMock->expects($this->any())->method('getId')->willReturn($id);
        $orderItemMock->expects($this->any())->method('getQtyToInvoice')->willReturn($qtyToInvoice);
        $orderItemMock->expects($this->any())->method('isDummy')->willReturn($isDummy);
        $orderItemMock->expects($this->any())->method('getSku')->willReturn($id);
        return $orderItemMock;
    }
}
