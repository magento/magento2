<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowCustomizerTest extends TestCase
{
    use MockCreationTrait;
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
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->scopeResolver = $this->createPartialMock(
            ScopeResolverInterface::class,
            ['getScope', 'getScopes']
        );
        
        // Mock StoreManager with Website and Store
        $websiteMock = $this->createMock(\Magento\Store\Model\Website::class);
        $websiteMock->method('getCode')->willReturn('base');
        $websiteMock->method('getDefaultGroupId')->willReturn(1);
        
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->method('getId')->willReturn(1);
        
        $groupMock = $this->createMock(\Magento\Store\Model\Group::class);
        $groupMock->method('getDefaultStoreId')->willReturn(1);
        
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->method('getWebsite')->willReturn($websiteMock);
        $storeManager->method('getStore')->willReturn($storeMock);
        $storeManager->method('getGroup')->willReturn($groupMock);
        
        $this->rowCustomizerMock = $this->objectManagerHelper->getObject(
            RowCustomizer::class,
            [
                'scopeResolver' => $this->scopeResolver,
                'storeManager' => $storeManager,
            ]
        );
        $this->productResourceCollection = $this->createPartialMock(
            Collection::class,
            ['addAttributeToFilter', 'getIterator']
        );
        // Mock Product - mock methods to avoid resource dependencies
        $this->product = $this->createPartialMockWithReflection(
            Product::class,
            ['getTypeInstance', 'getSku', 'getEntityId', 'getPriceType', 'getShipmentType',
             'getSkuType', 'getPriceView', 'getWeightType', 'getWebsiteIds']
        );
        $this->product->method('getSku')->willReturn('test-sku');
        $this->product->method('getEntityId')->willReturn(1);
        $this->product->method('getPriceType')->willReturn(1);
        $this->product->method('getShipmentType')->willReturn(1);
        $this->product->method('getSkuType')->willReturn(1);
        $this->product->method('getPriceView')->willReturn(1);
        $this->product->method('getWeightType')->willReturn(1);
        $this->product->method('getWebsiteIds')->willReturn([1]);
        $this->product->setStoreIds([1]);
        $this->product->setEntityId(1);
        $this->product->setPriceType(1);
        $this->product->setShipmentType(1);
        $this->product->setSkuType(1);
        $this->product->setPriceView(1);
        $this->product->setWeightType(1);
        $this->optionsCollection = $this->createPartialMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
            ['setOrder', 'getItems', 'getItemById', 'appendSelections', 'getIterator']
        );
        $this->product->setOptionsCollection($this->optionsCollection);
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
        $this->optionsCollection->method('getItems')->willReturn([$this->option]);
        $this->optionsCollection->method('getItemById')->willReturn($this->option);
        $this->optionsCollection->method('appendSelections')->willReturn([$this->option]);
        $this->optionsCollection->method('getIterator')->willReturn(new \ArrayIterator([$this->option]));
        // Mock selection product with magic method support
        $this->selection = $this->createPartialMockWithReflection(
            Product::class,
            ['getSku', 'getSelectionPriceValue', 'getSelectionQty', 'getSelectionPriceType',
             'getSelectionCanChangeQty', 'getOptionId']
        );
        $this->selection->method('getSku')->willReturn(1);
        $this->selection->method('getSelectionPriceValue')->willReturn(1);
        $this->selection->method('getSelectionQty')->willReturn(1);
        $this->selection->method('getSelectionPriceType')->willReturn(1);
        $this->selection->method('getSelectionCanChangeQty')->willReturn(1);
        $this->selection->method('getOptionId')->willReturn(1);
        $this->selectionsCollection = $this->createPartialMock(
            \Magento\Bundle\Model\ResourceModel\Selection\Collection::class,
            ['getIterator', 'addAttributeToSort', 'getItems']
        );
        $this->selectionsCollection->method('getIterator')->willReturn(
            new \ArrayIterator([$this->selection])
        );
        $this->selectionsCollection->method('addAttributeToSort')->willReturnSelf();
        $this->selectionsCollection->method('getItems')->willReturn([$this->selection]);
        $this->option->setData('selections', [$this->selection]);
        $this->product->setSelectionsCollection($this->selectionsCollection);
        
        // Mock type instance - needed by production code
        $typeInstance = $this->createPartialMock(
            \Magento\Bundle\Model\Product\Type::class,
            ['getOptionsCollection', 'getSelectionsCollection', 'getOptionsIds', 'setStoreFilter']
        );
        $typeInstance->method('getOptionsCollection')->willReturn($this->optionsCollection);
        $typeInstance->method('getSelectionsCollection')->willReturn($this->selectionsCollection);
        $typeInstance->method('getOptionsIds')->willReturn([1]);
        $typeInstance->method('setStoreFilter')->willReturnSelf();
        $this->product->method('getTypeInstance')->willReturn($typeInstance);
        
        $this->product->setSku(1);
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
        $scope = $this->createMock(ScopeInterface::class);
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
        $scope = $this->createMock(ScopeInterface::class);
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
