<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\StatusResolver;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class StatusResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param OrderInterface|MockObject $order
     * @param string $expectedReturn
     *
     * @dataProvider statesDataProvider
     */
    public function testGetOrderStatusByState($order, $expectedReturn)
    {
        $actualReturn = (new StatusResolver())->getOrderStatusByState($order, 'new');

        self::assertEquals($expectedReturn, $actualReturn);
    }

    public function statesDataProvider()
    {
        return [
            [
                $this->getOrder('pending', ['pending' => 'pending']),
                'pending'
            ],
            [
                $this->getOrder('processing', ['pending' => 'pending']),
                'processing'
            ],
        ];
    }

    /**
     * @param string $newOrderStatus
     * @param array $stateStatuses
     * @return OrderInterface|MockObject
     */
    private function getOrder($newOrderStatus, $stateStatuses)
    {
        $order = $this->getMockBuilder(OrderInterface::class)
            ->setMethods(['getConfig'])
            ->getMockForAbstractClass();
        $order->method('getPayment')
            ->willReturn($this->getPayment($newOrderStatus));
        $order->method('getConfig')
            ->willReturn($this->getConfig($stateStatuses));

        return $order;
    }

    /**
     * @param string $newOrderStatus
     * @return MockObject
     */
    private function getPayment($newOrderStatus)
    {
        $payment = $this->getMockBuilder(OrderPaymentInterface::class)
            ->setMethods(['getMethodInstance'])
            ->getMockForAbstractClass();
        $payment->method('getMethodInstance')
            ->willReturn($this->getMethodInstance($newOrderStatus));

        return $payment;
    }

    /**
     * @param string $newOrderStatus
     * @return MethodInterface|MockObject
     */
    private function getMethodInstance($newOrderStatus)
    {
        $methodInstance = $this->getMockBuilder(MethodInterface::class)
            ->getMockForAbstractClass();
        $methodInstance->method('getConfigData')
            ->with('order_status')
            ->willReturn($newOrderStatus);

        return $methodInstance;
    }

    /**
     * @param array $stateStatuses
     * @return Config|MockObject
     */
    private function getConfig($stateStatuses)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $config->method('getStateStatuses')
            ->willReturn($stateStatuses);
        $config->method('getStateDefaultStatus')
            ->willReturn('processing');

        return $config;
    }
}
