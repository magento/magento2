<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class InvoiceDocumentFactoryTest
 */
class InvoiceDocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceService
     */
    private $invoiceServiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceInterface
     */
    private $invoiceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceDocumentFactory
     */
    private $invoiceDocumentFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceCreationArgumentsInterface
     */
    private $itemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Order
     */
    private $orderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|InvoiceCommentCreationInterface
     */
    private $commentMock;

    protected function setUp(): void
    {
        $this->invoiceServiceMock = $this->getMockBuilder(InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addComment'])
            ->getMockForAbstractClass();

        $this->itemMock = $this->getMockBuilder(InvoiceItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->commentMock = $this->getMockBuilder(InvoiceCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->invoiceDocumentFactory = new InvoiceDocumentFactory($this->invoiceServiceMock);
    }

    public function testCreate()
    {
        $orderId = 10;
        $orderQty = 3;
        $comment = "Comment!";

        $this->itemMock->expects($this->once())
            ->method('getOrderItemId')
            ->willReturn($orderId);

        $this->itemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($orderQty);

        $this->invoiceMock->expects($this->once())
            ->method('addComment')
            ->with($comment, null, null)
            ->willReturnSelf();

        $this->invoiceServiceMock->expects($this->once())
            ->method('prepareInvoice')
            ->with($this->orderMock, [$orderId => $orderQty])
            ->willReturn($this->invoiceMock);

        $this->commentMock->expects($this->once())
            ->method('getComment')
            ->willReturn($comment);

        $this->commentMock->expects($this->once())
            ->method('getIsVisibleOnFront')
            ->willReturn(false);

        $this->assertEquals(
            $this->invoiceMock,
            $this->invoiceDocumentFactory->create($this->orderMock, [$this->itemMock], $this->commentMock)
        );
    }
}
