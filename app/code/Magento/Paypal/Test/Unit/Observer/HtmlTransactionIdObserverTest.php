<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;

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

        $this->paypalDataMock = $this->createMock(Data::class);
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
        $observerMock = $this->createPartialMockWithReflection(Observer::class, ['getDataObject']);
        $transactionMock = $this->createMock(Transaction::class);
        $orderMock = $this->createMock(Order::class);
        $paymentMock = $this->createMock(Payment::class);
        $methodInstanceMock = $this->createMock(MethodInterface::class);

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
