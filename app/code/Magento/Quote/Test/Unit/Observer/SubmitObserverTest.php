<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Observer\SubmitObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Psr\Log\LoggerInterface;

/**
 * Class SubmitObserverTest
 */
class SubmitObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SubmitObserver
     */
    private $model;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var OrderSender|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderSenderMock;

    /**
     * @var Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $observerMock;

    /**
     * @var Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    protected function setUp()
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->paymentMock = $this->createMock(Payment::class);
        $this->orderSenderMock = $this->createMock(OrderSender::class);
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getOrder'])
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
