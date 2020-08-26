<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Convert\Order;
use Magento\Sales\Model\Convert\OrderFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for shipment factory class.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShipmentFactoryTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var ShipmentFactory
     */
    protected $subject;

    /**
     * Order converter mock.
     *
     * @var \Magento\Sales\Model\Convert\Order|MockObject
     */
    protected $converter;

    /**
     * Shipment track factory mock.
     *
     * @var TrackFactory|MockObject
     */
    protected $trackFactory;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->converter = $this->createPartialMock(
            Order::class,
            ['toShipment', 'itemToShipmentItem']
        );

        $convertOrderFactory = $this->createPartialMock(OrderFactory::class, ['create']);
        $convertOrderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->converter);

        $this->trackFactory = $this->createPartialMock(
            TrackFactory::class,
            ['create']
        );

        $this->subject = $objectManager->getObject(
            ShipmentFactory::class,
            [
                'convertOrderFactory' => $convertOrderFactory,
                'trackFactory' => $this->trackFactory
            ]
        );
    }

    /**
     * @param array|null $tracks
     * @dataProvider createDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreate($tracks)
    {
        $orderItem = $this->createPartialMock(
            Item::class,
            ['getId', 'getQtyOrdered', 'getParentItemId', 'getIsVirtual']
        );
        $orderItem->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $orderItem->expects($this->any())
            ->method('getQtyOrdered')
            ->willReturn(5);
        $orderItem->expects($this->any())->method('getParentItemId')->willReturn(false);
        $orderItem->expects($this->any())->method('getIsVirtual')->willReturn(false);

        $shipmentItem = $this->createPartialMock(
            \Magento\Sales\Model\Order\Shipment\Item::class,
            ['setQty', 'getOrderItem', 'getQty']
        );
        $shipmentItem->expects($this->once())
            ->method('setQty')
            ->with(5);
        $shipmentItem->expects($this->once())
            ->method('getQty')
            ->willReturn(5);

        $shipmentItem->expects($this->atLeastOnce())->method('getOrderItem')->willReturn($orderItem);

        $order = $this->createPartialMock(\Magento\Sales\Model\Order::class, ['getAllItems']);
        $order->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$orderItem]);

        $shipment = $this->createPartialMock(
            Shipment::class,
            ['addItem', 'setTotalQty', 'addTrack']
        );
        $shipment->expects($this->once())
            ->method('addItem')
            ->with($shipmentItem);
        $shipment->expects($this->once())
            ->method('setTotalQty')
            ->with(5)
            ->willReturn($shipment);

        $this->converter->expects($this->any())
            ->method('toShipment')
            ->with($order)
            ->willReturn($shipment);
        $this->converter->expects($this->any())
            ->method('itemToShipmentItem')
            ->with($orderItem)
            ->willReturn($shipmentItem);

        if ($tracks) {
            $shipmentTrack = $this->createPartialMock(Track::class, ['addData']);

            if (empty($tracks[0]['number'])) {
                $shipmentTrack->expects($this->never())
                    ->method('addData');

                $this->trackFactory->expects($this->never())
                    ->method('create');

                $shipment->expects($this->never())
                    ->method('addTrack');

                $this->expectException(
                    LocalizedException::class
                );
            } else {
                $shipmentTrack->expects($this->once())
                    ->method('addData')
                    ->willReturnSelf();

                $this->trackFactory->expects($this->once())
                    ->method('create')
                    ->willReturn($shipmentTrack);

                $shipment->expects($this->once())
                    ->method('addTrack')
                    ->with($shipmentTrack);
            }
        }

        $this->assertEquals($shipment, $this->subject->create($order, ['1' => 5], $tracks));
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            [null],
            [[['number' => 'TEST_TRACK']]],
            [[['number' => '']]],
        ];
    }
}
