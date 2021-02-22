<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Sales\Order\Items;

class RendererTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Sales\Model\Order\Item|\PHPUnit\Framework\MockObject\MockObject */
    protected $orderItem;

    /** @var \Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer $model */
    protected $model;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject $serializer */
    protected $serializer;

    protected function setUp(): void
    {
        $this->orderItem = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getProductOptions', '__wakeup', 'getParentItem', 'getOrderItem', 'getOrderItemId', 'getId']
        );
        $this->serializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer::class,
            ['serializer' => $this->serializer]
        );
    }

    /**
     * @dataProvider getChildrenEmptyItemsDataProvider
     */
    public function testGetChildrenEmptyItems($class, $method, $returnClass)
    {
        $salesModel = $this->createPartialMock($returnClass, ['getAllItems', '__wakeup']);
        $salesModel->expects($this->once())->method('getAllItems')->willReturn([]);

        $item = $this->createPartialMock($class, [$method, 'getOrderItem', '__wakeup']);
        $item->expects($this->once())->method($method)->willReturn($salesModel);
        $item->expects($this->once())->method('getOrderItem')->willReturn($this->orderItem);
        $this->orderItem->expects($this->any())->method('getId')->willReturn(1);

        $this->assertNull($this->model->getChildren($item));
    }

    /**
     * @return array
     */
    public function getChildrenEmptyItemsDataProvider()
    {
        return [
            [
                \Magento\Sales\Model\Order\Invoice\Item::class,
                'getInvoice',
                \Magento\Sales\Model\Order\Invoice::class
            ],
            [
                \Magento\Sales\Model\Order\Shipment\Item::class,
                'getShipment',
                \Magento\Sales\Model\Order\Shipment::class
            ],
            [
                \Magento\Sales\Model\Order\Creditmemo\Item::class,
                'getCreditmemo',
                \Magento\Sales\Model\Order\Creditmemo::class
            ]
        ];
    }

    /**
     * @dataProvider getChildrenDataProvider
     */
    public function testGetChildren($parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, ['getId', '__wakeup']);
            $parentItem->expects($this->any())->method('getId')->willReturn(1);
        }
        $this->orderItem->expects($this->any())->method('getOrderItem')->willReturnSelf();
        $this->orderItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $this->orderItem->expects($this->any())->method('getOrderItemId')->willReturn(2);
        $this->orderItem->expects($this->any())->method('getId')->willReturn(1);

        $salesModel = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice::class,
            ['getAllItems', '__wakeup']
        );
        $salesModel->expects($this->once())->method('getAllItems')->willReturn([$this->orderItem]);

        $item = $this->createPartialMock(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            ['getInvoice', 'getOrderItem', 'getOrderItemId', '__wakeup']
        );
        $item->expects($this->any())->method('getInvoice')->willReturn($salesModel);
        $item->expects($this->any())->method('getOrderItem')->willReturn($this->orderItem);
        $item->expects($this->any())->method('getOrderItemId')->willReturn(2);

        $this->assertSame([2 => $this->orderItem], $this->model->getChildren($item));
    }

    /**
     * @return array
     */
    public function getChildrenDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider isShipmentSeparatelyWithoutItemDataProvider
     */
    public function testIsShipmentSeparatelyWithoutItem($productOptions, $result)
    {
        $this->model->setItem($this->orderItem);
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);

        $this->assertSame($result, $this->model->isShipmentSeparately());
    }

    /**
     * @return array
     */
    public function isShipmentSeparatelyWithoutItemDataProvider()
    {
        return [
            [['shipment_type' => 1], true],
            [['shipment_type' => 0], false],
            [[], false]
        ];
    }

    /**
     * @dataProvider isShipmentSeparatelyWithItemDataProvider
     */
    public function testIsShipmentSeparatelyWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItem =
                $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, ['getProductOptions',
                    '__wakeup']);
            $parentItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);
        } else {
            $this->orderItem->expects($this->any())->method('getProductOptions')
                ->willReturn($productOptions);
        }
        $this->orderItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $this->orderItem->expects($this->any())->method('getOrderItem')->willReturnSelf();

        $this->assertSame($result, $this->model->isShipmentSeparately($this->orderItem));
    }

    /**
     * @return array
     */
    public function isShipmentSeparatelyWithItemDataProvider()
    {
        return [
            [['shipment_type' => 1], false, false],
            [['shipment_type' => 0], true, false],
            [['shipment_type' => 1], true, true],
            [['shipment_type' => 0], false, true],
        ];
    }

    /**
     * @dataProvider isChildCalculatedWithoutItemDataProvider
     */
    public function testIsChildCalculatedWithoutItem($productOptions, $result)
    {
        $this->model->setItem($this->orderItem);
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);

        $this->assertSame($result, $this->model->isChildCalculated());
    }

    /**
     * @return array
     */
    public function isChildCalculatedWithoutItemDataProvider()
    {
        return [
            [['product_calculations' => 0], true],
            [['product_calculations' => 1], false],
            [[], false],
        ];
    }

    /**
     * @dataProvider isChildCalculatedWithItemDataProvider
     */
    public function testIsChildCalculatedWithItem($productOptions, $result, $parentItem)
    {
        if ($parentItem) {
            $parentItem =
                $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, ['getProductOptions',
                    '__wakeup']);
            $parentItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);
        } else {
            $this->orderItem->expects($this->any())->method('getProductOptions')
                ->willReturn($productOptions);
        }
        $this->orderItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $this->orderItem->expects($this->any())->method('getOrderItem')->willReturnSelf();

        $this->assertSame($result, $this->model->isChildCalculated($this->orderItem));
    }

    /**
     * @return array
     */
    public function isChildCalculatedWithItemDataProvider()
    {
        return [
            [['product_calculations' => 0], false, false],
            [['product_calculations' => 1], true, false],
            [['product_calculations' => 0], true, true],
            [['product_calculations' => 1], false, true],
        ];
    }

    public function testGetSelectionAttributes()
    {
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn([]);
        $this->assertNull($this->model->getSelectionAttributes($this->orderItem));
    }

    public function testGetSelectionAttributesWithBundle()
    {
        $bundleAttributes = 'Serialized value';
        $options = ['bundle_selection_attributes' => $bundleAttributes];
        $unserializedResult = 'result of "bundle_selection_attributes" unserialization';

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->with($bundleAttributes)
            ->willReturn($unserializedResult);
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($options);

        $this->assertEquals($unserializedResult, $this->model->getSelectionAttributes($this->orderItem));
    }

    public function testGetOrderOptions()
    {
        $productOptions = [
            'options' => ['options'],
            'additional_options' => ['additional_options'],
            'attributes_info' => ['attributes_info'],
        ];
        $this->model->setItem($this->orderItem);
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);
        $this->assertEquals(['attributes_info', 'options', 'additional_options'], $this->model->getOrderOptions());
    }

    public function testGetOrderItem()
    {
        $this->model->setItem($this->orderItem);
        $this->assertSame($this->orderItem, $this->model->getOrderItem());
    }

    /**
     * @dataProvider canShowPriceInfoDataProvider
     */
    public function testCanShowPriceInfo($parentItem, $productOptions, $result)
    {
        $this->model->setItem($this->orderItem);
        $this->orderItem->expects($this->any())->method('getOrderItem')->willReturnSelf();
        $this->orderItem->expects($this->any())->method('getParentItem')->willReturn($parentItem);
        $this->orderItem->expects($this->any())->method('getProductOptions')->willReturn($productOptions);

        $this->assertSame($result, $this->model->canShowPriceInfo($this->orderItem));
    }

    /**
     * @return array
     */
    public function canShowPriceInfoDataProvider()
    {
        return [
            [true, ['product_calculations' => 0], true],
            [false, [], true],
            [false, ['product_calculations' => 0], false],
        ];
    }
}
