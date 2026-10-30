<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Model\Order\Shipment\Item as ShipmentItem;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment\OrderRegistrar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class OrderRegistrarTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var OrderRegistrar
     */
    private $model;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var ShipmentInterface|MockObject
     */
    private $shipmentMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->shipmentMock = $this->createMock(ShipmentInterface::class);

        $this->model = new OrderRegistrar();
    }

    public function testRegister()
    {
        $item1 = $this->getShipmentItemMock();
        $item1->expects($this->once())->method('getQty')->willReturn(0);
        $item1->expects($this->never())->method('register');
        $item1->expects($this->never())->method('getOrderItem');

        $item2 = $this->getShipmentItemMock();
        $item2->expects($this->atLeastOnce())->method('getQty')->willReturn(0.5);
        $item2->expects($this->once())->method('register');

        $orderItemMock = $this->createMock(Item::class);
        $orderItemMock->expects($this->once())->method('isDummy')->with(true)->willReturn(false);
        $item2->expects($this->once())->method('getOrderItem')->willReturn($orderItemMock);

        $items = [$item1, $item2];
        $this->shipmentMock->expects($this->once())->method('getItems')->willReturn($items);
        $this->assertEquals(
            $this->orderMock,
            $this->model->register($this->orderMock, $this->shipmentMock)
        );
    }

    /**
     * @return MockObject
     */
    private function getShipmentItemMock()
    {
        return $this->createPartialMockWithReflection(
            ShipmentItem::class,
            ['register', 'getOrderItem', 'getQty']
        );
    }
}
