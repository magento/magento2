<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Validation;

use Magento\Sales\Model\Order;

/**
 * Test for \Magento\Sales\Model\Order\Validation\CanShip class
 */
class CanShipTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Validation\CanShip|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getItems'])
            ->getMockForAbstractClass();

        $this->orderItemMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQtyToShip', 'getLockedDoShip'])
            ->getMockForAbstractClass();

        $this->model = new \Magento\Sales\Model\Order\Validation\CanShip();
    }

    /**
     * @param string $state
     *
     * @dataProvider canShipWrongStateDataProvider
     */
    public function testCanShipWrongState($state)
    {
        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn($state);
        $this->orderMock->expects($this->once())
            ->method('getStatus')
            ->willReturn('status');
        $this->orderMock->expects($this->never())
            ->method('getItems');
        $this->assertEquals(
            [__('A shipment cannot be created when an order has a status of %1', 'status')],
            $this->model->validate($this->orderMock)
        );
    }

    /**
     * Data provider for testCanShipWrongState
     * @return array
     */
    public function canShipWrongStateDataProvider()
    {
        return [
            [Order::STATE_PAYMENT_REVIEW],
            [Order::STATE_HOLDED],
            [Order::STATE_CANCELED],
        ];
    }

    public function testCanShipNoItems()
    {
        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);

        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->assertNotEmpty(
            $this->model->validate($this->orderMock)
        );
    }

    /**
     * @param float $qtyToShipment
     * @param bool|null $itemLockedDoShipment
     * @param bool $expectedResult
     *
     * @dataProvider canShipDataProvider
     */
    public function testCanShip($qtyToShipment, $itemLockedDoShipment, $expectedResult)
    {
        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);

        $items = [$this->orderItemMock];
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->orderItemMock->expects($this->any())
            ->method('getQtyToShip')
            ->willReturn($qtyToShipment);
        $this->orderItemMock->expects($this->any())
            ->method('getLockedDoShip')
            ->willReturn($itemLockedDoShipment);

        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->orderMock)
        );
    }

    /**
     * Data provider for testCanShip
     *
     * @return array
     */
    public function canShipDataProvider()
    {
        return [
            [0, null, [__('The order does not allow a shipment to be created.')]],
            [-1, null, [__('The order does not allow a shipment to be created.')]],
            [1, true, [__('The order does not allow a shipment to be created.')]],
            [0.5, false, []],
        ];
    }
}
