<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Sales\Order\Pdf\Items;

class AbstractItemsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderItem;

    /** @var \Magento\Bundle\Model\Sales\Order\Pdf\Items\Shipment $model */
    protected $model;

    protected function setUp()
    {
        $this->orderItem = $this->getMock(
            'Magento\Sales\Model\Order\Item',
            ['getProductOptions', '__wakeup', 'getParentItem', 'getOrderItem', 'getOrderItemId', 'getId'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Bundle\Model\Sales\Order\Pdf\Items\Shipment');
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

        $this->assertSame(null, $this->model->getChilds($item));
    }

    public function getChildrenEmptyItemsDataProvider()
    {
        return [
            ['Magento\Sales\Model\Order\Invoice\Item', 'getInvoice', 'Magento\Sales\Model\Order\Invoice'],
            ['Magento\Sales\Model\Order\Shipment\Item', 'getShipment', 'Magento\Sales\Model\Order\Shipment'],
            ['Magento\Sales\Model\Order\Creditmemo\Item', 'getCreditmemo', 'Magento\Sales\Model\Order\Creditmemo']
        ];
    }

    /**
     * @dataProvider getChildrenDataProvider
     */
    public function testGetChildren($parentItem)
    {
        if ($parentItem) {
            $parentItem = $this->getMock('Magento\Sales\Model\Order\Item', ['getId', '__wakeup'], [], '', false);
            $parentItem->expects($this->any())->method('getId')->will($this->returnValue(1));
        }
        $this->orderItem->expects($this->any())->method('getOrderItem')->will($this->returnSelf());
        $this->orderItem->expects($this->any())->method('getParentItem')->will($this->returnValue($parentItem));
        $this->orderItem->expects($this->any())->method('getOrderItemId')->will($this->returnValue(2));
        $this->orderItem->expects($this->any())->method('getId')->will($this->returnValue(1));

        $salesModel = $this->getMock('Magento\Sales\Model\Order\Invoice', ['getAllItems', '__wakeup'], [], '', false);
        $salesModel->expects($this->once())->method('getAllItems')->will($this->returnValue([$this->orderItem]));

        $item = $this->getMock(
            'Magento\Sales\Model\Order\Invoice\Item',
            ['getInvoice', 'getOrderItem', '__wakeup'],
            [],
            '',
            false
        );
        $item->expects($this->once())->method('getInvoice')->will($this->returnValue($salesModel));
        $item->expects($this->any())->method('getOrderItem')->will($this->returnValue($this->orderItem));

        $this->assertSame([2 => $this->orderItem], $this->model->getChilds($item));
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
                $this->getMock('Magento\Sales\Model\Order\Item', ['getProductOptions', '__wakeup'], [], '', false);
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
                $this->getMock('Magento\Sales\Model\Order\Item', ['getProductOptions', '__wakeup'], [], '', false);
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

    /**
     * @dataProvider getBundleOptionsDataProvider
     */
    public function testGetBundleOptions($productOptions, $result)
    {
        $this->model->setItem($this->orderItem);
        $this->orderItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($productOptions));
        $this->assertSame($result, $this->model->getBundleOptions());
    }

    public function getBundleOptionsDataProvider()
    {
        return [
            [['bundle_options' => 'result'], 'result'],
            [[], []],
        ];
    }

    /**
     * @dataProvider getSelectionAttributesDataProvider
     */
    public function testGetSelectionAttributes($productOptions, $result)
    {
        $this->orderItem->expects($this->any())->method('getProductOptions')->will($this->returnValue($productOptions));
        $this->assertSame($result, $this->model->getSelectionAttributes($this->orderItem));
    }

    public function getSelectionAttributesDataProvider()
    {
        return [
            [[], null],
            [['bundle_selection_attributes' => 'a:1:{i:0;i:1;}'], [0 => 1]],
        ];
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
