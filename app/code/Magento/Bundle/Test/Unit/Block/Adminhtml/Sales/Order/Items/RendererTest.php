<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Sales\Order\Items;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderItem;

    /** @var \Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer $model */
    protected $model;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject $serializer */
    protected $serializer;

    protected function setUp()
    {
        $this->orderItem = $this->getMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getProductOptions', '__wakeup', 'getParentItem', 'getOrderItem', 'getOrderItemId', 'getId'],
            [],
            '',
            false
        );
        $this->serializer = $this->getMock(\Magento\Framework\Serialize\Serializer\Json::class);
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
        $salesModel = $this->getMock($returnClass, ['getAllItems', '__wakeup'], [], '', false);
        $salesModel->expects($this->once())->method('getAllItems')->will($this->returnValue([]));

        $item = $this->getMock($class, [$method, 'getOrderItem', '__wakeup'], [], '', false);
        $item->expects($this->once())->method($method)->will($this->returnValue($salesModel));
        $item->expects($this->once())->method('getOrderItem')->will($this->returnValue($this->orderItem));
        $this->orderItem->expects($this->any())->method('getId')->will($this->returnValue(1));

        $this->assertSame(null, $this->model->getChildren($item));
    }

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
            $parentItem = $this->getMock(\Magento\Sales\Model\Order\Item::class, ['getId', '__wakeup'], [], '', false);
            $parentItem->expects($this->any())->method('getId')->will($this->returnValue(1));
        }
        $this->orderItem->expects($this->any())->method('getOrderItem')->will($this->returnSelf());
        $this->orderItem->expects($this->any())->method('getParentItem')->will($this->returnValue($parentItem));
        $this->orderItem->expects($this->any())->method('getOrderItemId')->will($this->returnValue(2));
        $this->orderItem->expects($this->any())->method('getId')->will($this->returnValue(1));

        $salesModel = $this->getMock(
            \Magento\Sales\Model\Order\Invoice::class,
            ['getAllItems',
            '__wakeup'],
            [],
            '',
            false
        );
        $salesModel->expects($this->once())->method('getAllItems')->will($this->returnValue([$this->orderItem]));

        $item = $this->getMock(
            \Magento\Sales\Model\Order\Invoice\Item::class,
            ['getInvoice', 'getOrderItem', '__wakeup'],
            [],
            '',
            false
        );
        $item->expects($this->once())->method('getInvoice')->will($this->returnValue($salesModel));
        $item->expects($this->any())->method('getOrderItem')->will($this->returnValue($this->orderItem));

        $this->assertSame([2 => $this->orderItem], $this->model->getChildren($item));
    }

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
        $this->orderItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($productOptions));

        $this->assertSame($result, $this->model->isShipmentSeparately());
    }

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
                $this->getMock(
                    \Magento\Sales\Model\Order\Item::class,
                    ['getProductOptions',
                    '__wakeup'],
                    [],
                    '',
                    false
                );
            $parentItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($productOptions));
        } else {
            $this->orderItem->expects($this->any())->method('getProductOptions')
                ->will($this->returnValue($productOptions));
        }
        $this->orderItem->expects($this->any())->method('getParentItem')->will($this->returnValue($parentItem));
        $this->orderItem->expects($this->any())->method('getOrderItem')->will($this->returnSelf());

        $this->assertSame($result, $this->model->isShipmentSeparately($this->orderItem));
    }

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
        $this->orderItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($productOptions));

        $this->assertSame($result, $this->model->isChildCalculated());
    }

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
                $this->getMock(
                    \Magento\Sales\Model\Order\Item::class,
                    ['getProductOptions',
                    '__wakeup'],
                    [],
                    '',
                    false
                );
            $parentItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($productOptions));
        } else {
            $this->orderItem->expects($this->any())->method('getProductOptions')
                ->will($this->returnValue($productOptions));
        }
        $this->orderItem->expects($this->any())->method('getParentItem')->will($this->returnValue($parentItem));
        $this->orderItem->expects($this->any())->method('getOrderItem')->will($this->returnSelf());

        $this->assertSame($result, $this->model->isChildCalculated($this->orderItem));
    }

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
        $this->orderItem->expects($this->any())->method('getProductOptions')->will($this->returnValue([]));
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
            ->will($this->returnValue($unserializedResult));
        $this->orderItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($options));

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
        $this->orderItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($productOptions));
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
        $this->orderItem->expects($this->any())->method('getOrderItem')->will($this->returnSelf());
        $this->orderItem->expects($this->any())->method('getParentItem')->will($this->returnValue($parentItem));
        $this->orderItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($productOptions));

        $this->assertSame($result, $this->model->canShowPriceInfo($this->orderItem));
    }

    public function canShowPriceInfoDataProvider()
    {
        return [
            [true, ['product_calculations' => 0], true],
            [false, [], true],
            [false, ['product_calculations' => 0], false],
        ];
    }
}
