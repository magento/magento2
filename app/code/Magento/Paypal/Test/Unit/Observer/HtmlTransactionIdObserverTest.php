<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Observer;

/**
 * Class HtmlTransactionIdObserverTest
 */
class HtmlTransactionIdObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Observer\HtmlTransactionIdObserver
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_event;

    /**
     * @var \Magento\Paypal\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalDataMock;

    protected function setUp()
    {
        $this->_event = new \Magento\Framework\DataObject();

        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->paypalDataMock = $this->getMock(
            \Magento\Paypal\Helper\Data::class,
            ['getHtmlTransactionId'],
            [],
            '',
            false
        );
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            \Magento\Paypal\Observer\HtmlTransactionIdObserver::class,
            [
                'paypalData' => $this->paypalDataMock,
            ]
        );
    }

    public function testObserveHtmlTransactionId()
    {
        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->setMethods(['getDataObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $transactionMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)
            ->setMethods(['getOrder', 'getTxnId', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->setMethods(['getMethodInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->setMethods(['getCode'])
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
