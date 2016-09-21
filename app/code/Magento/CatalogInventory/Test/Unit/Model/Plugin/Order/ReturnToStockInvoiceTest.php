<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Plugin\Order;

use Magento\CatalogInventory\Model\Order\ReturnProcessor;
use Magento\CatalogInventory\Model\Plugin\Order\ReturnToStockInvoice;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundInvoiceInterface;
use Magento\Sales\Api\RefundOrderInterface;

/**
 * Class ReturnToStockInvoiceTest
 */
class ReturnToStockInvoiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ReturnToStockInvoice */
    private $returnTOStock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ReturnProcessor
     */
    private $returnProcessorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CreditmemoRepositoryInterface
     */
    private $creditmemoRepositoryMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject|InvoiceRepositoryInterface
     */
    private $invoiceRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OrderRepositoryInterface
     */
    private $orderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RefundOrderInterface
     */
    private $refundInvoiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CreditmemoCreationArgumentsInterface
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|OrderInterface
     */
    private $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CreditmemoInterface
     */
    private $creditmemoMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|InvoiceInterface
     */
    private $invoiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CreditmemoCreationArgumentsInterface
     */
    private $extencionAttributesMock;

    protected function setUp()
    {
        $this->returnProcessorMock = $this->getMockBuilder(ReturnProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoRepositoryMock = $this->getMockBuilder(CreditmemoRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->refundInvoiceMock = $this->getMockBuilder(RefundInvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoCreationArgumentsMock = $this->getMockBuilder(CreditmemoCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extencionAttributesMock = $this->getMockBuilder(CreditmemoCreationArgumentsExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getReturnToStockItems'])
            ->getMock();
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->returnTOStock = new ReturnToStockInvoice(
            $this->returnProcessorMock,
            $this->creditmemoRepositoryMock,
            $this->orderRepositoryMock,
            $this->invoiceRepositoryMock
        );
    }

    public function testAfterExecute()
    {
        $orderId = 1;
        $creditmemoId = 99;
        $items = [];
        $returnToStockItems = [1];
        $invoiceId = 98;
        $this->creditmemoCreationArgumentsMock->expects($this->exactly(3))
            ->method('getExtensionAttributes')
            ->willReturn($this->extencionAttributesMock);

        $this->invoiceRepositoryMock->expects($this->once())
            ->method('get')
            ->with($invoiceId)
            ->willReturn($this->invoiceMock);

        $this->extencionAttributesMock->expects($this->exactly(2))
            ->method('getReturnToStockItems')
            ->willReturn($returnToStockItems);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->orderMock);

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('get')
            ->with($creditmemoId)
            ->willReturn($this->creditmemoMock);

        $this->returnProcessorMock->expects($this->once())
            ->method('execute')
            ->with($this->creditmemoMock, $this->orderMock, $returnToStockItems);

        $this->invoiceMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn($orderId);

        $this->assertEquals(
            $this->returnTOStock->afterExecute(
                $this->refundInvoiceMock,
                $creditmemoId,
                $invoiceId,
                $items,
                false,
                false,
                false,
                null,
                $this->creditmemoCreationArgumentsMock
            ),
            $creditmemoId
        );
    }
}
