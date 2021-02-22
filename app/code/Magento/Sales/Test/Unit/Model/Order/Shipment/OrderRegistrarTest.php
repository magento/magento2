<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Shipment;

class OrderRegistrarTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\OrderRegistrar
     */
    private $model;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $shipmentMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = new \Magento\Sales\Model\Order\Shipment\OrderRegistrar();
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

        $orderItemMock = $this->createMock(\Magento\Sales\Model\Order\Item::class);
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
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getShipmentItemMock()
    {
        return $this->getMockBuilder(\Magento\Sales\Api\Data\ShipmentItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['register', 'getOrderItem'])
            ->getMockForAbstractClass();
    }
}
