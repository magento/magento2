<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Observer\SubmitObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for sending order email during order place on frontend
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubmitObserverTest extends TestCase
{
    /**
     * @var SubmitObserver
     */
    private $model;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var OrderSender|MockObject
     */
    private $orderSenderMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var Payment|MockObject
     */
    private $paymentMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->orderSenderMock = $this->createMock(OrderSender::class);
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuote', 'getOrder'])
            ->getMock();
        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $eventMock->expects($this->once())->method('getOrder')->willReturn($this->orderMock);
        $this->quoteMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->model = new SubmitObserver(
            $this->loggerMock,
            $this->orderSenderMock
        );
    }

    /**
     * Tests successful email sending.
     */
    public function testSendEmail()
    {
        $this->paymentMock->method('getOrderPlaceRedirectUrl')->willReturn('');
        $invoice = $this->createMock(Invoice::class);
        $invoiceCollection = $this->createMock(Collection::class);
        $invoiceCollection->method('getItems')
            ->willReturn([$invoice]);

        $this->orderMock->method('getInvoiceCollection')
            ->willReturn($invoiceCollection);
        $this->orderMock->method('getCanSendNewEmailFlag')->willReturn(true);
        $this->orderSenderMock->expects($this->once())
            ->method('send')->willReturn(true);
        $this->loggerMock->expects($this->never())
            ->method('critical');

        $this->model->execute($this->observerMock);
    }

    /**
     * Tests failing email sending.
     */
    public function testFailToSendEmail()
    {
        $this->paymentMock->expects($this->once())->method('getOrderPlaceRedirectUrl')->willReturn('');
        $this->orderMock->expects($this->once())->method('getCanSendNewEmailFlag')->willReturn(true);
        $this->orderSenderMock->expects($this->once())->method('send')->willThrowException(
            new \Exception('Some email sending Error')
        );
        $this->loggerMock->expects($this->once())->method('critical');
        $this->model->execute($this->observerMock);
    }

    /**
     * Tests send email when redirect.
     */
    public function testSendEmailWhenRedirectUrlExists()
    {
        $this->paymentMock->expects($this->once())->method('getOrderPlaceRedirectUrl')->willReturn(false);
        $this->orderMock->expects($this->once())->method('getCanSendNewEmailFlag');
        $this->orderSenderMock->expects($this->never())->method('send');
        $this->loggerMock->expects($this->never())->method('critical');
        $this->model->execute($this->observerMock);
    }
}
