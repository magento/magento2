<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
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
        )->disableOriginalConstructor()
            ->onlyMethods(['getEvent'])->getMock();
    }

    public function testSalesOrderBeforeSaveMethodNotFree()
    {
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['canUnhold', 'isCanceled', 'getState'];
        $order = $this->_getPreparedOrderMethod(
            'not_free',
            $neverInvokedMethods,
            ['hasForcedCanCreditMemo']
        );
        $this->_prepareNeverInvokedOrderMethods($order, array_merge($neverInvokedMethods,['hasForcedCanCreditMemo']));
        $this->eventMock->expects($this->once())->method('getOrder')->willReturn(
            $order
        );

        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveCantUnhold()
    {
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['isCanceled', 'getState'];
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                array_merge(['__wakeup', 'getPayment', 'canUnhold'], $neverInvokedMethods)
            )
            ->addMethods(['hasForcedCanCreditMemo'])->getMock();
        $paymentMock = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->getMock();
        $order->method('getPayment')->willReturn($paymentMock);
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);
        $methodInstance->expects($this->once())->method('getCode')->willReturn('free');
        $this->_prepareNeverInvokedOrderMethods($order, array_merge($neverInvokedMethods,['hasForcedCanCreditMemo']));
        $this->eventMock->expects($this->once())->method('getOrder')->willReturn(
            $order
        );
        $order->expects($this->once())->method('canUnhold')->willReturn(true);
        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveIsCanceled()
    {
        // check first canceled state
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['getState'];
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                array_merge(['__wakeup', 'getPayment', 'canUnhold', 'isCanceled'], $neverInvokedMethods)
            )
            ->addMethods(['hasForcedCanCreditMemo'])->getMock();
        $paymentMock = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->getMock();
        $order->method('getPayment')->willReturn($paymentMock);
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);
        $methodInstance->expects($this->once())->method('getCode')->willReturn('free');
        $this->_prepareNeverInvokedOrderMethods($order, array_merge($neverInvokedMethods,['hasForcedCanCreditMemo']));
        $this->eventMock->expects($this->once())->method('getOrder')->willReturn(
            $order
        );
        $order->expects($this->once())->method('canUnhold')->willReturn(false);

        $order->expects($this->once())->method('isCanceled')->willReturn(true);

        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveIsClosed()
    {
        // check closed state at second
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['hasForcedCanCreditMemo'];
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                array_merge(['__wakeup', 'getPayment', 'isCanceled', 'canUnhold', 'getState'])
            )
            ->addMethods($neverInvokedMethods)->getMock();
        $paymentMock = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->getMock();
        $order->method('getPayment')->willReturn($paymentMock);
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);
        $methodInstance->expects($this->once())->method('getCode')->willReturn('free');
        $this->_prepareNeverInvokedOrderMethods($order, $neverInvokedMethods);
        $this->eventMock->expects($this->once())->method('getOrder')->willReturn(
            $order
        );
        $order->expects($this->once())->method('canUnhold')->willReturn(false);

        $order->expects($this->once())->method('isCanceled')->willReturn(false);
        $order->expects($this->once())->method('getState')->willReturn(
            Order::STATE_CLOSED
        );
        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveSetForced()
    {
        // check closed state at second
        $this->_prepareEventMockWithMethods(['getOrder']);
        $order = $this->_getPreparedOrderMethod(
            'free',
            ['canUnhold', 'isCanceled', 'getState'],
            ['setForcedCanCreditmemo', 'hasForcedCanCreditmemo']
        );
        $this->eventMock->expects($this->once())->method('getOrder')->willReturn(
            $order
        );
        $order->expects($this->once())->method('canUnhold')->willReturn(false);

        $order->expects($this->once())->method('isCanceled')->willReturn(false);
        $order->expects($this->once())->method('getState')->willReturn(
            'not_closed_state'
        );
        $order->expects($this->once())->method('hasForcedCanCreditmemo')->willReturn(false);
        $order->expects($this->once())->method('setForcedCanCreditmemo')->willReturn(true);

        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    /**
     * The method should check that the payment is available, as this is not always the case.
     */
    public function testDoesNothingWhenNoPaymentIsAvailable()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please provide payment for the order.');
        $this->_prepareEventMockWithMethods(['getOrder']);

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                array_merge(['__wakeup', 'getPayment'])
            )->getMock();

        $this->eventMock->expects($this->once())->method('getOrder')->willReturn(
            $order
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
        )->disableOriginalConstructor()
            ->addMethods($methodsList)->getMock();
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($this->eventMock);
    }

    /**
     * Prepares Order with MethodInterface
     *
     * @param string $methodCode
     * @param array $orderMethods
     * @return MockObject
     */
    private function _getPreparedOrderMethod($methodCode, $orderMethods = [], $addOrderMethods = [])
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->addMethods($addOrderMethods)
            ->onlyMethods(
                array_merge(['__wakeup', 'getPayment'], $orderMethods)
            )->getMock();
        $paymentMock = $this->getMockBuilder(
            Payment::class
        )->disableOriginalConstructor()->getMock();
        $order->method('getPayment')->willReturn($paymentMock);
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
        )->getMockForAbstractClass();
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);
        $methodInstance->expects($this->once())->method('getCode')->willReturn($methodCode);
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
