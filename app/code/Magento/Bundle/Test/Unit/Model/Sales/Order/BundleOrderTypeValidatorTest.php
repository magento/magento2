<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order;

use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\ShipmentItemInterface;
use Magento\Sales\Model\Order\Item;
use Laminas\Uri\Http as HttpUri;
use Magento\Bundle\Model\Sales\Order\BundleOrderTypeValidator;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\Webapi\Request;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Test\Unit\Helper\ItemTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Model\Product\Type;

class BundleOrderTypeValidatorTest extends TestCase
{
    /**
     * @var Request|Request&MockObject|MockObject
     */
    private Request $request;

    /**
     * @var BundleOrderTypeValidator
     */
    private BundleOrderTypeValidator $validator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $uri = $this->createMock(HttpUri::class);
        $uri->method('getPath')->willReturn('V1/shipment/');
        $this->request->method('getUri')->willReturn($uri);

        $this->validator = new BundleOrderTypeValidator($this->request);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testIsValidSuccessShipmentTypeTogether(): void
    {
        $bundleProduct = new ProductTestHelper();
        $bundleProduct->setShipmentType(BundleOrderTypeValidator::SHIPMENT_TYPE_TOGETHER);

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->setProductType(Type::TYPE_BUNDLE);
        $bundleOrderItem->setProduct($bundleProduct);

        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('getItemById')
            ->willReturn($bundleOrderItem);

        $bundleShipmentItem = $this->createMock(ShipmentItemInterface::class);
        $bundleShipmentItem->method('getOrderItemId')->willReturn(1);

        $shipment = $this->createMock(Shipment::class);
        $shipment->expects($this->once())
            ->method('getItems')
            ->willReturn([$bundleShipmentItem]);
        $shipment->expects($this->once())->method('getOrder')->willReturn($order);

        try {
            $this->validator->isValid($shipment);
            $this->assertEmpty($this->validator->getMessages());
        } catch (\Exception $e) {
            $this->fail('Could not perform shipment validation. ' . $e->getMessage());
        }
    }

    public function testIsValidSuccessShipmentTypeSeparately()
    {
        $bundleProduct = new ProductTestHelper();
        $bundleProduct->setShipmentType(BundleOrderTypeValidator::SHIPMENT_TYPE_SEPARATELY);

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->setProductType(Type::TYPE_BUNDLE);
        $bundleOrderItem->setProduct($bundleProduct);

        $childOrderItem = $this->createMock(Item::class);
        $childOrderItem->method('getParentItemId')->willReturn(1);

        $order = $this->createMock(Order::class);
        $order->expects($this->any())
            ->method('getItemById')
            ->willReturnOnConsecutiveCalls($bundleOrderItem, $childOrderItem);

        $bundleShipmentItem = $this->createMock(ShipmentItemInterface::class);
        $bundleShipmentItem->method('getOrderItemId')->willReturn(1);
        $bundleShipmentItem->expects($this->exactly(3))->method('getOrderItemId')->willReturn(1);

        $childShipmentItem = $this->createMock(ShipmentItemInterface::class);
        $childShipmentItem->method('getOrderItemId')->willReturn(2);

        $shipment = $this->createMock(Shipment::class);
        $shipment->expects($this->once())
            ->method('getItems')
            ->willReturn([$bundleShipmentItem, $childShipmentItem]);
        $shipment->expects($this->exactly(2))->method('getOrder')->willReturn($order);

        try {
            $this->validator->isValid($shipment);
            $this->assertEmpty($this->validator->getMessages());
        } catch (\Exception $e) {
            $this->fail('Could not perform shipment validation. ' . $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testIsValidFailSeparateShipmentType(): void
    {
        $bundleProduct = new ProductTestHelper();
        $bundleProduct->setShipmentType(BundleOrderTypeValidator::SHIPMENT_TYPE_SEPARATELY);

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->setProductType(Type::TYPE_BUNDLE);
        $bundleOrderItem->setProduct($bundleProduct);
        $bundleOrderItem->setSku('sku');

        $childOrderItem = $this->createMock(Item::class);
        $childOrderItem->method('getParentItemId')->willReturn(1);
        $childOrderItem->method('getParentItem')->willReturn($bundleOrderItem);

        $order = $this->createMock(Order::class);
        $order->method('getItemById')->willReturn($childOrderItem);

        $childShipmentItem = $this->createMock(ShipmentItemInterface::class);
        $childShipmentItem->method('getOrderItemId')->willReturn(2);

        $shipment = $this->createMock(Shipment::class);
        $shipment->expects($this->once())
            ->method('getItems')
            ->willReturn([$childShipmentItem]);
        $shipment->expects($this->once())->method('getOrder')->willReturn($order);

        try {
            $this->validator->isValid($shipment);
            $this->assertNotEmpty($this->validator->getMessages());
            $this->assertTrue(
                in_array(
                    'Cannot create shipment as bundle product sku should be included as well.',
                    $this->validator->getMessages()
                )
            );
        } catch (\Exception $e) {
            $this->fail('Could not perform shipment validation. ' . $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testIsValidFailTogetherShipmentType(): void
    {
        $bundleProduct = new ProductTestHelper();
        $bundleProduct->setShipmentType(BundleOrderTypeValidator::SHIPMENT_TYPE_TOGETHER);

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->setProductType(Type::TYPE_BUNDLE);
        $bundleOrderItem->setProduct($bundleProduct);
        $bundleOrderItem->setSku('sku');

        $bundleShipmentItem = $this->createMock(ShipmentItemInterface::class);
        $bundleShipmentItem->method('getOrderItemId')->willReturn(1);
        $bundleShipmentItem->expects($this->exactly(3))->method('getOrderItemId')->willReturn(1);

        $childShipmentItem = $this->createMock(ShipmentItemInterface::class);
        $childShipmentItem->method('getOrderItemId')->willReturn(2);

        $childOrderItem = $this->createMock(Item::class);
        $childOrderItem->method('getParentItemId')->willReturn(1);

        $order = $this->createMock(Order::class);
        $order->expects($this->any())
            ->method('getItemById')
            ->willReturnOnConsecutiveCalls($bundleOrderItem, $childOrderItem);

        $shipment = $this->createMock(Shipment::class);
        $shipment->expects($this->once())
            ->method('getItems')
            ->willReturn([$bundleShipmentItem, $childShipmentItem]);
        $shipment->expects($this->exactly(2))->method('getOrder')->willReturn($order);

        try {
            $this->validator->isValid($shipment);
            $this->assertNotEmpty($this->validator->getMessages());
            $this->assertTrue(
                in_array(
                    'Cannot create shipment as bundle product "sku" has shipment type "Together". '
                    . 'Bundle product itself should be shipped instead.',
                    $this->validator->getMessages()
                )
            );
        } catch (\Exception $e) {
            $this->fail('Could not perform shipment validation. ' . $e->getMessage());
        }
    }

    /**
     * @return ItemTestHelper
     */
    private function getBundleOrderItemMock(): ItemTestHelper
    {
        return new ItemTestHelper();
    }
}
