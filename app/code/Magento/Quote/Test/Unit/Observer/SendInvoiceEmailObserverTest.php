<?php
/**
 * Copyright 2021 Adobe
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
use Magento\Sales\Model\Order\Email\Container\InvoiceIdentity;
use Magento\Quote\Observer\SendInvoiceEmailObserver;

/**
 * Test for sending invoice email during order place on frontend
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SendInvoiceEmailObserverTest extends TestCase
{
    /**
     * @var SendInvoiceEmailObserver
     */
    private $model;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var InvoiceSender|MockObject
     */
    private $invoiceSenderMock;

    /**
     * @var InvoiceIdentity|MockObject
     */
    private $invoiceIdentityMock;

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
        $this->invoiceSenderMock = $this->createMock(InvoiceSender::class);
        $this->invoiceIdentityMock = $this->getMockBuilder(InvoiceIdentity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isEnabled'])
            ->getMock();
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuote', 'getOrder'])
            ->getMock();
        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $eventMock->expects($this->any())->method('getOrder')->willReturn($this->orderMock);
        $this->quoteMock->expects($this->any())->method('getPayment')->willReturn($this->paymentMock);
        $this->model = new SendInvoiceEmailObserver(
            $this->loggerMock,
            $this->invoiceSenderMock,
            $this->invoiceIdentityMock
        );
    }

    /**
     * Tests successful email sending.
     */
    public function testSendEmail()
    {
        $this->invoiceIdentityMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->paymentMock->method('getOrderPlaceRedirectUrl')->willReturn('');

        $invoice = $this->createMock(Invoice::class);
        $invoiceCollection = $this->createMock(Collection::class);
        $invoiceCollection->method('getItems')
            ->willReturn([$invoice]);
        $this->orderMock->method('getInvoiceCollection')
            ->willReturn($invoiceCollection);
        $this->quoteMock
            ->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->orderMock->method('getCanSendNewEmailFlag')->willReturn(true);
        $this->invoiceSenderMock->expects($this->once())
            ->method('send')
            ->with($invoice)
            ->willReturn(true);
        $this->loggerMock->expects($this->never())
            ->method('critical');

        $this->model->execute($this->observerMock);
    }

    /**
     * Tests email sending disabled by configuration.
     */
    public function testSendEmailDisabled()
    {
        $this->invoiceIdentityMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->paymentMock
            ->expects($this->never())
            ->method('getOrderPlaceRedirectUrl');
        $this->orderMock
            ->expects($this->never())
            ->method('getInvoiceCollection');

        $this->quoteMock
            ->expects($this->never())
            ->method('getPayment');

        $this->orderMock
            ->expects($this->never())
            ->method('getCanSendNewEmailFlag');
        $this->loggerMock->expects($this->never())
            ->method('critical');

        $this->model->execute($this->observerMock);
    }

    /**
     * Tests failing email sending.
     */
    public function testFailToSendEmail()
    {
        $this->invoiceIdentityMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->paymentMock->expects($this->once())->method('getOrderPlaceRedirectUrl')->willReturn('');

        $invoice = $this->createMock(Invoice::class);
        $invoiceCollection = $this->createMock(Collection::class);
        $invoiceCollection->method('getItems')
            ->willReturn([$invoice]);
        $this->orderMock->method('getInvoiceCollection')
            ->willReturn($invoiceCollection);

        $this->orderMock->expects($this->once())->method('getCanSendNewEmailFlag')->willReturn(true);
        $this->invoiceSenderMock->expects($this->once())->method('send')->willThrowException(
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
        $this->invoiceIdentityMock
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->paymentMock->expects($this->once())->method('getOrderPlaceRedirectUrl')->willReturn(false);
        $this->orderMock->expects($this->once())->method('getCanSendNewEmailFlag');
        $this->invoiceSenderMock->expects($this->never())->method('send');
        $this->loggerMock->expects($this->never())->method('critical');
        $this->model->execute($this->observerMock);
    }
}
