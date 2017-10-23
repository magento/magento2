<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Test\Unit\Model\Export\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class RowCustomizerTest
 */
class RowCustomizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\BundleImportExport\Model\Export\RowCustomizer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowCustomizerMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productResourceCollection;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionsCollection;

    /**
     * @var \Magento\Bundle\Model\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $option;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectionsCollection;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selection;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->rowCustomizerMock = $this->objectManagerHelper->getObject(
            \Magento\BundleImportExport\Model\Export\RowCustomizer::class
        );
        $this->productResourceCollection = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
            ['addAttributeToFilter', 'getIterator']
        );
        $this->product = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getEntityId',
                'getPriceType',
                'getShipmentType',
                'getSkuType',
                'getPriceView',
                'getWeightType',
                'getTypeInstance',
                'getOptionsCollection',
                'getSelectionsCollection'
            ]
        );
        $this->product->expects($this->any())->method('getEntityId')->willReturn(1);
        $this->product->expects($this->any())->method('getPriceType')->willReturn(1);
        $this->product->expects($this->any())->method('getShipmentType')->willReturn(1);
        $this->product->expects($this->any())->method('getSkuType')->willReturn(1);
        $this->product->expects($this->any())->method('getPriceView')->willReturn(1);
        $this->product->expects($this->any())->method('getWeightType')->willReturn(1);
        $this->product->expects($this->any())->method('getTypeInstance')->willReturnSelf();
        $this->optionsCollection = $this->createPartialMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
            ['setOrder', 'getIterator']
        );
        $this->product->expects($this->any())->method('getOptionsCollection')->willReturn($this->optionsCollection);
        $this->optionsCollection->expects($this->any())->method('setOrder')->willReturnSelf();
        $this->option = $this->createPartialMock(
            \Magento\Bundle\Model\Option::class,
            ['getId', 'getTitle', 'getType', 'getRequired']
        );
        $this->option->expects($this->any())->method('getId')->willReturn(1);
        $this->option->expects($this->any())->method('getTitle')->willReturn('title');
        $this->option->expects($this->any())->method('getType')->willReturn(1);
        $this->option->expects($this->any())->method('getRequired')->willReturn(1);
        $this->optionsCollection->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$this->option]))
        );
        $this->selection = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getSku', 'getSelectionPriceValue', 'getIsDefault', 'getSelectionQty', 'getSelectionPriceType']
        );
        $this->selection->expects($this->any())->method('getSku')->willReturn(1);
        $this->selection->expects($this->any())->method('getSelectionPriceValue')->willReturn(1);
        $this->selection->expects($this->any())->method('getSelectionQty')->willReturn(1);
        $this->selection->expects($this->any())->method('getSelectionPriceType')->willReturn(1);
        $this->selectionsCollection = $this->createPartialMock(
            \Magento\Bundle\Model\ResourceModel\Selection\Collection::class,
            ['getIterator', 'addAttributeToSort']
        );
        $this->selectionsCollection->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$this->selection]))
        );
        $this->selectionsCollection->expects($this->any())->method('addAttributeToSort')->willReturnSelf();
        $this->product->expects($this->any())->method('getSelectionsCollection')->willReturn(
            $this->selectionsCollection
        );
        $this->productResourceCollection->expects($this->any())->method('addAttributeToFilter')->willReturnSelf();
        $this->productResourceCollection->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$this->product]))
        );
    }

    /**
     * Test prepareData()
     */
    public function testPrepareData()
    {
        $result = $this->rowCustomizerMock->prepareData($this->productResourceCollection, [1]);
        $this->assertNotNull($result);
    }

    /**
     * Test addHeaderColumns()
     */
    public function testAddHeaderColumns()
    {
        $productData = [0 => 'sku'];
        $expectedData = [
            'sku',
            'bundle_price_type',
            'bundle_sku_type',
            'bundle_price_view',
            'bundle_weight_type',
            'bundle_values',
            'bundle_shipment_type'
        ];
        $this->assertEquals($expectedData, $this->rowCustomizerMock->addHeaderColumns($productData));
    }

    /**
     * Test addData()
     */
    public function testAddData()
    {
        $preparedData = $this->rowCustomizerMock->prepareData($this->productResourceCollection, [1]);
        $attributes = 'attribute=1,sku_type=1,attribute2="Text",price_type=1,price_view=1,weight_type=1,'
            . 'values=values,shipment_type=1,attribute3=One,Two,Three';
        $dataRow = [
            'sku' => 'sku1',
            'additional_attributes' => $attributes
        ];
        $preparedRow = $preparedData->addData($dataRow, 1);
        $expected = [
            'sku' => 'sku1',
            'additional_attributes' => 'attribute=1,attribute2="Text",attribute3=One,Two,Three',
            'bundle_price_type' => 'fixed',
            'bundle_shipment_type' => 'separately',
            'bundle_sku_type' => 'fixed',
            'bundle_price_view' => 'As low as',
            'bundle_weight_type' => 'fixed',
            'bundle_values' => 'name=title,type=1,required=1,sku=1,price=1,default=,default_qty=1,price_type=percent'
        ];
        $this->assertEquals($expected, $preparedRow);
    }

    /**
     * Test getAdditionalRowsCount()
     */
    public function testGetAdditionalRowsCount()
    {
        $count = [5];
        $this->assertEquals($count, $this->rowCustomizerMock->getAdditionalRowsCount($count, 0));
    }
}
