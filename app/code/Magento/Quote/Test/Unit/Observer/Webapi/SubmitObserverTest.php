<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Observer\Webapi;

class SubmitObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Observer\Webapi\SubmitObserver
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

    protected function setUp()
    {
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->orderMock = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $this->paymentMock = $this->getMock('Magento\Quote\Model\Quote\Payment', [], [], '', false);
        $this->orderSenderMock =
            $this->getMock('Magento\Sales\Model\Order\Email\Sender\OrderSender', [], [], '', false);
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getOrder'])
            ->getMock();
        $this->observerMock = $this->getMock('Magento\Framework\Event\Observer', ['getEvent'], [], '', false);
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($this->quoteMock);
        $eventMock->expects($this->once())->method('getOrder')->willReturn($this->orderMock);
        $this->quoteMock->expects($this->once())->method('getPayment')->willReturn($this->paymentMock);
        $this->model = new \Magento\Quote\Observer\Webapi\SubmitObserver(
            $this->loggerMock,
            $this->orderSenderMock
        );
    }

    public function testSendEmail()
    {
        $this->paymentMock->expects($this->once())->method('getOrderPlaceRedirectUrl')->willReturn('');
        $this->orderMock->expects($this->once())->method('getCanSendNewEmailFlag')->willReturn(true);
        $this->orderSenderMock->expects($this->once())->method('send')->willReturn(true);
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
}
