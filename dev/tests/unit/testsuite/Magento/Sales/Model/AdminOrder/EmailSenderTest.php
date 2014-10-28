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
namespace Magento\Sales\Model\AdminOrder;

class EmailSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSenderMock;

    protected function setUp()
    {
        $this->messageManagerMock = $this->getMock(
            '\Magento\Framework\Message\Manager',
            [],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(
            '\Magento\Framework\Logger',
            [],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            '\Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        );
        $this->orderSenderMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Sender\OrderSender',
            [],
            [],
            '',
            false
        );

        $this->emailSender = new EmailSender($this->messageManagerMock, $this->loggerMock, $this->orderSenderMock);
    }

    public function testSendSuccess()
    {
        $this->orderSenderMock->expects($this->once())
            ->method('send');
        $this->assertTrue($this->emailSender->send($this->orderMock));
    }

    public function testSendFailure()
    {
        $this->orderSenderMock->expects($this->once())
            ->method('send')
            ->will($this->throwException(new \Magento\Framework\Mail\Exception('test message')));
        $this->messageManagerMock->expects($this->once())
            ->method('addWarning');
        $this->loggerMock->expects($this->once())
            ->method('logException');

        $this->assertFalse($this->emailSender->send($this->orderMock));
    }
}
