<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Payment\Model\Observer */
    protected $observer;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Sales\Model\Order\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderConfigMock;

    /** @var \Magento\Payment\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $paymentConfigMock;

    /** @var \Magento\Core\Model\Resource\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $coreResourceConfigMock;

    /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject */
    protected $observerMock;

    /** @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventMock;

    const ORDER_STATUS = 'status';

    const METHOD_CODE = 'method_code';

    protected function setUp()
    {
        $this->orderConfigMock = $this->getMock('Magento\Sales\Model\Order\Config', [], [], '', false);
        $this->paymentConfigMock = $this->getMock('Magento\Payment\Model\Config', [], [], '', false);
        $this->coreResourceConfigMock = $this->getMock('Magento\Core\Model\Resource\Config', [], [], '', false);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->observer = $this->objectManagerHelper->getObject(
            'Magento\Payment\Model\Observer',
            [
                'salesOrderConfig' => $this->orderConfigMock,
                'paymentConfig' => $this->paymentConfigMock,
                'resourceConfig' => $this->coreResourceConfigMock
            ]
        );

        $this->observerMock = $this->getMockBuilder(
            'Magento\Framework\Event\Observer'
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

        $this->observer->salesOrderBeforeSave($this->observerMock);
    }

    public function testSalesOrderBeforeSaveCantUnhold()
    {
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['isCanceled', 'getState', 'hasForcedCanCreditMemo'];
        $order = $this->_getPreparedOrderMethod('free', ['canUnhold'] + $neverInvokedMethods);
        $this->_prepareNeverInvokedOrderMethods($order, $neverInvokedMethods);
        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );
        $order->expects($this->once())->method('canUnhold')->will($this->returnValue(true));
        $this->observer->salesOrderBeforeSave($this->observerMock);
    }

    public function testSalesOrderBeforeSaveIsCanceled()
    {
        // check first canceled state
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['getState', 'hasForcedCanCreditMemo'];
        $order = $this->_getPreparedOrderMethod('free', ['canUnhold', 'isCanceled'] + $neverInvokedMethods);
        $this->_prepareNeverInvokedOrderMethods($order, $neverInvokedMethods);
        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );
        $order->expects($this->once())->method('canUnhold')->will($this->returnValue(false));

        $order->expects($this->once())->method('isCanceled')->will($this->returnValue(true));

        $this->observer->salesOrderBeforeSave($this->observerMock);
    }

    public function testSalesOrderBeforeSaveIsClosed()
    {
        // check closed state at second
        $this->_prepareEventMockWithMethods(['getOrder']);
        $neverInvokedMethods = ['hasForcedCanCreditMemo'];
        $order = $this->_getPreparedOrderMethod('free', ['canUnhold', 'isCanceled', 'getState'] + $neverInvokedMethods);
        $this->_prepareNeverInvokedOrderMethods($order, $neverInvokedMethods);
        $this->eventMock->expects($this->once())->method('getOrder')->will(
            $this->returnValue($order)
        );
        $order->expects($this->once())->method('canUnhold')->will($this->returnValue(false));

        $order->expects($this->once())->method('isCanceled')->will($this->returnValue(false));
        $order->expects($this->once())->method('getState')->will(
            $this->returnValue(\Magento\Sales\Model\Order::STATE_CLOSED)
        );
        $this->observer->salesOrderBeforeSave($this->observerMock);
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

        $this->observer->salesOrderBeforeSave($this->observerMock);
    }

    public function testUpdateOrderStatusForPaymentMethodsNotNewState()
    {
        $this->_prepareEventMockWithMethods(['getState']);
        $this->eventMock->expects($this->once())->method('getState')->will($this->returnValue('NotNewState'));
        $this->observer->updateOrderStatusForPaymentMethods($this->observerMock);
    }

    public function testUpdateOrderStatusForPaymentMethodsNewState()
    {
        $this->_prepareEventMockWithMethods(['getState', 'getStatus']);
        $this->eventMock->expects($this->once())->method('getState')->will(
            $this->returnValue(\Magento\Sales\Model\Order::STATE_NEW)
        );
        $this->eventMock->expects($this->once())->method('getStatus')->will(
            $this->returnValue(self::ORDER_STATUS)
        );

        $defaultStatus = 'defaultStatus';
        $this->orderConfigMock->expects($this->once())->method('getStateDefaultStatus')->with(
            \Magento\Sales\Model\Order::STATE_NEW
        )->will($this->returnValue($defaultStatus));

        $this->paymentConfigMock->expects($this->once())->method('getActiveMethods')->will(
            $this->returnValue($this->_getPreparedActiveMethods())
        );

        $this->coreResourceConfigMock->expects($this->once())->method('saveConfig')->with(
            'payment/' . self::METHOD_CODE . '/order_status',
            $defaultStatus,
            'default',
            0
        );
        $this->observer->updateOrderStatusForPaymentMethods($this->observerMock);
    }

    /**
     * Prepares EventMock with set of methods
     *
     * @param $methodsList
     */
    private function _prepareEventMockWithMethods($methodsList)
    {
        $this->eventMock = $this->getMockBuilder(
            'Magento\Framework\Event'
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
        $order = $this->getMockBuilder('Magento\Sales\Model\Order')->disableOriginalConstructor()->setMethods(
            array_merge(['__wakeup', 'getPayment'], $orderMethods)
        )->getMock();
        $paymentMock = $this->getMockBuilder(
            'Magento\Sales\Model\Order\Payment'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $order->expects($this->once())->method('getPayment')->will($this->returnValue($paymentMock));
        $methodInstance = $this->getMockBuilder(
            'Magento\Payment\Model\MethodInterface'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMock->expects($this->once())->method('getMethodInstance')->will($this->returnValue($methodInstance));
        $methodInstance->expects($this->once())->method('getCode')->will($this->returnValue($methodCode));
        return $order;
    }

    /**
     * Return mocked data of getActiveMethods
     *
     * @return array
     */
    private function _getPreparedActiveMethods()
    {
        $mockedMethods = ['getCode', 'getFormBlockType', 'getTitle', 'getConfigData'];
        $method1 = $this->getMockBuilder(
            'Magento\Payment\Model\MethodInterface'
        )->disableOriginalConstructor()->setMethods($mockedMethods)->getMock();
        $method1->expects($this->once())->method('getConfigData')->with('order_status')->will(
            $this->returnValue(self::ORDER_STATUS)
        );
        $method1->expects($this->once())->method('getCode')->will(
            $this->returnValue(self::METHOD_CODE)
        );

        $method2 = $this->getMockBuilder(
            'Magento\Payment\Model\MethodInterface'
        )->disableOriginalConstructor()->setMethods($mockedMethods)->getMock();
        $method2->expects($this->once())->method('getConfigData')->with('order_status')->will(
            $this->returnValue('not_a_status')
        );

        return [$method1, $method2];
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
