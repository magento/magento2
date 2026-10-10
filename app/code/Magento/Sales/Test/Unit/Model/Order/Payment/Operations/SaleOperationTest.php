<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
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
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class SaleOperationTest extends TestCase
{
    use MockCreationTrait;

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
        $this->processInvoiceOperation = $this->createMock(ProcessInvoiceOperation::class);

        $this->model = new SaleOperation(
            $this->processInvoiceOperation
        );
    }

    /**
     * Tests a case when 'sale' operation is called with fraud payment.
     *
     * @throws LocalizedException
     */
    #[DataProvider('saleDataProvider')]
    public function testExecute(\Closure $invoice)
    {
        $invoice = $invoice($this);
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
        $paymentMethod = $this->createMock(MethodInterface::class);

        /** @var Payment|MockObject  $orderPayment | */
        $orderPayment = $this->createPartialMockWithReflection(
            Payment::class,
            ['setCreatedInvoice', 'getOrder', 'getMethodInstance', 'getIsFraudDetected']
        );
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
    public static function saleDataProvider()
    {
        return [
            ['invoice' => static fn (self $testCase) => $testCase->getPaidInvoice()],
            ['invoice' => static fn (self $testCase) => $testCase->getUnpaidInvoice()]
        ];
    }

    /**
     * @return MockObject
     */
    public function getPaidInvoice(): MockObject
    {
        $invoice = $this->createPartialMockWithReflection(
            Invoice::class,
            ['getIsPaid', 'register', 'pay']
        );
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
    public function getUnpaidInvoice(): MockObject
    {
        $invoice = $this->createPartialMockWithReflection(
            Invoice::class,
            ['getIsPaid', 'register', 'pay']
        );
        $invoice->expects($this->once())
            ->method('register');
        $invoice->method('getIsPaid')
            ->willReturn(false);
        $invoice->expects($this->never())
            ->method('pay');

        return $invoice;
    }
}
