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
use Magento\Sales\Model\Order\Payment\Operations\SaleOperation;
use Magento\Sales\Model\Order\Payment\Operations\ProcessInvoiceOperation;

class SaleOperationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProcessInvoiceOperation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $processInvoiceOperation;

    /**
     * @var SaleOperation
     */
    private $model;

    protected function setUp()
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
            ->setMethods(['prepareInvoice', 'addRelatedObject', 'setStatus'])
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

        /** @var MethodInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMethod */
        $paymentMethod = $this->getMockForAbstractClass(MethodInterface::class);
        $paymentMethod->method('canCapture')
            ->willReturn(true);

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject  $orderPayment| */
        $orderPayment = $this->getMockBuilder(Payment::class)
            ->setMethods(['setCreatedInvoice', 'getOrder', 'getMethodInstance', 'getIsFraudDetected'])
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getPaidInvoice(): \PHPUnit_Framework_MockObject_MockObject
    {
        $invoice = $this->getMockBuilder(Invoice::class)
            ->setMethods(['register', 'getIsPaid', 'pay'])
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getUnpaidInvoice(): \PHPUnit_Framework_MockObject_MockObject
    {
        $invoice = $this->getMockBuilder(Invoice::class)
            ->setMethods(['register', 'getIsPaid', 'pay'])
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
