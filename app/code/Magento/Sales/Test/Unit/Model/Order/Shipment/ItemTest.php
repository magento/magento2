<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Shipment;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\Order\Shipment\Item;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var Context|Context&MockObject|MockObject
     */
    private Context $context;

    /**
     * @var Registry|Registry&MockObject|MockObject
     */
    private Registry $registry;

    /**
     * @var ExtensionAttributesFactory|ExtensionAttributesFactory&MockObject|MockObject
     */
    private ExtensionAttributesFactory $extensionFactory;

    /**
     * @var AttributeValueFactory|AttributeValueFactory&MockObject|MockObject
     */
    private AttributeValueFactory $customAttributeFactory;

    /**
     * @var ItemFactory|ItemFactory&MockObject|MockObject
     */
    private ItemFactory $orderItemFactory;

    /**
     * @var AbstractResource|AbstractResource&MockObject|MockObject
     */
    private AbstractResource $resource;

    /**
     * @var Item
     */
    private Item $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->extensionFactory = $this->createMock(ExtensionAttributesFactory::class);
        $this->customAttributeFactory = $this->createMock(AttributeValueFactory::class);
        $this->orderItemFactory = $this->createMock(ItemFactory::class);
        $this->resource = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->addMethods(['getIdFieldName'])
            ->getMockForAbstractClass();
        $this->model = new Item(
            $this->context,
            $this->registry,
            $this->extensionFactory,
            $this->customAttributeFactory,
            $this->orderItemFactory,
            $this->resource
        );
    }

    /**
     * @return void
     */
    public function testGetOrderItem(): void
    {
        $childItem = $this->createMock(Order\Item::class);
        $childItem->expects($this->once())->method('getItemId')->willReturn(2);
        $collection = $this->createMock(ItemCollection::class);
        $collection->expects($this->once())->method('count')->willReturn(1);
        $collection->expects($this->once())->method('getIterator')
            ->willReturn(new \ArrayIterator([$childItem]));

        $order = $this->createMock(Order::class);
        $order->expects($this->once())->method('getItemsCollection')->willReturn($collection);

        $shipmentItem = $this->createMock(Order\Item::class);
        $shipmentItem->expects($this->once())->method('getOrder')->willReturn($order);
        $shipmentItem->expects($this->once())->method('setData')->with('has_children', true);
        $shipmentItem->expects($this->once())->method('addChildItem')->with($childItem);
        $shipmentItem->expects($this->any())->method('getItemId')->willReturn(1);

        $item = $this->createMock(Order\Item::class);
        $item->expects($this->once())->method('load')->willReturn($shipmentItem);

        $this->orderItemFactory->expects($this->once())
            ->method('create')
            ->willReturn($item);

        $this->model->getOrderItem();
    }
}
