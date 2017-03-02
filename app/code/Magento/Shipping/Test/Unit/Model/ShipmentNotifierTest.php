<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Unit\Model;

use \Magento\Shipping\Model\ShipmentNotifier;

use Magento\Framework\Exception\MailException;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * Class ShipmentNotifierTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentNotifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ShipmentNotifier
     */
    protected $notifier;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipment;

    /**
     * @var \Magento\Framework\ObjectManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentSenderMock;

    protected function setUp()
    {
        $this->historyCollectionFactory = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->shipment = $this->getMock(
            \Magento\Sales\Model\Order\Shipment::class,
            ['__wakeUp', 'getEmailSent'],
            [],
            '',
            false
        );
        $this->shipmentSenderMock = $this->getMock(
            \Magento\Sales\Model\Order\Email\Sender\ShipmentSender::class,
            ['send'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->notifier = new ShipmentNotifier(
            $this->historyCollectionFactory,
            $this->loggerMock,
            $this->shipmentSenderMock
        );
    }

    /**
     * Test case for successful email sending
     */
    public function testNotifySuccess()
    {
        $historyCollection = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\History\Collection::class,
            ['getUnnotifiedForInstance', 'save', 'setIsCustomerNotified'],
            [],
            '',
            false
        );
        $historyItem = $this->getMock(
            \Magento\Sales\Model\Order\Status\History::class,
            ['setIsCustomerNotified', 'save', '__wakeUp'],
            [],
            '',
            false
        );
        $historyItem->expects($this->at(0))
            ->method('setIsCustomerNotified')
            ->with(1);
        $historyItem->expects($this->at(1))
            ->method('save');
        $historyCollection->expects($this->once())
            ->method('getUnnotifiedForInstance')
            ->with($this->shipment)
            ->will($this->returnValue($historyItem));
        $this->shipment->expects($this->once())
            ->method('getEmailSent')
            ->will($this->returnValue(true));
        $this->historyCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($historyCollection));

        $this->shipmentSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->shipment));

        $this->assertTrue($this->notifier->notify($this->shipment));
    }

    /**
     * Test case when email has not been sent
     */
    public function testNotifyFail()
    {
        $this->shipment->expects($this->once())
            ->method('getEmailSent')
            ->will($this->returnValue(false));
        $this->assertFalse($this->notifier->notify($this->shipment));
    }

    /**
     * Test case when Mail Exception has been thrown
     */
    public function testNotifyException()
    {
        $exception = new MailException(__('Email has not been sent'));
        $this->shipmentSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->shipment))
            ->will($this->throwException($exception));
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->equalTo($exception));
        $this->assertFalse($this->notifier->notify($this->shipment));
    }
}
