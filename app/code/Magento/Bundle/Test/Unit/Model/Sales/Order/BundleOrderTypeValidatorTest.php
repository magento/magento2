<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order;

use Laminas\Uri\Http as HttpUri;
use Magento\Bundle\Model\Sales\Order\BundleOrderTypeValidator;
use Magento\Catalog\Model\Product;
use Magento\Framework\Webapi\Request;
use Magento\Sales\Model\Order\Shipment;
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
        $uri->expects($this->any())->method('getPath')->willReturn('V1/shipment/');
        $this->request->expects($this->any())->method('getUri')->willReturn($uri);

        $this->validator = new BundleOrderTypeValidator($this->request);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testIsValidSuccessShipmentTypeTogether(): void
    {
        $bundleProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getShipmentType'])
            ->getMock();
        $bundleProduct->expects($this->any())
            ->method('getShipmentType')
            ->willReturn(BundleOrderTypeValidator::SHIPMENT_TYPE_TOGETHER);

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->expects($this->any())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $bundleOrderItem->expects($this->any())->method('getProduct')->willReturn($bundleProduct);

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->once())
            ->method('getItemById')
            ->willReturn($bundleOrderItem);

        $bundleShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $bundleShipmentItem->expects($this->any())->method('getOrderItemId')->willReturn(1);

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
        $bundleProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getShipmentType'])
            ->getMock();
        $bundleProduct->expects($this->any())
            ->method('getShipmentType')
            ->willReturn(BundleOrderTypeValidator::SHIPMENT_TYPE_SEPARATELY);

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->expects($this->any())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $bundleOrderItem->expects($this->any())->method('getProduct')->willReturn($bundleProduct);

        $childOrderItem = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $childOrderItem->expects($this->any())->method('getParentItemId')
            ->willReturn(1);

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->any())
            ->method('getItemById')
            ->willReturnOnConsecutiveCalls($bundleOrderItem, $childOrderItem);

        $bundleShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $bundleShipmentItem->expects($this->any())->method('getOrderItemId')->willReturn(1);
        $bundleShipmentItem->expects($this->exactly(3))->method('getOrderItemId')->willReturn(1);

        $childShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $childShipmentItem->expects($this->any())->method('getOrderItemId')->willReturn(2);

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
        $bundleProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getShipmentType'])
            ->getMock();
        $bundleProduct->expects($this->any())
            ->method('getShipmentType')
            ->willReturn(BundleOrderTypeValidator::SHIPMENT_TYPE_SEPARATELY);

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->expects($this->any())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $bundleOrderItem->expects($this->any())->method('getProduct')->willReturn($bundleProduct);
        $bundleOrderItem->expects($this->any())->method('getSku')->willReturn('sku');

        $childOrderItem = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $childOrderItem->expects($this->any())->method('getParentItemId')
            ->willReturn(1);
        $childOrderItem->expects($this->any())->method('getParentItem')->willReturn($bundleOrderItem);

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->any())
            ->method('getItemById')
            ->willReturn($childOrderItem);

        $childShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $childShipmentItem->expects($this->any())->method('getOrderItemId')->willReturn(2);

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
        $bundleProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getShipmentType'])
            ->getMock();
        $bundleProduct->expects($this->any())
            ->method('getShipmentType')
            ->willReturn(BundleOrderTypeValidator::SHIPMENT_TYPE_TOGETHER);

        $bundleOrderItem = $this->getBundleOrderItemMock();
        $bundleOrderItem->expects($this->any())->method('getProductType')->willReturn(Type::TYPE_BUNDLE);
        $bundleOrderItem->expects($this->any())->method('getProduct')->willReturn($bundleProduct);
        $bundleOrderItem->expects($this->any())->method('getSku')->willReturn('sku');

        $bundleShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $bundleShipmentItem->expects($this->any())->method('getOrderItemId')->willReturn(1);
        $bundleShipmentItem->expects($this->exactly(3))->method('getOrderItemId')->willReturn(1);

        $childShipmentItem = $this->createMock(\Magento\Sales\Api\Data\ShipmentItemInterface::class);
        $childShipmentItem->expects($this->any())->method('getOrderItemId')->willReturn(2);

        $childOrderItem = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $childOrderItem->expects($this->any())->method('getParentItemId')
            ->willReturn(1);

        $order = $this->createMock(\Magento\Sales\Model\Order::class);
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
     * @return MockObject
     */
    private function getBundleOrderItemMock(): MockObject
    {
        return $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->addMethods(['getHasChildren'])
            ->onlyMethods(['getItemId', 'isDummy', 'getProductType', 'getSku', 'getParentItem', 'getProduct'])
            ->getMock();
    }
}
