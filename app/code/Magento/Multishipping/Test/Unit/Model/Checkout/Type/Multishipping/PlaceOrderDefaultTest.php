<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Model\Checkout\Type\Multishipping;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault.
 */
class PlaceOrderDefaultTest extends TestCase
{
    /**
     * @var OrderManagementInterface|MockObject
     */
    private $orderManagement;

    /**
     * @var PlaceOrderDefault
     */
    private $placeOrderDefault;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->orderManagement = $this->getMockForAbstractClass(OrderManagementInterface::class);

        $this->placeOrderDefault = new PlaceOrderDefault($this->orderManagement);
    }

    public function testPlace()
    {
        $incrementId = '000000001';

        $order = $this->getMockForAbstractClass(OrderInterface::class);
        $order->method('getIncrementId')->willReturn($incrementId);
        $orderList = [$order];

        $this->orderManagement->expects($this->once())
            ->method('place')
            ->with($order)
            ->willReturn($order);
        $errors = $this->placeOrderDefault->place($orderList);

        $this->assertEmpty($errors);
    }

    public function testPlaceWithErrors()
    {
        $incrementId = '000000001';

        $order = $this->getMockForAbstractClass(OrderInterface::class);
        $order->method('getIncrementId')->willReturn($incrementId);
        $orderList = [$order];

        $exception = new \Exception('error');
        $this->orderManagement->method('place')->willThrowException($exception);
        $errors = $this->placeOrderDefault->place($orderList);

        $this->assertEquals(
            [$incrementId => $exception],
            $errors
        );
    }
}
