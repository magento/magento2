<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Product\CopyConstructor;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $duplicate;

    /**
     * @var \Magento\Bundle\Model\Product\CopyConstructor\Bundle
     */
    protected $model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        // Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate
        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->duplicate = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['setBundleOptionsData', 'setBundleSelectionsData', '__wakeup'],
            [],
            '',
            false
        );
        $this->model = new \Magento\Bundle\Model\Product\CopyConstructor\Bundle();
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testBuildNegative()
    {
        $this->product->expects($this->once())->method('getTypeId')->will($this->returnValue('other product'));
        $this->product->expects($this->never())->method('getTypeInstance');
        $this->model->build($this->product, $this->duplicate);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildPositive()
    {
        //prepare mocks and data samples
        $instance = $this->getMock(
            'Magento\Bundle\Model\Product\Type',
            ['setStoreFilter', 'getOptionsCollection', 'getSelectionsCollection', 'getOptionsIds'],
            [],
            '',
            false
        );
        $option = $this->getMock(
            'Magento\Bundle\Model\Option',
            ['getSelections', '__wakeup', 'getData'],
            [],
            '',
            false
        );
        $options = [$option];
        $optionCollection = $this->objectManager->getCollectionMock(
            'Magento\Bundle\Model\Resource\Option\Collection',
            $options
        );
        $optionRawData = [
            ['required' => true, 'position' => 100, 'type' => 'someType', 'title' => 'title', 'delete' => ''],
        ];
        $selectionRawData = [
            [
                [
                    'product_id' => 123,
                    'position' => 500,
                    'is_default' => false,
                    'selection_price_type' => 'priceType',
                    'selection_price_value' => 'priceVal',
                    'selection_qty' => 21,
                    'selection_can_change_qty' => 11,
                    'delete' => '',
                ],
            ],
        ];

        $selection = $this->getMock(
            'Magento\Bundle\Model\Selection',
            [
                'getProductId',
                'getPosition',
                'getIsDefault',
                'getSelectionPriceType',
                'getSelectionPriceValue',
                'getSelectionQty',
                'getSelectionCanChangeQty',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $selections = [$selection];
        $selectionCollection = $this->getMock(
            'Magento\Bundle\Model\Resource\Selection\Collection',
            [],
            [],
            '',
            false
        );

        // method flow
        $this->product->expects($this->once())->method('getTypeId')->will($this->returnValue('bundle'));
        $this->product->expects($this->any())->method('getTypeInstance')->will($this->returnValue($instance));
        $instance->expects($this->once())->method('setStoreFilter')->with(null, $this->product);
        $instance->expects(
            $this->once()
        )->method(
            'getOptionsCollection'
        )->with(
            $this->product
        )->will(
            $this->returnValue($optionCollection)
        );
        $instance->expects(
            $this->once()
        )->method(
            'getSelectionsCollection'
        )->with(
            null,
            $this->product
        )->will(
            $this->returnValue($selectionCollection)
        );
        $optionCollection->expects($this->once())->method('appendSelections')->with($selectionCollection);
        $option->expects($this->any())->method('getSelections')->will($this->returnValue($selections));

        $option->expects($this->at(0))->method('getData')->with('required')->will($this->returnValue(true));
        $option->expects($this->at(1))->method('getData')->with('position')->will($this->returnValue(100));
        $option->expects($this->at(2))->method('getData')->with('type')->will($this->returnValue('someType'));
        $option->expects($this->at(3))->method('getData')->with('title')->will($this->returnValue('title'));
        $option->expects($this->at(4))->method('getData')->with('title')->will($this->returnValue('title'));

        $selection->expects($this->once())->method('getProductId')->will($this->returnValue(123));
        $selection->expects($this->once())->method('getPosition')->will($this->returnValue(500));
        $selection->expects($this->once())->method('getIsDefault')->will($this->returnValue(false));
        $selection->expects($this->once())->method('getSelectionPriceType')->will($this->returnValue('priceType'));
        $selection->expects($this->once())->method('getSelectionPriceValue')->will($this->returnValue('priceVal'));
        $selection->expects($this->once())->method('getSelectionQty')->will($this->returnValue(21));
        $selection->expects($this->once())->method('getSelectionCanChangeQty')->will($this->returnValue(11));

        $this->duplicate->expects($this->once())->method('setBundleOptionsData')->with($optionRawData);
        $this->duplicate->expects($this->once())->method('setBundleSelectionsData')->with($selectionRawData);

        $this->model->build($this->product, $this->duplicate);
    }
}
