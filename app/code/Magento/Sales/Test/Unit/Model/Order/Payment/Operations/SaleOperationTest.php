<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Operations;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Operations\ProcessInvoiceOperation;
use Magento\Sales\Model\Order\Payment\Operations\SaleOperation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaleOperationTest extends TestCase
{
    /**
     * @var ProcessInvoiceOperation|MockObject
     */
    private $processInvoiceOperation;

    /**
     * @var SaleOperation
     */
    private $model;

    protected function setUp(): void
    {
        $this->processInvoiceOperation = $this->getMockBuilder(ProcessInvoiceOperation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SaleOperation(
            $this->processInvoiceOperation
        );
    }

    /**
     * Tests a case when 'sale' operation is called with fraud payment.
     *
     * @throws LocalizedException
     * @dataProvider saleDataProvider
     */
    public function testExecute(Invoice $invoice)
    {
        $order = $this->getMockBuilder(Order::class)
            ->onlyMethods(['prepareInvoice', 'addRelatedObject', 'setStatus'])
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->once())
            ->method('prepareInvoice')
            ->willReturn($invoice);
        $order->expects($this->once())
            ->method('addRelatedObject');
        $order->expects($this->once())
            ->method('setStatus')
            ->with(Order::STATUS_FRAUD);

        /** @var MethodInterface|MockObject $paymentMethod */
        $paymentMethod = $this->getMockForAbstractClass(MethodInterface::class);

        /** @var Payment|MockObject  $orderPayment | */
        $orderPayment = $this->getMockBuilder(Payment::class)
            ->addMethods(['setCreatedInvoice'])
            ->onlyMethods(['getOrder', 'getMethodInstance', 'getIsFraudDetected'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderPayment->expects($this->once())
            ->method('setCreatedInvoice')
            ->with($invoice);
        $orderPayment->method('getIsFraudDetected')
            ->willReturn(true);
        $orderPayment->method('getOrder')
            ->willReturn($order);
        $orderPayment->method('getMethodInstance')
            ->willReturn($paymentMethod);

        $this->assertInstanceOf(
            Payment::class,
            $this->model->execute($orderPayment)
        );
    }

    /**
     * @return array
     */
    public function saleDataProvider()
    {
        return [
            ['paid invoice' => $this->getPaidInvoice()],
            ['unpaid invoice' => $this->getUnpaidInvoice()]
        ];
    }

    /**
     * @return MockObject
     */
    private function getPaidInvoice(): MockObject
    {
        $invoice = $this->getMockBuilder(Invoice::class)
            ->addMethods(['getIsPaid'])
            ->onlyMethods(['register', 'pay'])
            ->disableOriginalConstructor()
            ->getMock();
        $invoice->expects($this->once())
            ->method('register');
        $invoice->method('getIsPaid')
            ->willReturn(true);
        $invoice->expects($this->once())
            ->method('pay');

        return $invoice;
    }

    /**
     * @return MockObject
     */
    private function getUnpaidInvoice(): MockObject
    {
        $invoice = $this->getMockBuilder(Invoice::class)
            ->addMethods(['getIsPaid'])
            ->onlyMethods(['register', 'pay'])
            ->disableOriginalConstructor()
            ->getMock();
        $invoice->expects($this->once())
            ->method('register');
        $invoice->method('getIsPaid')
            ->willReturn(false);
        $invoice->expects($this->never())
            ->method('pay');

        return $invoice;
    }
}
