<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Model\Resource\Order\Status\History\CollectionFactory;
use Magento\Framework\Mail\Exception;

/**
 * Class InvoiceNotifierTest
 */
class InvoiceNotifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CollectionFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceNotifier
     */
    protected $notifier;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoice;

    /**
     * @var \Magento\Framework\ObjectManager |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceSenderMock;

    public function setUp()
    {
        $this->historyCollectionFactory = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Status\History\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->invoice = $this->getMock(
            'Magento\Sales\Model\Order\Invoice',
            ['__wakeUp', 'getEmailSent'],
            [],
            '',
            false
        );
        $this->invoiceSenderMock = $this->getMock(
            'Magento\Sales\Model\Order\Email\Sender\InvoiceSender',
            ['send'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(
            'Magento\Framework\Logger',
            ['logException'],
            [],
            '',
            false
        );
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
            ->with($this->invoice)
            ->will($this->returnValue($historyItem));
        $this->invoice->expects($this->once())
            ->method('getEmailSent')
            ->will($this->returnValue(true));
        $this->historyCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($historyCollection));

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
            ->will($this->returnValue(false));
        $this->assertFalse($this->notifier->notify($this->invoice));
    }

    /**
     * Test case when Mail Exception has been thrown
     */
    public function testNotifyException()
    {
        $exception = new Exception('Email has not been sent');
        $this->invoiceSenderMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo($this->invoice))
            ->will($this->throwException($exception));
        $this->loggerMock->expects($this->once())
            ->method('logException')
            ->with($this->equalTo($exception));
        $this->assertFalse($this->notifier->notify($this->invoice));
    }
}
