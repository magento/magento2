<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;

/**
 * Test for \Magento\Quote\Observer\SubmitObserver class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubmitObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Observer\SubmitObserver
     */
    protected $model;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderSenderMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Quote\Model\Quote\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var InvoiceSender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceSenderMock;

    /**
     * @var Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceMock;

    /**
     * @var InvoiceCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceCollectionMock;

    protected function setUp()
    {
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->paymentMock = $this->createMock(\Magento\Quote\Model\Quote\Payment::class);
        $this->orderSenderMock =
            $this->createMock(\Magento\Sales\Model\Order\Email\Sender\OrderSender::class);
        $eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getOrder'])
            ->getMock();
        $this->observerMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $eventMock->expects($this->once())->method('getOrder')->willReturn($this->orderMock);
        $this->quoteMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->invoiceSenderMock = $this->createMock(InvoiceSender::class);
        $this->invoiceMock = $this->createMock(Invoice::class);
        $this->invoiceCollectionMock = $this->createMock(InvoiceCollection::class);
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(
            \Magento\Quote\Observer\SubmitObserver::class,
            [
                'logger' => $this->loggerMock,
                'orderSender' => $this->orderSenderMock,
                'invoiceSender' => $this->invoiceSenderMock,
            ]
        );
    }

    public function testSendEmail()
    {
        $this->paymentMock->expects($this->once())->method('getOrderPlaceRedirectUrl')->willReturn('');
        $this->orderMock->expects($this->once())->method('getCanSendNewEmailFlag')->willReturn(true);
        $this->orderSenderMock->expects($this->once())->method('send')->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('getInvoiceCollection')
            ->willReturn($this->invoiceCollectionMock);
        $this->invoiceCollectionMock->expects($this->once())->method('getItems')->willReturn([]);
        $this->loggerMock->expects($this->never())->method('critical');
        $this->model->execute($this->observerMock);
    }

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

    public function testSendEmailWhenRedirectUrlExists()
    {
        $this->paymentMock->expects($this->once())->method('getOrderPlaceRedirectUrl')->willReturn(false);
        $this->orderMock->expects($this->once())->method('getCanSendNewEmailFlag');
        $this->orderSenderMock->expects($this->never())->method('send');
        $this->loggerMock->expects($this->never())->method('critical');
        $this->model->execute($this->observerMock);
    }

    public function testSendEmailWithPaidInvoice()
    {
        $this->prepareDataForSendInvoice();
        $this->invoiceMock->expects($this->once())->method('getState')->willReturn(Invoice::STATE_PAID);
        $this->invoiceSenderMock->expects($this->once())
            ->method('send')
            ->with($this->invoiceMock)
            ->willReturn(true);
        $this->loggerMock->expects($this->never())->method('critical');

        $this->model->execute($this->observerMock);
    }

    public function testSendEmailWithNotPaidInvoice()
    {
        $this->prepareDataForSendInvoice();
        $this->invoiceMock->expects($this->once())->method('getState')->willReturn(Invoice::STATE_OPEN);
        $this->invoiceSenderMock->expects($this->never())->method('send');
        $this->loggerMock->expects($this->never())->method('critical');

        $this->model->execute($this->observerMock);
    }

    private function prepareDataForSendInvoice()
    {
        $this->paymentMock->expects($this->once())->method('getOrderPlaceRedirectUrl')->willReturn('');
        $this->orderMock->expects($this->once())->method('getCanSendNewEmailFlag')->willReturn(true);
        $this->orderSenderMock->expects($this->once())->method('send')->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('getInvoiceCollection')
            ->willReturn($this->invoiceCollectionMock);
        $this->invoiceCollectionMock->expects($this->once())->method('getItems')->willReturn([$this->invoiceMock]);
    }
}
