<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Exception\MailException;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\InvoiceNotifier;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InvoiceNotifierTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var InvoiceNotifier
     */
    protected $notifier;

    /**
     * @var Invoice|MockObject
     */
    protected $invoice;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $invoiceSenderMock;

    protected function setUp(): void
    {
        $this->historyCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->invoice = $this->createPartialMock(
            Invoice::class,
            ['getEmailSent']
        );
        $this->invoiceSenderMock = $this->createPartialMock(
            InvoiceSender::class,
            ['send']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
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
        $historyCollection = $this->getMockBuilder(Collection::class)
            ->addMethods(['setIsCustomerNotified'])
            ->onlyMethods(['getUnnotifiedForInstance', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $historyItem = $this->createPartialMock(
            History::class,
            ['setIsCustomerNotified', 'save']
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
            ->with($this->invoice);

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
            ->with($this->invoice)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->assertFalse($this->notifier->notify($this->invoice));
    }
}
