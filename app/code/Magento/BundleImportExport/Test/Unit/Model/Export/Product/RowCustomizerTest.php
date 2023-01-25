<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleImportExport\Test\Unit\Model\Export\Product;

use Magento\Bundle\Model\Option;
use Magento\BundleImportExport\Model\Export\RowCustomizer;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RowCustomizerTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var RowCustomizer|MockObject
     */
    protected $rowCustomizerMock;

    /**
     * @var Collection|MockObject
     */
    protected $productResourceCollection;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Option\Collection|MockObject
     */
    protected $optionsCollection;

    /**
     * @var Option|MockObject
     */
    protected $option;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection\Collection|MockObject
     */
    protected $selectionsCollection;

    /**
     * @var Product|MockObject
     */
    protected $selection;

    /** @var ScopeResolverInterface|MockObject */
    private $scopeResolver;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->scopeResolver = $this->getMockBuilder(ScopeResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getScope'])
            ->getMockForAbstractClass();
        $this->rowCustomizerMock = $this->objectManagerHelper->getObject(
            RowCustomizer::class,
            [
                'scopeResolver' => $this->scopeResolver,
            ]
        );
        $this->productResourceCollection = $this->createPartialMock(
            Collection::class,
            ['addAttributeToFilter', 'getIterator']
        );
        $this->product = $this->getMockBuilder(Product::class)
            ->addMethods([
                'getPriceType',
                'getShipmentType',
                'getSkuType',
                'getPriceView',
                'getWeightType',
                'getOptionsCollection',
                'getSelectionsCollection'
            ])
            ->onlyMethods(['getEntityId', 'getSku', 'getStoreIds', 'getTypeInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->method('getStoreIds')->willReturn([1]);
        $this->product->method('getEntityId')->willReturn(1);
        $this->product->method('getPriceType')->willReturn(1);
        $this->product->method('getShipmentType')->willReturn(1);
        $this->product->method('getSkuType')->willReturn(1);
        $this->product->method('getPriceView')->willReturn(1);
        $this->product->method('getWeightType')->willReturn(1);
        $this->product->method('getTypeInstance')->willReturnSelf();
        $this->optionsCollection = $this->createPartialMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
            ['setOrder', 'getItems']
        );
        $this->product->method('getOptionsCollection')->willReturn($this->optionsCollection);
        $this->optionsCollection->method('setOrder')->willReturnSelf();
        $this->option = $this->createPartialMock(
            Option::class,
            ['getId', 'getOptionId', 'getTitle', 'getType', 'getRequired']
        );
        $this->option->method('getId')->willReturn(1);
        $this->option->method('getOptionId')->willReturn(1);
        $this->option->method('getTitle')->willReturn('title');
        $this->option->method('getType')->willReturn(1);
        $this->option->method('getRequired')->willReturn(1);
        $this->optionsCollection->method('getItems')->willReturn(
            new \ArrayIterator([$this->option])
        );
        $this->selection = $this->getMockBuilder(Product::class)
            ->addMethods([
                'getSelectionPriceValue',
                'getIsDefault',
                'getSelectionQty',
                'getSelectionPriceType',
                'getSelectionCanChangeQty'
            ])
            ->onlyMethods(['getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->selection->method('getSku')->willReturn(1);
        $this->selection->method('getSelectionPriceValue')->willReturn(1);
        $this->selection->method('getSelectionQty')->willReturn(1);
        $this->selection->method('getSelectionPriceType')->willReturn(1);
        $this->selection->method('getSelectionCanChangeQty')->willReturn(1);
        $this->selectionsCollection = $this->createPartialMock(
            \Magento\Bundle\Model\ResourceModel\Selection\Collection::class,
            ['getIterator', 'addAttributeToSort']
        );
        $this->selectionsCollection->method('getIterator')->willReturn(
            new \ArrayIterator([$this->selection])
        );
        $this->selectionsCollection->method('addAttributeToSort')->willReturnSelf();
        $this->product->method('getSelectionsCollection')->willReturn(
            $this->selectionsCollection
        );
        $this->product->method('getSku')->willReturn(1);
        $this->productResourceCollection->method('addAttributeToFilter')->willReturnSelf();
        $this->productResourceCollection->method('getIterator')->willReturn(
            new \ArrayIterator([$this->product])
        );
    }

    /**
     * Test prepareData()
     */
    public function testPrepareData()
    {
        $scope = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $this->scopeResolver->method('getScope')->willReturn($scope);
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
        $scope = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $this->scopeResolver->method('getScope')->willReturn($scope);
        $preparedData = $this->rowCustomizerMock->prepareData($this->productResourceCollection, [1]);
        $attributes = 'attribute=1,sku_type=1,attribute2="Text",price_type=1,price_view=1,weight_type=1,'
            . 'values=values,shipment_type=1,attribute3=One,Two,Three';
        $dataRow = [
            'sku' => 'sku1',
            'additional_attributes' => $attributes
        ];
        $preparedRow = $preparedData->addData($dataRow, 1);

        $bundleValues = [
            'name=title',
            'type=1',
            'required=1',
            'sku=1',
            'price=1',
            'default=',
            'default_qty=1',
            'price_type=percent',
            'can_change_qty=1',
        ];

        $expected = [
            'sku' => 'sku1',
            'additional_attributes' => 'attribute=1,attribute2="Text",attribute3=One,Two,Three',
            'bundle_price_type' => 'fixed',
            'bundle_shipment_type' => 'separately',
            'bundle_sku_type' => 'fixed',
            'bundle_price_view' => 'As low as',
            'bundle_weight_type' => 'fixed',
            'bundle_values' => implode(',', $bundleValues)
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
