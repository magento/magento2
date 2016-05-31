<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use \Magento\Sales\Model\Order\CreditmemoNotifier;

use Magento\Framework\Exception\MailException;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

/**
 * Class CreditmemoNotifierTest
 */
class CreditmemoNotifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var CreditmemoNotifier
     */
    protected $notifier;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemo;

    /**
     * @var \Magento\Framework\ObjectManagerInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoSenderMock;

    protected function setUp()
    {
        $this->historyCollectionFactory = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->creditmemo = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            ['__wakeUp', 'getEmailSent'],
            [],
            '',
            false
        );
        $this->creditmemoSenderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender\CreditmemoSender',
            ['send'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->notifier = new CreditmemoNotifier(
            $this->historyCollectionFactory,
            $this->loggerMock,
            $this->creditmemoSenderMock
        );
    }

    /**
     * Test case for successful email sending
     */
    public function testNotifySuccess()
    {
        $historyCollection = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Status\History\Collection',
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
            ->with($this->creditmemo)
            ->will($this->returnValue($historyItem));
        $this->creditmemo->expects($this->once())
            ->method('getEmailSent')
            ->will($this->returnValue(true));
        $this->historyCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($historyCollection));

        $this->creditmemoSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->creditmemo));

        $this->assertTrue($this->notifier->notify($this->creditmemo));
    }

    /**
     * Test case when email has not been sent
     */
    public function testNotifyFail()
    {
        $this->creditmemo->expects($this->once())
            ->method('getEmailSent')
            ->will($this->returnValue(false));
        $this->assertFalse($this->notifier->notify($this->creditmemo));
    }

    /**
     * Test case when Mail Exception has been thrown
     */
    public function testNotifyException()
    {
        $exception = new MailException(__('Email has not been sent'));
        $this->creditmemoSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->creditmemo))
            ->will($this->throwException($exception));
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->equalTo($exception));
        $this->assertFalse($this->notifier->notify($this->creditmemo));
    }
}
