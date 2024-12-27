<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Payment;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /** @var  Transaction */
    protected $transaction;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var  ManagerInterface|MockObject */
    protected $eventManagerMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getEventDispatcher'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->onlyMethods(['dispatch'])
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->once())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->transaction = $this->objectManagerHelper->getObject(
            Transaction::class,
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
