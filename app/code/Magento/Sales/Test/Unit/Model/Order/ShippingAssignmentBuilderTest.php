<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Api\Data\ShippingAssignmentInterface;
use Magento\Sales\Api\Data\ShippingAssignmentInterfaceFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\ShippingAssignmentBuilder;
use Magento\Sales\Model\Order\ShippingBuilderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingAssignmentBuilderTest extends TestCase
{
    /**
     * @var ShippingAssignmentBuilder
     */
    private $shippingAssignmentBuilder;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactory;

    /**
     * @var ShippingAssignmentInterfaceFactory|Mockobject
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingBuilderFactory|MockObject
     */
    private $shippingBuilderFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAssignmentFactory = $this->getMockBuilder(ShippingAssignmentInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingBuilderFactory = $this->getMockBuilder(ShippingBuilderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAssignmentBuilder = new ShippingAssignmentBuilder(
            $this->orderFactory,
            $this->shippingAssignmentFactory,
            $this->shippingBuilderFactory
        );
    }

    /**
     * Test for case when order is provided instead of order_id
     *
     * @return void
     */
    public function testCreateWithOrder() : void
    {
        $order = $this->getMockBuilder(OrderInterface::class)
            ->getMockForAbstractClass();
        $this->shippingAssignmentBuilder->setOrder($order);
        $order->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);
        $this->orderFactory->expects($this->never())
            ->method('create');

        $this->shippingAssignmentBuilder->create();
    }
}
