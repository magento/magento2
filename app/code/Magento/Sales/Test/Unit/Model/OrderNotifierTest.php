<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\Exception\MailException;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\OrderNotifier;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OrderNotifierTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var OrderNotifier
     */
    protected $notifier;

    /**
     * @var Order|MockObject
     */
    protected $order;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $orderSenderMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->historyCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->order = $this->createPartialMock(Order::class, ['getEmailSent']);
        $this->orderSenderMock = $this->createPartialMock(
            OrderSender::class,
            ['send']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->notifier = new OrderNotifier(
            $this->historyCollectionFactory,
            $this->loggerMock,
            $this->orderSenderMock
        );
    }

    /**
     * Test case for successful email sending
     *
     * @return void
     */
    public function testNotifySuccess(): void
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
        $historyItem
            ->method('setIsCustomerNotified')
            ->with(1);
        $historyCollection->expects($this->once())
            ->method('getUnnotifiedForInstance')
            ->with($this->order)
            ->willReturn($historyItem);
        $this->order->expects($this->once())
            ->method('getEmailSent')
            ->willReturn(true);
        $this->historyCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($historyCollection);

        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->with($this->order);

        $this->assertTrue($this->notifier->notify($this->order));
    }

    /**
     * Test case when email has not been sent
     */
    public function testNotifyFail(): void
    {
        $this->order->expects($this->once())
            ->method('getEmailSent')
            ->willReturn(false);
        $this->assertFalse($this->notifier->notify($this->order));
    }

    /**
     * Test case when Mail Exception has been thrown
     */
    public function testNotifyException(): void
    {
        $exception = new MailException(__('Email has not been sent'));
        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->with($this->order)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->assertFalse($this->notifier->notify($this->order));
    }
}
