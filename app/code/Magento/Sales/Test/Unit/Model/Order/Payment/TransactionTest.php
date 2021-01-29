<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Payment;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class TransactionTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Sales\Model\Order\Payment\Transaction */
    protected $transaction;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $contextMock;

    /** @var  \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $eventManagerMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->setMethods(['getEventDispatcher'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->transaction = $this->objectManagerHelper->getObject(
            \Magento\Sales\Model\Order\Payment\Transaction::class,
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
        $this->assertNull($this->transaction->getData('html_txn_id'));
    }
}
