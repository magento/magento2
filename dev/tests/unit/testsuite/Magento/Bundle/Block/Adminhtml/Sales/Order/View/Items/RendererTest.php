<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderItem;

    /** @var \Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer $model */
    protected $model;

    protected function setUp()
    {
        $this->orderItem = $this->getMock(
            'Magento\Sales\Model\Order\Item',
            ['getProductOptions', '__wakeup', 'getParentItem', 'getOrderItem'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer');
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
