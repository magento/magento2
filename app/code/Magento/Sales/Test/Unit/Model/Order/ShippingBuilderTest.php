<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShippingInterfaceFactory;
use Magento\Sales\Api\Data\TotalInterfaceFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\ShippingBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingBuilderTest extends TestCase
{
    /**
     * @var ShippingBuilder
     */
    private $shippingBuilder;

    /**
     * @var OrderFactory|MockObject
     */
    private $orderFactory;

    /**
     * @var ShippingInterfaceFactory|Mockobject
     */
    private $shippingFactory;

    /**
     * @var TotalInterfaceFactory|MockObject
     */
    private $totalFactory;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingFactory = $this->getMockBuilder(ShippingInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->totalFactory = $this->getMockBuilder(TotalInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingBuilder = new ShippingBuilder($this->orderFactory, $this->shippingFactory, $this->totalFactory);
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
        $this->shippingBuilder->setOrder($order);
        $order->expects($this->any())
            ->method('getEntityId')
            ->willReturn(1);
        $this->orderFactory->expects($this->never())
            ->method('create');

        $this->shippingBuilder->create();
    }
}
