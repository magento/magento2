<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\AdminOrder;

use \Magento\Sales\Model\AdminOrder\EmailSender;

class EmailSenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
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

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->orderSenderMock = $this->createMock(\Magento\Sales\Model\Order\Email\Sender\OrderSender::class);

        $this->emailSender = new EmailSender($this->messageManagerMock, $this->loggerMock, $this->orderSenderMock);
    }

    /**
     * testSendSuccess
     */
    public function testSendSuccess()
    {
        $this->orderSenderMock->expects($this->once())
            ->method('send');
        $this->assertTrue($this->emailSender->send($this->orderMock));
    }

    /**
     * testSendFailure
     */
    public function testSendFailure()
    {
        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->willThrowException(new \Magento\Framework\Exception\MailException(__('test message')));
        $this->messageManagerMock->expects($this->once())
            ->method('addWarningMessage');
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->emailSender->send($this->orderMock));
    }
}
