<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Shipment;

class OrderRegistrarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\OrderRegistrar
     */
    private $model;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipmentMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->shipmentMock = $this->getMockBuilder(\Magento\Sales\Api\Data\ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = new \Magento\Sales\Model\Order\Shipment\OrderRegistrar();
    }

    /**
     * @dataProvider itemQuantities
     */
    public function testRegister($quantity, $reqisterCallsCount, $isDeletedCallsCount)
    {
        $item = $this->getShipmentItemMock();

        $item->expects($this->once())->method('getQty')->willReturn($quantity);
        $item->expects($this->exactly($reqisterCallsCount))->method('register');
        $item->expects($this->exactly($isDeletedCallsCount))->method('isDeleted');

        $items = [$item];
        $this->shipmentMock->expects($this->once())->method('getItems')->willReturn($items);
        $this->assertEquals(
            $this->orderMock,
            $this->model->register($this->orderMock, $this->shipmentMock)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getShipmentItemMock()
    {
        return $this->getMockBuilder(\Magento\Sales\Api\Data\ShipmentItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['register', 'isDeleted'])
            ->getMockForAbstractClass();
    }

    /**
     * @return array
     */
    public function itemQuantities()
    {
        return [
            [
                'quantity' => 0,
                'reqisterCallsCount' => 0,
                'isDeletedCallsCount' => 1,
            ],
            [
                'quantity' => 0.5,
                'reqisterCallsCount' => 1,
                'isDeletedCallsCount' => 0,
            ],
            [
                'quantity' => 1,
                'reqisterCallsCount' => 1,
                'isDeletedCallsCount' => 0,
            ],
        ];
    }
}
