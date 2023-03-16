<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order;

use Magento\Bundle\Model\Sales\Order\BundleOrderTypeValidator;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Shipment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Product\Type;

class BundleOrderTypeValidatorTest extends TestCase
{
    /**
     * @return void
     */
    public function testIsValidSuccess(): void
    {
        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->expects($this->exactly(2))->method('getItemId')->willReturn(1);
        $bundleOrderItem->expects($this->once())->method('isDummy')->with(true)->willReturn(true);
        $bundleOrderItem->expects($this->once())->method('getHasChildren')->willReturn(false);
        $bundleOrderItem->expects($this->any())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $bundleOrderItem->expects($this->any())->method('getSku')->willReturn('bundleSKU');

        $simpleProductOrderItem = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getHasChildren'])
            ->onlyMethods(['getItemId', 'isDummy', 'getProductType'])
            ->getMock();
        $simpleProductOrderItem->expects($this->exactly(2))->method('getItemId')->willReturn(2);
        $simpleProductOrderItem->expects($this->once())->method('isDummy')->with(true)->willReturn(true);
        $simpleProductOrderItem->expects($this->once())->method('getHasChildren')->willReturn(false);
        $simpleProductOrderItem->expects($this->any())->method('getProductType')->willReturn(Type::TYPE_SIMPLE);

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$bundleOrderItem, $simpleProductOrderItem]);

        $bundleShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $bundleShipmentItem->expects($this->exactly(2))->method('getOrderItemId')->willReturn(1);
        $simpleProductShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $simpleProductShipmentItem->expects($this->exactly(2))->method('getOrderItemId')->willReturn(2);

        $shipment = $this->createMock(Shipment::class);
        $shipment->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$bundleShipmentItem, $simpleProductShipmentItem]);
        $shipment->expects($this->once())->method('getOrder')->willReturn($order);

        try {
            $validator = new BundleOrderTypeValidator();
            $validator->isValid($shipment);
            $this->assertEmpty($validator->getMessages());
        } catch (\Exception $e) {
            $this->fail('Could not perform shipment validation. ' . $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testIsValidFailSeparateShipmentType(): void
    {
        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->expects($this->once())->method('getItemId')->willReturn(1);
        $bundleOrderItem->expects($this->once())->method('isDummy')->with(true)->willReturn(true);
        $bundleOrderItem->expects($this->once())->method('getHasChildren')->willReturn(true);
        $bundleOrderItem->expects($this->any())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $bundleOrderItem->expects($this->any())->method('getSku')->willReturn('bundleSKU');

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$bundleOrderItem]);

        $bundleShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $bundleShipmentItem->expects($this->once())->method('getOrderItemId')->willReturn(1);

        $shipment = $this->createMock(Shipment::class);
        $shipment->expects($this->once())
            ->method('getItems')
            ->willReturn([$bundleShipmentItem]);
        $shipment->expects($this->once())->method('getOrder')->willReturn($order);

        try {
            $validator = new BundleOrderTypeValidator();
            $validator->isValid($shipment);
            $messages = $validator->getMessages();
            $this->assertNotEmpty($messages);
            /** @var Phrase $validationMsg */
            $validationMsg = current($messages)[0];
            foreach ($validationMsg->getArguments() as $argument) {
                if (is_string($argument)) {
                    $this->assertSame($argument, 'bundleSKU');
                } else {
                    $this->assertTrue(in_array($argument->getText(), ['Separately', 'Bundle product options']));
                }
            }
        } catch (\Exception $e) {
            $this->fail('Could not perform shipment validation. ' . $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testIsValidFailTogetherShipmentType(): void
    {
        $parentItem = $this->createMock(OrderItemInterface::class);
        $parentItem->expects($this->once())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $parentItem->expects($this->any())->method('getSku')->willReturn('bundleSKU');

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->expects($this->once())->method('getItemId')->willReturn(1);
        $bundleOrderItem->expects($this->once())->method('isDummy')->with(true)->willReturn(true);
        $bundleOrderItem->expects($this->once())->method('getHasChildren')->willReturn(false);
        $bundleOrderItem->expects($this->any())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $bundleOrderItem->expects($this->exactly(3))->method('getParentItem')->willReturn($parentItem);

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$bundleOrderItem]);

        $bundleShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $bundleShipmentItem->expects($this->once())->method('getOrderItemId')->willReturn(1);

        $shipment = $this->createMock(Shipment::class);
        $shipment->expects($this->once())
            ->method('getItems')
            ->willReturn([$bundleShipmentItem]);
        $shipment->expects($this->once())->method('getOrder')->willReturn($order);

        try {
            $validator = new BundleOrderTypeValidator();
            $validator->isValid($shipment);
            $messages = $validator->getMessages();
            $this->assertNotEmpty($messages);
            /** @var Phrase $validationMsg */
            $validationMsg = current($messages)[0];
            foreach ($validationMsg->getArguments() as $argument) {
                if (is_string($argument)) {
                    $this->assertSame($argument, 'bundleSKU');
                } else {
                    $this->assertTrue(in_array($argument->getText(), ['Together', 'Bundle product itself']));
                }
            }
        } catch (\Exception $e) {
            $this->fail('Could not perform shipment validation. ' . $e->getMessage());
        }
    }

    /**
     * @return MockObject
     */
    private function getBundleOrderItemMock(): MockObject
    {
        return $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getHasChildren'])
            ->onlyMethods(['getItemId', 'isDummy', 'getProductType', 'getSku', 'getParentItem'])
            ->getMock();
    }
}
