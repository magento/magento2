<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\SalesOrderBeforeSaveObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SalesOrderBeforeSaveObserverTest extends TestCase
{
    /** @var SalesOrderBeforeSaveObserver */
    protected $salesOrderBeforeSaveObserver;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Observer|MockObject */
    protected $observerMock;

    /** @var Event|MockObject */
    protected $eventMock;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->salesOrderBeforeSaveObserver = $this->objectManagerHelper->getObject(
            SalesOrderBeforeSaveObserver::class,
            []
        );

        $this->observerMock = $this->getMockBuilder(
            Observer::class
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
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment', 'canUnhold'], $neverInvokedMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
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
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment', 'canUnhold', 'isCanceled'], $neverInvokedMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
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
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment', 'isCanceled', 'canUnhold', 'getState'], $neverInvokedMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
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
            $this->returnValue(Order::STATE_CLOSED)
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
     */
    public function testDoesNothingWhenNoPaymentIsAvailable()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Please provide payment for the order.');
        $this->_prepareEventMockWithMethods(['getOrder']);

        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->setMethods(
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
            Event::class
        )->disableOriginalConstructor()->setMethods($methodsList)->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->will($this->returnValue($this->eventMock));
    }

    /**
     * Prepares Order with MethodInterface
     *
     * @param string $methodCode
     * @param array $orderMethods
     * @return MockObject
     */
    private function _getPreparedOrderMethod($methodCode, $orderMethods = [])
    {
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment'], $orderMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodInstance));
        $methodInstance->expects($this->once())->method('getCode')->will($this->returnValue($methodCode));
        return $order;
    }

    /**
     * Sets never expectation for order methods listed in $method
     *
     * @param MockObject $order
     * @param array $methods
     */
    private function _prepareNeverInvokedOrderMethods(MockObject $order, $methods = [])
    {
        foreach ($methods as $method) {
            $order->expects($this->never())->method($method);
        }
    }
}
