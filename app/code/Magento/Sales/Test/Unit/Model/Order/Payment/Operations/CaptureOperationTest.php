<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Operations;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Operations\CaptureOperation;
use Magento\Sales\Model\Order\Payment\Operations\ProcessInvoiceOperation;
use Magento\Sales\Model\Order\Payment\State\CommandInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface as TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class CaptureOperationTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var TransactionManagerInterface|MockObject
     */
    private $transactionManager;

    /**
     * @var EventManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var BuilderInterface|MockObject
     */
    private $transactionBuilder;

    /**
     * @var CommandInterface|MockObject
     */
    private $stateCommand;

    /**
     * @var ProcessInvoiceOperation|MockObject
     */
    private $processInvoiceOperation;

    /**
     * @var CaptureOperation
     */
    private $model;

    protected function setUp(): void
    {
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->eventManager = $this->createMock(EventManagerInterface::class);
        $this->transactionBuilder = $this->createMock(BuilderInterface::class);
        $this->stateCommand = $this->createMock(CommandInterface::class);
        $this->processInvoiceOperation = $this->createMock(ProcessInvoiceOperation::class);

        $this->model = new CaptureOperation(
            $this->stateCommand,
            $this->transactionBuilder,
            $this->transactionManager,
            $this->eventManager,
            $this->processInvoiceOperation
        );
    }

    /**
     * Tests a case when capture operation is called with null invoice.
     *
     * @throws LocalizedException
     */
    public function testCaptureWithoutInvoice()
    {
        $invoice = $this->createMock(Invoice::class);
        $invoice->expects($this->once())
            ->method('register');
        $invoice->expects($this->once())
            ->method('capture');

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

        /** @var MethodInterface $paymentMethod */
        $paymentMethod = $this->createMock(MethodInterface::class);
        $paymentMethod->method('canCapture')
            ->willReturn(true);

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
            $this->model->capture($orderPayment, null)
        );
    }

    /**
     * Tests a case when capture operation is called with null invoice.
     *
     * @throws LocalizedException
     */
    public function testCaptureWithInvoice()
    {
        /** @var Invoice|MockObject  $invoice */
        $invoice = $this->createMock(Invoice::class);

        /** @var Payment|MockObject  $orderPayment | */
        $orderPayment = $this->createMock(Payment::class);

        $this->processInvoiceOperation->expects($this->once())
            ->method('execute')
            ->willReturn($orderPayment);

        $this->assertInstanceOf(
            Payment::class,
            $this->model->capture($orderPayment, $invoice)
        );
    }
}
