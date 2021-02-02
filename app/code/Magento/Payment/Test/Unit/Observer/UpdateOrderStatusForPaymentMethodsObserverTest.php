<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class UpdateOrderStatusForPaymentMethodsObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Payment\Observer\updateOrderStatusForPaymentMethodsObserver */
    protected $updateOrderStatusForPaymentMethodsObserver;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Sales\Model\Order\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $orderConfigMock;

    /** @var \Magento\Payment\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $paymentConfigMock;

    /** @var \Magento\Config\Model\ResourceModel\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $coreResourceConfigMock;

    /** @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject */
    protected $observerMock;

    /** @var \Magento\Framework\Event|\PHPUnit\Framework\MockObject\MockObject */
    protected $eventMock;

    const ORDER_STATUS = 'status';

    const METHOD_CODE = 'method_code';

    protected function setUp(): void
    {
        $this->orderConfigMock = $this->createMock(\Magento\Sales\Model\Order\Config::class);
        $this->paymentConfigMock = $this->createMock(\Magento\Payment\Model\Config::class);
        $this->coreResourceConfigMock = $this->createMock(\Magento\Config\Model\ResourceModel\Config::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->updateOrderStatusForPaymentMethodsObserver = $this->objectManagerHelper->getObject(
            \Magento\Payment\Observer\UpdateOrderStatusForPaymentMethodsObserver::class,
            [
                'salesOrderConfig' => $this->orderConfigMock,
                'paymentConfig' => $this->paymentConfigMock,
                'resourceConfig' => $this->coreResourceConfigMock
            ]
        );

        $this->observerMock = $this->getMockBuilder(
            \Magento\Framework\Event\Observer::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
    }

    public function testUpdateOrderStatusForPaymentMethodsNotNewState()
    {
        $this->_prepareEventMockWithMethods(['getState']);
        $this->eventMock->expects($this->once())->method('getState')->willReturn('NotNewState');
        $this->updateOrderStatusForPaymentMethodsObserver->execute($this->observerMock);
    }

    public function testUpdateOrderStatusForPaymentMethodsNewState()
    {
        $this->_prepareEventMockWithMethods(['getState', 'getStatus']);
        $this->eventMock->expects($this->once())->method('getState')->willReturn(
            \Magento\Sales\Model\Order::STATE_NEW
        );
        $this->eventMock->expects($this->once())->method('getStatus')->willReturn(
            self::ORDER_STATUS
        );

        $defaultStatus = 'defaultStatus';
        $this->orderConfigMock->expects($this->once())->method('getStateDefaultStatus')->with(
            \Magento\Sales\Model\Order::STATE_NEW
        )->willReturn($defaultStatus);

        $this->paymentConfigMock->expects($this->once())->method('getActiveMethods')->willReturn(
            $this->_getPreparedActiveMethods()
        );

        $this->coreResourceConfigMock->expects($this->once())->method('saveConfig')->with(
            'payment/' . self::METHOD_CODE . '/order_status',
            $defaultStatus,
            'default',
            0
        );
        $this->updateOrderStatusForPaymentMethodsObserver->execute($this->observerMock);
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
        $this->observerMock->expects($this->any())->method('getEvent')->willReturn($this->eventMock);
    }

    /**
     * Return mocked data of getActiveMethods
     *
     * @return array
     */
    private function _getPreparedActiveMethods()
    {
        $method1 = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->getMockForAbstractClass();
        $method1->expects($this->once())->method('getConfigData')->with('order_status')->willReturn(
            self::ORDER_STATUS
        );
        $method1->expects($this->once())->method('getCode')->willReturn(
            self::METHOD_CODE
        );

        $method2 = $this->getMockBuilder(
            \Magento\Payment\Model\MethodInterface::class
        )->getMockForAbstractClass();
        $method2->expects($this->once())->method('getConfigData')->with('order_status')->willReturn(
            'not_a_status'
        );

        return [$method1, $method2];
    }
}
