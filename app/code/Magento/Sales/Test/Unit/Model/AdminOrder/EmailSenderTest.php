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
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection as InvoiceCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests to sent order emails
 */
class EmailSenderTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var Manager|MockObject
     */
    private $messageManagerMock;

    /**
     * @var OrderSender|MockObject
     */
    private $orderSenderMock;

    /**
     * @var InvoiceSender|MockObject
     */
    private $invoiceSenderMock;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->messageManagerMock = $this->createMock(Manager::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->orderSenderMock = $this->createMock(OrderSender::class);
        $this->invoiceSenderMock = $this->createMock(InvoiceSender::class);

        $this->emailSender = new EmailSender(
            $this->messageManagerMock,
            $this->loggerMock,
            $this->orderSenderMock,
            $this->invoiceSenderMock
        );
    }

    /**
     * Test to send order emails
     */
    public function testSendSuccess()
    {
        $invoicePaid = $this->createMock(Invoice::class);
        $invoicePaid->method('getState')->willReturn(Invoice::STATE_PAID);
        $invoiceOpen = $this->createMock(Invoice::class);
        $invoiceOpen->method('getState')->willReturn(Invoice::STATE_OPEN);
        $order = $this->createOrderMock([$invoiceOpen, $invoicePaid]);

        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->with($order);
        $this->invoiceSenderMock->expects($this->once())
            ->method('send')
            ->with($invoicePaid);

        $this->assertTrue($this->emailSender->send($order));
    }

    /**
     * testSendFailure
     */
    public function testSendFailure()
    {
        $orderMock = $this->createOrderMock();
        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->willThrowException(new MailException(__('test message')));
        $this->messageManagerMock->expects($this->once())
            ->method('addWarningMessage');
        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->assertFalse($this->emailSender->send($orderMock));
    }

    /**
     * Create order mock
     *
     * @param array $invoiceCollection
     * @return MockObject|Order
     */
    private function createOrderMock(array $invoiceCollection = []): MockObject
    {
        $collection = $this->createMock(InvoiceCollection::class);
        $collection->method('getItems')->willReturn($invoiceCollection);
        $order = $this->createMock(Order::class);
        $order->method('getInvoiceCollection')->willReturn($collection);

        return $order;
    }
}
