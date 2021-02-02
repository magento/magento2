<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Exception\MailException;
use Magento\Sales\Model\Order\InvoiceNotifier;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * Class InvoiceNotifierTest
 */
class InvoiceNotifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CollectionFactory |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceNotifier
     */
    protected $notifier;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $invoice;

    /**
     * @var \Magento\Framework\ObjectManagerInterface |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $invoiceSenderMock;

    protected function setUp(): void
    {
        $this->historyCollectionFactory = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory::class,
            ['create']
        );
        $this->invoice = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice::class,
            ['__wakeUp', 'getEmailSent']
        );
        $this->invoiceSenderMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\Sender\InvoiceSender::class,
            ['send']
        );
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->notifier = new InvoiceNotifier(
            $this->historyCollectionFactory,
            $this->loggerMock,
            $this->invoiceSenderMock
        );
    }

    /**
     * Test case for successful email sending
     */
    public function testNotifySuccess()
    {
        $historyCollection = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\History\Collection::class,
            ['getUnnotifiedForInstance', 'save', 'setIsCustomerNotified']
        );
        $historyItem = $this->createPartialMock(
            \Magento\Sales\Model\Order\Status\History::class,
            ['setIsCustomerNotified', 'save', '__wakeUp']
        );
        $historyItem->expects($this->at(0))
            ->method('setIsCustomerNotified')
            ->with(1);
        $historyItem->expects($this->at(1))
            ->method('save');
        $historyCollection->expects($this->once())
            ->method('getUnnotifiedForInstance')
            ->with($this->invoice)
            ->willReturn($historyItem);
        $this->invoice->expects($this->once())
            ->method('getEmailSent')
            ->willReturn(true);
        $this->historyCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($historyCollection);

        $this->invoiceSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->invoice));

        $this->assertTrue($this->notifier->notify($this->invoice));
    }

    /**
     * Test case when email has not been sent
     */
    public function testNotifyFail()
    {
        $this->invoice->expects($this->once())
            ->method('getEmailSent')
            ->willReturn(false);
        $this->assertFalse($this->notifier->notify($this->invoice));
    }

    /**
     * Test case when Mail Exception has been thrown
     */
    public function testNotifyException()
    {
        $exception = new MailException(__('Email has not been sent'));
        $this->invoiceSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->invoice))
            ->will($this->throwException($exception));
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->equalTo($exception));
        $this->assertFalse($this->notifier->notify($this->invoice));
    }
}
