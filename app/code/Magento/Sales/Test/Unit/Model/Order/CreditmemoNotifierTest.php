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
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoNotifier;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CreditmemoNotifierTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var CreditmemoNotifier
     */
    protected $notifier;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemo;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $creditmemoSenderMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->historyCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->creditmemo = $this->createPartialMock(
            Creditmemo::class,
            ['getEmailSent']
        );
        $this->creditmemoSenderMock = $this->createPartialMock(
            CreditmemoSender::class,
            ['send']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->notifier = new CreditmemoNotifier(
            $this->historyCollectionFactory,
            $this->loggerMock,
            $this->creditmemoSenderMock
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
        $historyItem->method('setIsCustomerNotified')
            ->with(1);
        $historyCollection->expects($this->once())
            ->method('getUnnotifiedForInstance')
            ->with($this->creditmemo)
            ->willReturn($historyItem);
        $this->creditmemo->expects($this->once())
            ->method('getEmailSent')
            ->willReturn(true);
        $this->historyCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($historyCollection);

        $this->creditmemoSenderMock->expects($this->once())
            ->method('send')
            ->with($this->creditmemo);

        $this->assertTrue($this->notifier->notify($this->creditmemo));
    }

    /**
     * Test case when email has not been sent
     *
     * @return void
     */
    public function testNotifyFail(): void
    {
        $this->creditmemo->expects($this->once())
            ->method('getEmailSent')
            ->willReturn(false);
        $this->assertFalse($this->notifier->notify($this->creditmemo));
    }

    /**
     * Test case when Mail Exception has been thrown
     *
     * @return void
     */
    public function testNotifyException(): void
    {
        $exception = new MailException(__('Email has not been sent'));
        $this->creditmemoSenderMock->expects($this->once())
            ->method('send')
            ->with($this->creditmemo)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->assertFalse($this->notifier->notify($this->creditmemo));
    }
}
