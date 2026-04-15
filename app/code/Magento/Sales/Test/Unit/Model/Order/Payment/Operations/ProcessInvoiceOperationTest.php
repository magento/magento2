<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment\Operations;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Operations\ProcessInvoiceOperation;
use Magento\Sales\Model\Order\Payment\State\CommandInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface as TransactionManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessInvoiceOperationTest extends TestCase
{

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
    protected $model;

    protected function setUp(): void
    {
        $this->transactionManager = $this->createMock(TransactionManagerInterface::class);
        $this->eventManager = $this->createMock(EventManagerInterface::class);
        $this->transactionBuilder = $this->createMock(BuilderInterface::class);
        $this->stateCommand = $this->createMock(CommandInterface::class);

        $this->model = new ProcessInvoiceOperation(
            $this->stateCommand,
            $this->transactionBuilder,
            $this->transactionManager,
            $this->eventManager
        );
    }

    public function testExecute()
    {
        $amountToCapture = $baseGrandTotal = 10;
        $operationMethod = 'sale';
        $storeId = 1;
        $transactionId = '1ASD3456';

        /** @var Order|MockObject $order */
        $order = $this->createMock(Order::class);
        $order->method('getStoreId')
            ->willReturn($storeId);

        /** @var Adapter|MockObject $paymentMethod */
        $paymentMethod = $this->createMock(Adapter::class);

        $orderPayment = $this->createMock(Payment::class);
        $orderPayment->method('formatAmount')
            ->with($baseGrandTotal)
            ->willReturnArgument(0);
        $orderPayment->method('getOrder')
            ->willReturn($order);
        $orderPayment->method('getMethodInstance')
            ->willReturn($paymentMethod);
        $orderPayment->expects($this->once())
            ->method('setTransactionId')
            ->with($transactionId);
        $authTransaction = $this->createMock(Transaction::class);
        $orderPayment->expects($this->once())
            ->method('getAuthorizationTransaction')
            ->willReturn($authTransaction);
        $orderPayment->expects($this->once())
            ->method('getIsTransactionPending')
            ->willReturn(true);
        $orderPayment->expects($this->once())
            ->method('getTransactionAdditionalInfo')
            ->willReturn([]);

        $this->transactionManager->expects($this->once())
            ->method('generateTransactionId')
            ->with($orderPayment, Transaction::TYPE_CAPTURE, $authTransaction)
            ->willReturn($transactionId);

        $paymentMethod->method('setStore')
            ->with($storeId);
        $paymentMethod->expects($this->once())
            ->method($operationMethod)
            ->with($orderPayment, $amountToCapture);

        $this->transactionBuilder->expects($this->once())
            ->method('setPayment')
            ->with($orderPayment)
            ->willReturnSelf();

        $invoice = $this->createMock(Invoice::class);
        $invoice->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);

        $this->model->execute($orderPayment, $invoice, $operationMethod);
    }
}
