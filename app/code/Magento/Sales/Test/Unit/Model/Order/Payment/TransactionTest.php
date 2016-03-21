<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Sales\Model\Order\Payment\Transaction */
    protected $transaction;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var  \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('\Magento\Framework\Model\Context')
            ->setMethods(['getEventDispatcher'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder('\Magento\Framework\Event\ManagerInterface')
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->transaction = $this->objectManagerHelper->getObject(
            '\Magento\Sales\Model\Order\Payment\Transaction',
            [
                'context' => $this->contextMock
            ]
        );
    }

    public function testGetHtmlTxnId()
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch');

        $this->transaction->setData('html_txn_id', 'test');


        $this->assertEquals('test', $this->transaction->getHtmlTxnId());
    }

    public function testGetHtmlTxnIdIsNull()
    {
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch');

        $this->transaction->setData('txn_id', 'test');


        $this->assertEquals('test', $this->transaction->getHtmlTxnId());
        $this->assertEquals(null, $this->transaction->getData('html_txn_id'));
    }
}
