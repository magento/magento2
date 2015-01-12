<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

use Magento\Framework\Mail\Exception;
use Magento\Sales\Model\Resource\Order\Status\History\CollectionFactory;

/**
 * Class OrderNotifierTest
 */
class OrderNotifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var \Magento\Sales\Model\OrderNotifier
     */
    protected $notifier;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;

    /**
     * @var \Magento\Framework\ObjectManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderSenderMock;

    public function setUp()
    {
        $this->historyCollectionFactory = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\History\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['__wakeUp', 'getEmailSent'],
            [],
            '',
            false
        );
        $this->orderSenderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender\OrderSender',
            ['send'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->notifier = new OrderNotifier(
            $this->historyCollectionFactory,
            $this->loggerMock,
            $this->orderSenderMock
        );
    }

    /**
     * Test case for successful email sending
     */
    public function testNotifySuccess()
    {
        $historyCollection = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\History\Collection',
            ['getUnnotifiedForInstance', 'save', 'setIsCustomerNotified'],
            [],
            '',
            false
        );
        $historyItem = $this->getMock(
            'Magento\Sales\Model\Order\Status\History',
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
            ->with($this->order)
            ->will($this->returnValue($historyItem));
        $this->order->expects($this->once())
            ->method('getEmailSent')
            ->will($this->returnValue(true));
        $this->historyCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($historyCollection));

        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->order));

        $this->assertTrue($this->notifier->notify($this->order));
    }

    /**
     * Test case when email has not been sent
     */
    public function testNotifyFail()
    {
        $this->order->expects($this->once())
            ->method('getEmailSent')
            ->will($this->returnValue(false));
        $this->assertFalse($this->notifier->notify($this->order));
    }

    /**
     * Test case when Mail Exception has been thrown
     */
    public function testNotifyException()
    {
        $exception = new Exception('Email has not been sent');
        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->order))
            ->will($this->throwException($exception));
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->equalTo($exception));
        $this->assertFalse($this->notifier->notify($this->order));
    }
}
