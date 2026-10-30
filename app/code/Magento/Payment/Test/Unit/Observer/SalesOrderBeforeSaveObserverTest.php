<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\SalesOrderBeforeSaveObserver;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SalesOrderBeforeSaveObserverTest extends TestCase
{
    use MockCreationTrait;

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

        $this->observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
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
        $this->_prepareNeverInvokedOrderMethods($order, array_merge($neverInvokedMethods, ['hasForcedCanCreditMemo']));
        $this->eventMock->expects($this->once())->method('getOrder')->willReturn(
            $order
        );

        $this->salesOrderBeforeSaveObserver->execute($this->observerMock);
    }

    public function testSalesOrderBeforeSaveCantUnhold()
    {
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['isCanceled', 'getState'];
        $order = $this->createPartialMockWithReflection(
            Order::class,
            array_merge(['__wakeup', 'getPayment', 'canUnhold', 'hasForcedCanCreditMemo'], $neverInvokedMethods)
        );
        $paymentMock = $this->createMock(Payment::class);
        $order->method('getPayment')->willReturn($paymentMock);
        $methodInstance = $this->createMock(MethodInterface::class);
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);
        $methodInstance->expects($this->once())->method('getCode')->willReturn('free');
        $this->_prepareNeverInvokedOrderMethods($order, array_merge($neverInvokedMethods, ['hasForcedCanCreditMemo']));
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
        $order = $this->createPartialMockWithReflection(
            Order::class,
            array_merge(['__wakeup', 'getPayment', 'canUnhold', 'isCanceled', 'hasForcedCanCreditMemo'], $neverInvokedMethods)
        );
        $paymentMock = $this->createMock(Payment::class);
        $order->method('getPayment')->willReturn($paymentMock);
        $methodInstance = $this->createMock(MethodInterface::class);
        $paymentMock->expects($this->once())->method('getMethodInstance')->willReturn($methodInstance);
        $methodInstance->expects($this->once())->method('getCode')->willReturn('free');
        $this->_prepareNeverInvokedOrderMethods($order, array_merge($neverInvokedMethods, ['hasForcedCanCreditMemo']));
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
        $order = $this->createPartialMockWithReflection(
            Order::class,
            array_merge(['__wakeup', 'getPayment', 'isCanceled', 'canUnhold', 'getState'], $neverInvokedMethods)
        );
        $paymentMock = $this->createMock(Payment::class);
        $order->method('getPayment')->willReturn($paymentMock);
        $methodInstance = $this->createMock(MethodInterface::class);
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

        $order = $this->createPartialMock(Order::class, ['__wakeup', 'getPayment']);

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
        $this->eventMock = $this->createPartialMockWithReflection(Event::class, $methodsList);
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
        $order = $this->createPartialMockWithReflection(
            Order::class,
            array_merge(['__wakeup', 'getPayment'], $orderMethods, $addOrderMethods)
        );
        $paymentMock = $this->createMock(Payment::class);
        $order->method('getPayment')->willReturn($paymentMock);
        $methodInstance = $this->createMock(MethodInterface::class);
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
