<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\StatusResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class StatusResolverTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @param \Closure $order
     * @param string $expectedReturn
     *
     */
    #[DataProvider('statesDataProvider')]
    public function testGetOrderStatusByState($order, $expectedReturn)
    {
        $order = $order($this);

        $actualReturn = (new StatusResolver())->getOrderStatusByState($order, 'new');

        self::assertEquals($expectedReturn, $actualReturn);
    }

    /**
     * @return array
     */
    public static function statesDataProvider()
    {
        return [
            [
                static fn (self $testCase) => $testCase->getOrder('pending', ['pending' => 'pending']),
                'pending'
            ],
            [
                static fn (self $testCase) => $testCase->getOrder('processing', ['pending' => 'pending']),
                'processing'
            ],
        ];
    }

    /**
     * @param string $newOrderStatus
     * @param array $stateStatuses
     * @return OrderInterface|MockObject
     */
    public function getOrder($newOrderStatus, $stateStatuses)
    {
        $order = $this->createPartialMockWithReflection(Order::class, ['getConfig', 'getPayment']);
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
        $payment = $this->createPartialMockWithReflection(Payment::class, ['getMethodInstance']);
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
            ->getMock();
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
        $config = $this->createMock(Config::class);
        $config->method('getStateStatuses')
            ->willReturn($stateStatuses);
        $config->method('getStateDefaultStatus')
            ->willReturn('processing');

        return $config;
    }
}
