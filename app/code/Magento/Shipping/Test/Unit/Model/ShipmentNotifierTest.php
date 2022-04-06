<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model;

use Magento\Framework\Exception\MailException;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentNotifierTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ShipmentNotifier
     */
    protected $notifier;

    /**
     * @var Order|MockObject
     */
    protected $shipment;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var ObjectManager|MockObject
     */
    protected $shipmentSenderMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->historyCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->shipment = $this->createPartialMock(
            Shipment::class,
            ['__wakeUp', 'getEmailSent']
        );
        $this->shipmentSenderMock = $this->createPartialMock(
            ShipmentSender::class,
            ['send']
        );
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->notifier = new ShipmentNotifier(
            $this->historyCollectionFactory,
            $this->loggerMock,
            $this->shipmentSenderMock
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
            ['setIsCustomerNotified', 'save', '__wakeUp']
        );
        $historyItem
            ->method('setIsCustomerNotified')
            ->with(1);
        $historyCollection->expects($this->once())
            ->method('getUnnotifiedForInstance')
            ->with($this->shipment)
            ->willReturn($historyItem);
        $this->shipment->expects($this->once())
            ->method('getEmailSent')
            ->willReturn(true);
        $this->historyCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($historyCollection);

        $this->shipmentSenderMock->expects($this->once())
            ->method('send')
            ->with($this->shipment);

        $this->assertTrue($this->notifier->notify($this->shipment));
    }

    /**
     * Test case when email has not been sent
     *
     * @return void
     */
    public function testNotifyFail(): void
    {
        $this->shipment->expects($this->once())
            ->method('getEmailSent')
            ->willReturn(false);
        $this->assertFalse($this->notifier->notify($this->shipment));
    }

    /**
     * Test case when Mail Exception has been thrown
     *
     * @return void
     */
    public function testNotifyException(): void
    {
        $exception = new MailException(__('Email has not been sent'));
        $this->shipmentSenderMock->expects($this->once())
            ->method('send')
            ->with($this->shipment)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->assertFalse($this->notifier->notify($this->shipment));
    }
}
