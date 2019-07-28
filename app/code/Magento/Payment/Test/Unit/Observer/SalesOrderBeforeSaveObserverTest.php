<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SalesOrderBeforeSaveObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Observer\SalesOrderBeforeSaveObserver */
    protected $salesOrderBeforeSaveObserver;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject */
    protected $observerMock;

    /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->salesOrderBeforeSaveObserver = $this->objectManagerHelper->getObject(
            \Magento\Payment\Observer\SalesOrderBeforeSaveObserver::class,
            []
        );

        $this->observerMock = $this->getMockBuilder(
            \Magento\Framework\Event\Observer::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
    }

    public function testSalesOrderBeforeSaveMethodNotFree()
    {
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['canUnhold', 'isCanceled', 'getState', 'hasForcedCanCreditMemo'];
        $order = $this->_getPreparedOrderMethod(
            'not_free',
            $neverInvokedMethods
        );
        $this->_prepareNeverInvokedOrderMethods($order, $neverInvokedMethods);
        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );

        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveCantUnhold()
    {
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['isCanceled', 'getState', 'hasForcedCanCreditMemo'];
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment', 'canUnhold'], $neverInvokedMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodInstance));
        $methodInstance->expects($this->once())->method('getCode')->will($this->returnValue('free'));
        $this->_prepareNeverInvokedOrderMethods($order, $neverInvokedMethods);
        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );
        $order->expects($this->once())->method('canUnhold')->will($this->returnValue(true));
        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveIsCanceled()
    {
        // check first canceled state
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['getState', 'hasForcedCanCreditMemo'];
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment', 'canUnhold', 'isCanceled'], $neverInvokedMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodInstance));
        $methodInstance->expects($this->once())->method('getCode')->will($this->returnValue('free'));
        $this->_prepareNeverInvokedOrderMethods($order, $neverInvokedMethods);
        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );
        $order->expects($this->once())->method('canUnhold')->will($this->returnValue(false));

        $order->expects($this->once())->method('isCanceled')->will($this->returnValue(true));

        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveIsClosed()
    {
        // check closed state at second
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['hasForcedCanCreditMemo'];
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment', 'isCanceled', 'canUnhold', 'getState'], $neverInvokedMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodInstance));
        $methodInstance->expects($this->once())->method('getCode')->will($this->returnValue('free'));
        $this->_prepareNeverInvokedOrderMethods($order, $neverInvokedMethods);
        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );
        $order->expects($this->once())->method('canUnhold')->will($this->returnValue(false));

        $order->expects($this->once())->method('isCanceled')->will($this->returnValue(false));
        $order->expects($this->once())->method('getState')->will(
            $this->returnValue(\Magento\Sales\Model\Order::STATE_CLOSED)
        );
        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveSetForced()
    {
        // check closed state at second
        $this->_prepareEventMockWithMethods(['getOrder']);
        $order = $this->_getPreparedOrderMethod(
            'free',
            ['canUnhold', 'isCanceled', 'getState', 'setForcedCanCreditmemo', 'hasForcedCanCreditmemo']
        );
        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );
        $order->expects($this->once())->method('canUnhold')->will($this->returnValue(false));

        $order->expects($this->once())->method('isCanceled')->will($this->returnValue(false));
        $order->expects($this->once())->method('getState')->will(
            $this->returnValue('not_closed_state')
        );
        $order->expects($this->once())->method('hasForcedCanCreditmemo')->will($this->returnValue(false));
        $order->expects($this->once())->method('setForcedCanCreditmemo')->will($this->returnValue(true));

        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    /**
     * The method should check that the payment is available, as this is not always the case.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please provide payment for the order.
     */
    public function testDoesNothingWhenNoPaymentIsAvailable()
    {
        $this->_prepareEventMockWithMethods(['getOrder']);

        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment'])
        )->getMock();

        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );

        $order->expects($this->exactly(1))->method('getPayment')->willReturn(null);

        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    /**
     * Prepares EventMock with set of methods
     *
     * @param $methodsList
     */
    private function _prepareEventMockWithMethods($methodsList)
    {
        $this->eventMock = $this->getMockBuilder(
            \Magento\Framework\Event::class
        )->disableOriginalConstructor()->setMethods($methodsList)->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->eventMock));
    }

    /**
     * Prepares Order with MethodInterface
     *
     * @param string $methodCode
     * @param array $orderMethods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function _getPreparedOrderMethod($methodCode, $orderMethods = [])
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment'], $orderMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            \Magento\Sales\Model\Order\Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodInstance));
        $methodInstance->expects($this->once())->method('getCode')->will($this->returnValue($methodCode));
        return $order;
    }

    /**
     * Sets never expectation for order methods listed in $method
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $order
     * @param array $methods
     */
    private function _prepareNeverInvokedOrderMethods(\PHPUnit_Framework_MockObject_MockObject $order, $methods = [])
    {
        foreach ($methods as $method) {
            $order->expects($this->never())->method($method);
        }
    }
}
