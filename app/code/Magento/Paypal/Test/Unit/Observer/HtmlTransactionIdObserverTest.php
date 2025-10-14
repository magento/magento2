<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Helper\Data;
use Magento\Paypal\Observer\HtmlTransactionIdObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HtmlTransactionIdObserverTest extends TestCase
{
    /**
     * @var HtmlTransactionIdObserver
     */
    protected $_model;

    /**
     * @var Observer
     */
    protected $_observer;

    /**
     * @var DataObject
     */
    protected $_event;

    /**
     * @var Data|MockObject
     */
    protected $paypalDataMock;

    protected function setUp(): void
    {
        $this->_event = new DataObject();

        $this->_observer = new Observer();
        $this->_observer->setEvent($this->_event);

        $this->paypalDataMock = $this->createPartialMock(Data::class, ['getHtmlTransactionId']);
        $objectManagerHelper = new ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            HtmlTransactionIdObserver::class,
            [
                'paypalData' => $this->paypalDataMock,
            ]
        );
    }

    public function testObserveHtmlTransactionId()
    {
        $observerMock = $this->getMockBuilder(Observer::class)
            ->addMethods(['getDataObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $transactionMock = $this->getMockBuilder(Transaction::class)
            ->onlyMethods(['getOrder', 'getTxnId', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock = $this->getMockBuilder(Order::class)
            ->onlyMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->onlyMethods(['getMethodInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodInstanceMock = $this->getMockBuilder(MethodInterface::class)
            ->onlyMethods(['getCode'])
            ->getMockForAbstractClass();

        $observerMock->expects($this->once())
            ->method('getDataObject')
            ->willReturn($transactionMock);
        $transactionMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);
        $methodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn("'test'");
        $transactionMock->expects($this->once())
            ->method('getTxnId')
            ->willReturn("'test'");

        $this->paypalDataMock->expects($this->once())
            ->method('getHtmlTransactionId')
            ->willReturn('test');

        $transactionMock->expects($this->once())
            ->method('setData')->with('html_txn_id', 'test');

        $this->_model->execute($observerMock);
    }
}
