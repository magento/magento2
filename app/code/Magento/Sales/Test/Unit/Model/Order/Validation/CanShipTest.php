<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Validation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Validation\CanShip;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Model\Order\Validation\CanShip class
 */
class CanShipTest extends TestCase
{
    /**
     * @var CanShip|MockObject
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var OrderItemInterface|MockObject
     */
    private $orderItemMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getItems'])
            ->getMockForAbstractClass();

        $this->orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQtyToShip', 'getLockedDoShip'])
            ->getMockForAbstractClass();

        $this->model = new CanShip();
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
