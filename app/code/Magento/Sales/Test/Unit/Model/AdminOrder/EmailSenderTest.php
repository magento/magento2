<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\AdminOrder;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Message\Manager;
use Magento\Sales\Model\AdminOrder\EmailSender;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class EmailSenderTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $orderMock;

    /**
     * @var MockObject
     */
    protected $loggerMock;

    /**
     * @var MockObject
     */
    protected $messageManagerMock;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var OrderSender
     */
    protected $orderSenderMock;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->orderSenderMock = $this->createMock(OrderSender::class);

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
            ->willThrowException(new MailException(__('test message')));
        $this->messageManagerMock->expects($this->once())
            ->method('addWarningMessage');
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->emailSender->send($this->orderMock));
    }
}
