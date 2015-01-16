<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\AdminOrder;

class EmailSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSenderMock;

    protected function setUp()
    {
        $this->messageManagerMock = $this->getMock(
            '\Magento\Framework\Message\Manager',
            [],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(
            '\Psr\Log\LoggerInterface',
            [],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            '\Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );
        $this->orderSenderMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Sender\OrderSender',
            [],
            [],
            '',
            false
        );

        $this->emailSender = new EmailSender($this->messageManagerMock, $this->loggerMock, $this->orderSenderMock);
    }

    public function testSendSuccess()
    {
        $this->orderSenderMock->expects($this->once())
            ->method('send');
        $this->assertTrue($this->emailSender->send($this->orderMock));
    }

    public function testSendFailure()
    {
        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->will($this->throwException(new \Magento\Framework\Mail\Exception('test message')));
        $this->messageManagerMock->expects($this->once())
            ->method('addWarning');
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->emailSender->send($this->orderMock));
    }
}
