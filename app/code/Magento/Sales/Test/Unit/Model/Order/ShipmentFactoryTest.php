<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Convert\Order;
use Magento\Sales\Model\Convert\OrderFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Item as ShipmentItem;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order as SalesOrder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

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

        $this->subject = new ShipmentFactory(
            $convertOrderFactory,
            $this->trackFactory,
            new Json()
        );
    }

    /**
     * @param array|null $tracks     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[DataProvider('createDataProvider')]
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
            ShipmentItem::class,
            ['setQty', 'getOrderItem', 'getQty']
        );
        $shipmentItem->expects($this->once())
            ->method('setQty')
            ->with(5);
        $shipmentItem->expects($this->once())
            ->method('getQty')
            ->willReturn(5);

        $shipmentItem->expects($this->atLeastOnce())->method('getOrderItem')->willReturn($orderItem);

        $order = $this->createPartialMock(SalesOrder::class, ['getAllItems']);
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
     * @param array|null $tracks     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    #[DataProvider('createDataProvider')]
    public function testCreateWithFloatQtyShipment(?array $tracks): void
    {
        $orderItem = $this->createPartialMock(
            Item::class,
            ['getId', 'getQtyOrdered', 'getParentItemId', 'getIsVirtual','getIsQtyDecimal']
        );
        $orderItem->expects($this->any())
            ->method('getIsQtyDecimal')
            ->willReturn(true);
        $orderItem->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $orderItem->expects($this->any())
            ->method('getQtyOrdered')
            ->willReturn(0.5);
        $orderItem->expects($this->any())->method('getParentItemId')->willReturn(false);
        $orderItem->expects($this->any())->method('getIsVirtual')->willReturn(false);

        $shipmentItem = $this->createPartialMock(
            ShipmentItem::class,
            ['setQty', 'getOrderItem', 'getQty']
        );
        $shipmentItem->expects($this->once())
            ->method('setQty')
            ->with(0.5);
        $shipmentItem->expects($this->once())
            ->method('getQty')
            ->willReturn(0.5);

        $shipmentItem->expects($this->atLeastOnce())->method('getOrderItem')->willReturn($orderItem);

        $order = $this->createPartialMock(SalesOrder::class, ['getAllItems']);
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
            ->with(0.5)
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

        $this->assertEquals($shipment, $this->subject->create($order, ['1' => 0.5], $tracks));
    }

    /**
     * A bundle shipped separately records one shipped bundle per bundle's share of its items (#38029)
     *
     * Two bundles of two products, three of each per bundle. Before, every shipment added exactly 1 to the bundle's
     * shipped quantity.
     *
     * @param float $childShippedBefore
     * @param float $shipQty
     * @param float $parentShippedBefore
     * @param float $expectedParentQty
     * @param float $otherChildShipped
     * @param float $otherShipQty
     * @param float $childCanceled
     * @return void
     */
    #[DataProvider('bundleShippedSeparatelyDataProvider')]
    public function testCreateBundleShippedSeparately(
        float $childShippedBefore,
        float $shipQty,
        float $parentShippedBefore,
        float $expectedParentQty,
        float $otherChildShipped = 0,
        float $otherShipQty = 0,
        float $childCanceled = 0
    ): void {
        $methods = ['getId', 'getChildrenItems', 'isShipSeparately', 'isDummy'];
        $parent = $this->createPartialMock(Item::class, $methods);
        $child = $this->createPartialMock(Item::class, $methods);
        $other = $this->createPartialMock(Item::class, $methods);
        $parent->setData(
            ['qty_ordered' => 2, 'qty_shipped' => $parentShippedBefore, 'has_children' => true]
        );
        $child->setData(
            [
                'parent_item_id' => 1,
                'qty_ordered' => 6,
                'qty_shipped' => $childShippedBefore,
                'qty_canceled' => $childCanceled,
            ]
        );
        $other->setData(
            ['parent_item_id' => 1, 'qty_ordered' => 6, 'qty_shipped' => $otherChildShipped]
        );
        $parent->method('getId')->willReturn(1);
        $child->method('getId')->willReturn(2);
        $other->method('getId')->willReturn(3);
        $parent->method('getChildrenItems')->willReturn([$child, $other]);
        $parent->method('isShipSeparately')->willReturn(true);
        $parent->method('isDummy')->willReturn(true);
        $child->method('isShipSeparately')->willReturn(true);
        $child->method('isDummy')->willReturn(false);
        $other->method('isShipSeparately')->willReturn(true);
        $other->method('isDummy')->willReturn(false);

        $shipmentItems = [];
        foreach ([$parent, $child, $other] as $orderItem) {
            $shipmentItem = $this->createPartialMock(ShipmentItem::class, ['getOrderItem']);
            $shipmentItem->method('getOrderItem')->willReturn($orderItem);
            $shipmentItems[$orderItem->getId()] = $shipmentItem;
        }
        $order = $this->createPartialMock(SalesOrder::class, ['getAllItems']);
        $order->method('getAllItems')->willReturn([$parent, $child, $other]);
        $shipment = $this->createPartialMock(Shipment::class, ['addItem', 'setTotalQty']);
        $shipment->method('setTotalQty')->willReturnSelf();
        $this->converter->method('toShipment')->willReturn($shipment);
        $this->converter->method('itemToShipmentItem')->willReturnCallback(
            static fn ($orderItem) => $shipmentItems[$orderItem->getId()]
        );

        $this->subject->create($order, array_filter([2 => $shipQty, 3 => $otherShipQty]));

        if ($shipQty) {
            $this->assertEquals($shipQty, $shipmentItems[2]->getQty());
        }
        $this->assertEquals($expectedParentQty, $shipmentItems[1]->getQty());
    }

    /**
     * @return array
     */
    public static function bundleShippedSeparatelyDataProvider(): array
    {
        return [
            'everything: two bundles' => [0, 6, 0, 2, 0, 6],
            'one product only: one bundle started, not complete' => [0, 6, 0, 1],
            'earlier shipment of one product, now the other: both bundles' => [6, 0, 1, 1, 0, 6],
            'one bundle\'s share of a product canceled, the rest shipped: done' => [0, 3, 0, 2, 0, 6, 3],
            'a product partly canceled, the rest not yet shipped: one bundle' => [0, 3, 0, 1, 0, 3, 3],
            'one bundle of each product: one bundle' => [0, 3, 0, 1, 0, 3],
            'the rest of each product: the second bundle' => [3, 3, 1, 1, 3, 3],
            'one item: one bundle started' => [0, 1, 0, 1],
            'a further item of a started bundle: none' => [1, 1, 1, 0],
        ];
    }

    /**
     * @return array
     */
    public static function createDataProvider()
    {
        return [
            [null],
            [[['number' => 'TEST_TRACK']]],
            [[['number' => '']]],
        ];
    }
}
