<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Model\OptionFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\BundleFactory;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Bundle\Model\Selection;
use Magento\Bundle\Model\SelectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\StockState;
use Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for bundle product type
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeTest extends TestCase
{
    /**
     * @var BundleFactory|MockObject
     */
    private $bundleFactory;

    /**
     * @var SelectionFactory|MockObject
     */
    private $bundleModelSelection;

    /**
     * @var Type
     */
    protected $model;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $bundleCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Data|MockObject
     */
    protected $catalogData;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var OptionFactory|MockObject
     */
    protected $bundleOptionFactory;

    /**
     * @var StockRegistryInterface|MockObject
     */
    protected $stockRegistry;

    /**
     * @var StockStateInterface|MockObject
     */
    protected $stockState;

    /**
     * @var \Magento\Catalog\Helper\Product|MockObject
     */
    private $catalogProduct;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrency;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ArrayUtils|MockObject
     */
    private $arrayUtility;

    /**
     * @var CollectionProcessor|MockObject
     */
    private $catalogRuleProcessor;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->bundleCollectionFactory =
            $this->getMockBuilder(CollectionFactory::class)
                ->onlyMethods(['create'])
                ->addMethods(
                    [
                        'addFilterByRequiredOptions',
                        'addAttributeToSelect',
                        'getItemById',
                        'setOptionIdsFilter',
                        'setFlag',
                        'setPositionOrder',
                        'addStoreFilter',
                        'setStoreId'
                    ]
                )
                ->disableOriginalConstructor()
                ->getMock();
        $this->catalogData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->bundleOptionFactory = $this->getMockBuilder(OptionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->onlyMethods(['getStockItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockState = $this->getMockBuilder(StockState::class)
            ->onlyMethods(['getStockQty'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProduct = $this->getMockBuilder(\Magento\Catalog\Helper\Product::class)
            ->onlyMethods(['getSkipSaleableCheck'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->onlyMethods(['convert'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->bundleModelSelection = $this->getMockBuilder(SelectionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleFactory = $this->getMockBuilder(BundleFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(Json::class)
            ->onlyMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->arrayUtility = $this->getMockBuilder(ArrayUtils::class)
            ->onlyMethods(['flatten'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogRuleProcessor = $this->getMockBuilder(CollectionProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = new ObjectManager($this);
        $this->model = $objectHelper->getObject(
            Type::class,
            [
                'bundleModelSelection' => $this->bundleModelSelection,
                'bundleFactory' => $this->bundleFactory,
                'bundleCollection' => $this->bundleCollectionFactory,
                'bundleOption' => $this->bundleOptionFactory,
                'catalogData' => $this->catalogData,
                'storeManager' => $this->storeManager,
                'stockRegistry' => $this->stockRegistry,
                'stockState' => $this->stockState,
                'catalogProduct' => $this->catalogProduct,
                'priceCurrency' => $this->priceCurrency,
                'serializer' => $this->serializer,
                'metadataPool' => $this->metadataPool,
                'arrayUtility' => $this->arrayUtility
            ]
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedWithoutOptions(): void
    {
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionsData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId'])
            ->addMethods(['getRequired', 'isMultiSelection'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->onlyMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(
                [
                    'getOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setQty',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData'
                ]
            )
            ->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Type $productType */
        $productType = $this->getMockBuilder(Type::class)
            ->onlyMethods(['setStoreFilter', 'getOptionsCollection', 'getOptionsIds', 'getSelectionsCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->onlyMethods(['getItems', 'getItemById', 'appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $product->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $optionCollection->expects($this->any())
            ->method('appendSelections')
            ->with($selectionCollection, true, true)
            ->willReturn([$option]);
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $productType->expects($this->once())
            ->method('getOptionsCollection')
            ->willReturn($optionCollection);
        $productType->expects($this->once())
            ->method('getOptionsIds')
            ->willReturn([1, 2, 3]);
        $productType->expects($this->once())
            ->method('getSelectionsCollection')
            ->willReturn($selectionCollection);
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn('options');
        $option
            ->method('getId')
            ->willReturnOnConsecutiveCalls(333, 3);
        $option->expects($this->once())
            ->method('getRequired')
            ->willReturn(true);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('Please specify product option(s).', $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedWithShoppingCart(): void
    {
        /** @var MockObject|Price $priceModel */
        $priceModel = $this->getMockBuilder(Price::class)
            ->addMethods(['getSelectionFinalTotalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionQty',
                    'getBundleOptionsData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId', 'getProduct', 'getTitle'])
            ->addMethods(['getRequired', 'isMultiSelection', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->onlyMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $selection = $this->getMockBuilder(DataObject::class)
            ->addMethods(
                [
                    '__wakeup',
                    'isSalable',
                    'getOptionId',
                    'getSelectionCanChangeQty',
                    'getSelectionId',
                    'addCustomOption',
                    'getId',
                    'getOption',
                    'getTypeInstance'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(
                [
                    'getOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setQty',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData',
                    'getId',
                    'getCustomOption',
                    'getPriceModel'
                ]
            )
            ->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Type $productType */
        $productType = $this->getMockBuilder(Type::class)
            ->onlyMethods(['setStoreFilter', 'prepareForCart'])
            ->addMethods(['setParentProductId', 'addCustomOption', 'setCartQty', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->onlyMethods(['getItems', 'getItemById', 'appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $product->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($key) use ($optionCollection, $selectionCollection) {
                    $resultValue = null;
                    switch ($key) {
                        case '_cache_instance_options_collection':
                            $resultValue = $optionCollection;
                            break;
                        case '_cache_instance_used_selections':
                            $resultValue = $selectionCollection;
                            break;
                        case '_cache_instance_used_selections_ids':
                            $resultValue = [5];
                            break;
                    }

                    return $resultValue;
                }
            );
        $bundleOptions = [3 => 5];

        $product->expects($this->any())
            ->method('getId')
            ->willReturn(333);
        $product->expects($this->once())
            ->method('getCustomOption')
            ->willReturn($option);
        $product->expects($this->once())
            ->method('getPriceModel')
            ->willReturn($priceModel);
        $optionCollection->expects($this->once())
            ->method('getItemById')
            ->willReturn($option);
        $optionCollection->expects($this->once())
            ->method('appendSelections')
            ->with($selectionCollection, true, true);
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $buyRequest->expects($this->once())->method('getBundleOption')->willReturn($bundleOptions);
        $selectionCollection->expects($this->any())
            ->method('getItems')
            ->willReturn([$selection]);
        $selectionCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(1);
        $selection->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);
        $selection->expects($this->any())
            ->method('getOptionId')
            ->willReturn(3);
        $selection->expects($this->any())
            ->method('getOption')
            ->willReturn($option);
        $selection->expects($this->once())
            ->method('getSelectionCanChangeQty')
            ->willReturn(true);
        $selection->expects($this->once())
            ->method('getSelectionId');
        $selection->expects($this->once())
            ->method('addCustomOption')
            ->willReturnSelf();
        $selection->expects($this->any())
            ->method('getId')
            ->willReturn(333);
        $selection->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $option
            ->method('getId')
            ->willReturnOnConsecutiveCalls(333, 3, 3);
        $option->expects($this->once())
            ->method('getRequired')
            ->willReturn(false);
        $option->expects($this->once())
            ->method('isMultiSelection')
            ->willReturn(true);
        $option->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $option->expects($this->once())
            ->method('getValue')
            ->willReturn(4);
        $option->expects($this->once())
            ->method('getTitle')
            ->willReturn('Title for option');

        $this->arrayUtility->expects($this->once())->method('flatten')->willReturn($bundleOptions);

        $buyRequest->expects($this->once())
            ->method('getBundleOptionQty')
            ->willReturn([3 => 5]);
        $priceModel->expects($this->once())
            ->method('getSelectionFinalTotalPrice')
            ->willReturnSelf();
        $productType->expects($this->once())
            ->method('prepareForCart')
            ->willReturn([$productType]);
        $productType->expects($this->once())
            ->method('setParentProductId')
            ->willReturnSelf();
        $productType->expects($this->any())
            ->method('addCustomOption')
            ->willReturnSelf();
        $productType->expects($this->once())
            ->method('setCartQty')
            ->willReturnSelf();
        $productType->expects($this->once())
            ->method('getSelectionId')
            ->willReturn(314);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals([$product, $productType], $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedEmptyShoppingCart(): void
    {
        /** @var MockObject|Price $priceModel */
        $priceModel = $this->getMockBuilder(Price::class)
            ->addMethods(['getSelectionFinalTotalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionQty',
                    'getBundleOptionsData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId', 'getProduct', 'getTitle'])
            ->addMethods(['getRequired', 'isMultiSelection', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->onlyMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $selection = $this->getMockBuilder(DataObject::class)
            ->addMethods(
                [
                    '__wakeup',
                    'isSalable',
                    'getOptionId',
                    'getSelectionCanChangeQty',
                    'getSelectionId',
                    'addCustomOption',
                    'getId',
                    'getOption',
                    'getTypeInstance'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(
                [
                    'getOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setQty',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData',
                    'getId',
                    'getCustomOption',
                    'getPriceModel'
                ]
            )
            ->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Type $productType */
        $productType = $this->getMockBuilder(Type::class)
            ->onlyMethods(['setStoreFilter', 'prepareForCart'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->onlyMethods(['getItems', 'getItemById', 'appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $product->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($key) use ($optionCollection, $selectionCollection) {
                    $resultValue = null;
                    switch ($key) {
                        case '_cache_instance_options_collection':
                            $resultValue = $optionCollection;
                            break;
                        case '_cache_instance_used_selections':
                            $resultValue = $selectionCollection;
                            break;
                        case '_cache_instance_used_selections_ids':
                            $resultValue = [5];
                            break;
                    }

                    return $resultValue;
                }
            );
        $bundleOptions = [3 => 5];

        $product->expects($this->any())
            ->method('getId')
            ->willReturn(333);
        $product->expects($this->once())
            ->method('getCustomOption')
            ->willReturn($option);
        $product->expects($this->once())
            ->method('getPriceModel')
            ->willReturn($priceModel);
        $optionCollection->expects($this->once())
            ->method('getItemById')
            ->willReturn($option);
        $optionCollection->expects($this->once())
            ->method('appendSelections')
            ->with($selectionCollection, true, true);
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn($bundleOptions);

        $this->arrayUtility->expects($this->once())->method('flatten')->willReturn($bundleOptions);

        $selectionCollection->expects($this->any())
            ->method('getItems')
            ->willReturn([$selection]);
        $selectionCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(1);
        $selection->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);
        $selection->expects($this->any())
            ->method('getOptionId')
            ->willReturn(3);
        $selection->expects($this->any())
            ->method('getOption')
            ->willReturn($option);
        $selection->expects($this->once())
            ->method('getSelectionCanChangeQty')
            ->willReturn(true);
        $selection->expects($this->once())
            ->method('getSelectionId');
        $selection->expects($this->once())
            ->method('addCustomOption')
            ->willReturnSelf();
        $selection->expects($this->any())
            ->method('getId')
            ->willReturn(333);
        $selection->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $option
            ->method('getId')
            ->willReturnOnConsecutiveCalls(333, 3, 3);
        $option->expects($this->once())
            ->method('getRequired')
            ->willReturn(false);
        $option->expects($this->once())
            ->method('isMultiSelection')
            ->willReturn(true);
        $option->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $option->expects($this->once())
            ->method('getValue')
            ->willReturn(4);
        $option->expects($this->once())
            ->method('getTitle')
            ->willReturn('Title for option');
        $buyRequest->expects($this->once())
            ->method('getBundleOptionQty')
            ->willReturn([3 => 5]);
        $priceModel->expects($this->once())
            ->method('getSelectionFinalTotalPrice')
            ->willReturnSelf();
        $productType->expects($this->once())
            ->method('prepareForCart')
            ->willReturn([]);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('We can\'t add this item to your shopping cart right now.', $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedStringInResult(): void
    {
        /** @var MockObject|Price $priceModel */
        $priceModel = $this->getMockBuilder(Price::class)
            ->addMethods(['getSelectionFinalTotalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionQty',
                    'getBundleOptionsData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId', 'getProduct', 'getTitle'])
            ->addMethods(['getRequired', 'isMultiSelection', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->onlyMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $selection = $this->getMockBuilder(DataObject::class)
            ->addMethods(
                [
                    '__wakeup',
                    'isSalable',
                    'getOptionId',
                    'getSelectionCanChangeQty',
                    'getSelectionId',
                    'addCustomOption',
                    'getId',
                    'getOption',
                    'getTypeInstance'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(
                [
                    'getOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setQty',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData',
                    'getId',
                    'getCustomOption',
                    'getPriceModel'
                ]
            )
            ->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Type $productType */
        $productType = $this->getMockBuilder(Type::class)
            ->onlyMethods(['setStoreFilter', 'prepareForCart'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->onlyMethods(['getItems', 'getItemById', 'appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $product->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($key) use ($optionCollection, $selectionCollection) {
                    $resultValue = null;
                    switch ($key) {
                        case '_cache_instance_options_collection':
                            $resultValue = $optionCollection;
                            break;
                        case '_cache_instance_used_selections':
                            $resultValue = $selectionCollection;
                            break;
                        case '_cache_instance_used_selections_ids':
                            $resultValue = [5];
                            break;
                    }

                    return $resultValue;
                }
            );
        $product->expects($this->any())
            ->method('getId')
            ->willReturn(333);
        $product->expects($this->once())
            ->method('getCustomOption')
            ->willReturn($option);
        $product->expects($this->once())
            ->method('getPriceModel')
            ->willReturn($priceModel);
        $optionCollection->expects($this->once())
            ->method('getItemById')
            ->willReturn($option);
        $optionCollection->expects($this->once())
            ->method('appendSelections')
            ->with($selectionCollection, true, true);
        $productType->expects($this->once())
            ->method('setStoreFilter');

        $bundleOptions = [3 => 5];
        $buyRequest->expects($this->once())->method('getBundleOption')->willReturn($bundleOptions);

        $selectionCollection->expects($this->any())
            ->method('getItems')
            ->willReturn([$selection]);
        $selectionCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(1);
        $selection->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);
        $selection->expects($this->any())
            ->method('getOptionId')
            ->willReturn(3);
        $selection->expects($this->any())
            ->method('getOption')
            ->willReturn($option);
        $selection->expects($this->once())
            ->method('getSelectionCanChangeQty')
            ->willReturn(true);
        $selection->expects($this->once())
            ->method('getSelectionId');
        $selection->expects($this->once())
            ->method('addCustomOption')
            ->willReturnSelf();
        $selection->expects($this->any())
            ->method('getId')
            ->willReturn(333);
        $selection->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $option
            ->method('getId')
            ->willReturnOnConsecutiveCalls(333, 3, 3);
        $option->expects($this->once())
            ->method('getRequired')
            ->willReturn(false);
        $option->expects($this->once())
            ->method('isMultiSelection')
            ->willReturn(true);
        $option->expects($this->once())
            ->method('getProduct')
            ->willReturn($product);
        $option->expects($this->once())
            ->method('getValue')
            ->willReturn(4);
        $option->expects($this->once())
            ->method('getTitle')
            ->willReturn('Title for option');

        $this->arrayUtility->expects($this->once())->method('flatten')->willReturn($bundleOptions);

        $buyRequest->expects($this->once())
            ->method('getBundleOptionQty')
            ->willReturn([3 => 5]);
        $priceModel->expects($this->once())
            ->method('getSelectionFinalTotalPrice')
            ->willReturnSelf();
        $productType->expects($this->once())
            ->method('prepareForCart')
            ->willReturn('string');

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('string', $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedWithoutSelections(): void
    {
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionQty',
                    'getBundleOptionsData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId'])
            ->addMethods(['getRequired', 'isMultiSelection'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(
                [
                    'getOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setQty',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData',
                    'getId'
                ]
            )
            ->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Type $productType */
        $productType = $this->getMockBuilder(Type::class)
            ->onlyMethods(['setStoreFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->onlyMethods(['getItems', 'getItemById', 'appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $product->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($key) use ($optionCollection) {
                    $resultValue = null;
                    switch ($key) {
                        case '_cache_instance_options_collection':
                            $resultValue = $optionCollection;
                            break;
                    }

                    return $resultValue;
                }
            );
        $product->expects($this->once())
            ->method('getId')
            ->willReturn(333);
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $option
            ->method('getId')
            ->willReturnOnConsecutiveCalls(333);

        $bundleOptions = [];
        $buyRequest->expects($this->once())->method('getBundleOption')->willReturn($bundleOptions);
        $buyRequest->expects($this->once())
            ->method('getBundleOptionQty')
            ->willReturn([3 => 5]);

        $this->arrayUtility->expects($this->once())->method('flatten')->willReturn($bundleOptions);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product, 'single');
        $this->assertEquals([$product], $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedSelectionsSelectionIdsExists(): void
    {
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionsData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId'])
            ->addMethods(['getRequired', 'isMultiSelection'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->onlyMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $selection = $this->getMockBuilder(DataObject::class)
            ->addMethods(['__wakeup', 'isSalable', 'getOptionId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(
                [
                    'getOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setQty',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData'
                ]
            )
            ->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Type $productType */
        $productType = $this->getMockBuilder(Type::class)
            ->onlyMethods(['setStoreFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->onlyMethods(['getItems', 'getItemById', 'appendSelections'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $product->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($key) use ($optionCollection, $selectionCollection) {
                    $resultValue = null;
                    switch ($key) {
                        case '_cache_instance_options_collection':
                            $resultValue = $optionCollection;
                            break;
                        case '_cache_instance_used_selections':
                            $resultValue = $selectionCollection;
                            break;
                        case '_cache_instance_used_selections_ids':
                            $resultValue = [5];
                            break;
                    }

                    return $resultValue;
                }
            );
        $optionCollection->expects($this->once())
            ->method('appendSelections')
            ->with($selectionCollection, true, true);
        $productType->expects($this->once())
            ->method('setStoreFilter');

        $bundleOptions = [3 => 5];
        $buyRequest->expects($this->once())->method('getBundleOption')->willReturn($bundleOptions);

        $this->arrayUtility->expects($this->once())->method('flatten')->willReturn($bundleOptions);

        $selectionCollection
            ->method('getItems')
            ->willReturnOnConsecutiveCalls([$selection], []);
        $selectionCollection
            ->method('getSize')
            ->willReturnOnConsecutiveCalls(1, 0);
        $option->expects($this->any())
            ->method('getId')
            ->willReturn(3);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('Please specify product option(s).', $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedSelectRequiredOptions(): void
    {
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionsData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId'])
            ->addMethods(['getRequired', 'isMultiSelection'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->onlyMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $selection = $this->getMockBuilder(DataObject::class)
            ->addMethods(['__wakeup', 'isSalable', 'getOptionId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(
                [
                    'getOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setQty',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData'
                ]
            )
            ->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Type $productType */
        $productType = $this->getMockBuilder(Type::class)
            ->onlyMethods(['setStoreFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->onlyMethods(['getItems', 'getItemById'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $product->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($key) use ($optionCollection, $selectionCollection) {
                    $resultValue = null;
                    switch ($key) {
                        case '_cache_instance_options_collection':
                            $resultValue = $optionCollection;
                            break;
                        case '_cache_instance_used_selections':
                            $resultValue = $selectionCollection;
                            break;
                        case '_cache_instance_used_selections_ids':
                            $resultValue = [0 => 5];
                            break;
                    }

                    return $resultValue;
                }
            );
        $optionCollection->expects($this->once())
            ->method('getItemById')
            ->willReturn($option);
        $productType->expects($this->once())
            ->method('setStoreFilter');

        $bundleOptions = [3 => 5];
        $buyRequest->expects($this->once())->method('getBundleOption')->willReturn($bundleOptions);

        $this->arrayUtility->expects($this->once())->method('flatten')->willReturn($bundleOptions);

        $selectionCollection->expects($this->any())
            ->method('getItems')
            ->willReturn([$selection]);
        $selectionCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(1);
        $selection->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);
        $option
            ->method('getId')
            ->willReturnOnConsecutiveCalls(333, 3);
        $option->expects($this->once())
            ->method('getRequired')
            ->willReturn(true);
        $option->expects($this->once())
            ->method('isMultiSelection')
            ->willReturn(true);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('The required options you selected are not available.', $result);
    }

    /**
     * @return void
     */
    public function testPrepareForCartAdvancedParentClassReturnString(): void
    {
        $exceptedResult = 'String message';

        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getItems', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getOptions'])
            ->addMethods(['getHasOptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())
            ->method('getOptions')
            ->willThrowException(new LocalizedException(__($exceptedResult)));
        $product->expects($this->once())
            ->method('getHasOptions')
            ->willReturn(true);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);

        $this->assertEquals($exceptedResult, $result);
    }

    /**
     * @return void
     */
    public function testPrepareForCartAdvancedAllRequiredOption(): void
    {
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionsData'
                ]
            )->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId'])
            ->addMethods(['getRequired'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(
                [
                    'getOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setQty',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData'
                ]
            )->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Type $productType */
        $productType = $this->getMockBuilder(Type::class)
            ->onlyMethods(['setStoreFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->onlyMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(false);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($productType);
        $product->expects($this->once())
            ->method('hasData')
            ->willReturn(true);
        $product->expects($this->any())
            ->method('getData')
            ->willReturnCallback(
                function ($key) use ($optionCollection) {
                    $resultValue = null;
                    switch ($key) {
                        case '_cache_instance_options_collection':
                            $resultValue = $optionCollection;
                            break;
                        case '_cache_instance_used_selections_ids':
                            $resultValue = [0 => 5];
                            break;
                    }
                    return $resultValue;
                }
            );
        $optionCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$option]);
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([3 => 5]);
        $option->method('getId')
            ->willReturnOnConsecutiveCalls(3);
        $option->expects($this->once())
            ->method('getRequired')
            ->willReturn(true);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('Please select all required options.', $result);
    }

    /**
     * @return void
     */
    public function testPrepareForCartAdvancedSpecifyProductOptions(): void
    {
        /** @var MockObject|DefaultType $group */
        $group = $this->getMockBuilder(DefaultType::class)
            ->onlyMethods(['setOption', 'setProduct', 'validateUserValue', 'prepareForCart'])
            ->addMethods(['setRequest', 'setProcessMode'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->onlyMethods(['unsetData', 'getData'])
            ->addMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionsData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product\Option $option */
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['groupFactory', 'getType', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|Product $product */
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getOptions', 'prepareCustomOptions', 'addCustomOption', 'setQty'])
            ->addMethods(['getHasOptions', 'setCartQty', 'getSkipCheckRequiredOption'])
            ->disableOriginalConstructor()
            ->getMock();

        $buyRequest->method('getOptions')
            ->willReturn([333 => ['type' => 'image/jpeg']]);
        $option->method('getId')
            ->willReturn(333);
        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->any())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([0, '', 'str']);
        $group->expects($this->once())
            ->method('validateUserValue');

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('Please specify product option(s).', $result);
    }

    /**
     * @return void
     */
    public function testHasWeightTrue(): void
    {
        $this->assertTrue($this->model->hasWeight(), 'This product has no weight, but it should');
    }

    /**
     * @return void
     */
    public function testGetIdentities(): void
    {
        $identities = ['id1', 'id2'];
        $productMock = $this->createMock(Product::class);
        $optionMock = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)->addMethods(['getSelections'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionCollectionMock = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $cacheKey = '_cache_instance_options_collection';
        $productMock->expects($this->once())
            ->method('getIdentities')
            ->willReturn($identities);
        $productMock->expects($this->once())
            ->method('hasData')
            ->with($cacheKey)
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getData')
            ->with($cacheKey)
            ->willReturn($optionCollectionMock);
        $optionCollectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$optionMock]);
        $optionMock
            ->expects($this->exactly(2))
            ->method('getSelections')
            ->willReturn([$productMock]);
        $this->assertEquals($identities, $this->model->getIdentities($productMock));
    }

    /**
     * @return void
     */
    public function testGetSkuWithType(): void
    {
        $sku = 'sku';
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock
            ->method('getData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['sku'] => $sku,
                ['sku_type'] => 'some_data'
            });

        $this->assertEquals($sku, $this->model->getSku($productMock));
    }

    /**
     * @return void
     */
    public function testGetSkuWithoutType(): void
    {
        $sku = 'sku';
        $itemSku = 'item';
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->onlyMethods(['getValue'])
            ->addMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getSku', 'getEntityId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $productMock
            ->method('getCustomOption')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['option_ids'] => false,
                ['bundle_selection_ids'] => $customOptionMock
            });
        $customOptionMock->expects($this->any())
            ->method('getValue')
            ->willReturn($serializeIds);
        $selectionMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock
            ->method('getData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['sku'] => $sku,
                ['sku_type'] => null,
                ['_cache_instance_used_selections'] => $selectionMock,
                ['_cache_instance_used_selections_ids'] => $selectionIds
            });
        $selectionMock->expects(($this->any()))
            ->method('getItemByColumnValue')
            ->willReturn($selectionItemMock);
        $selectionItemMock
            ->method('getEntityId')
            ->willReturnOnConsecutiveCalls(1);
        $selectionItemMock->expects($this->once())
            ->method('getSku')
            ->willReturn($itemSku);

        $this->assertEquals($sku . '-' . $itemSku, $this->model->getSku($productMock));
    }

    /**
     * @return void
     */
    public function testGetWeightWithoutCustomOption(): void
    {
        $weight = 5;
        $productMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock
            ->method('getData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['weight_type'] => true,
                ['weight'] => $weight
            });

        $this->assertEquals($weight, $this->model->getWeight($productMock));
    }

    /**
     * @return void
     */
    public function testGetWeightWithCustomOption(): void
    {
        $weight = 5;
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->onlyMethods(['getValue'])
            ->addMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getSelectionId', 'getWeight', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($serializeIds);

        $selectionMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
        ->disableOriginalConstructor()
        ->getMock();
        $productMock
            ->method('getData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['weight_type'] => false,
                ['_cache_instance_used_selections'] => $selectionMock,
                ['_cache_instance_used_selections_ids'] => $selectionIds
            });
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$selectionItemMock]);
        $selectionItemMock->expects($this->any())
            ->method('getSelectionId')
            ->willReturn('id');
        $productMock
            ->method('getCustomOption')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['bundle_selection_ids'] => $customOptionMock,
                ['selection_qty_' . 'id'] => null
            });
        $selectionItemMock->expects($this->once())
            ->method('getWeight')
            ->willReturn($weight);

        $this->assertEquals($weight, $this->model->getWeight($productMock));
    }

    /**
     * @return void
     */
    public function testGetWeightWithSeveralCustomOption(): void
    {
        $weight = 5;
        $qtyOption = 5;
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->onlyMethods(['getValue'])
            ->addMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $qtyOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->onlyMethods(['getValue'])
            ->addMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getSelectionId', 'getWeight', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($serializeIds);

        $selectionMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
        ->disableOriginalConstructor()
        ->getMock();
        $productMock
            ->method('getData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['weight_type'] => false,
                ['_cache_instance_used_selections'] => $selectionMock,
                ['_cache_instance_used_selections_ids'] => $selectionIds
            });
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$selectionItemMock]);
        $selectionItemMock->expects($this->any())
            ->method('getSelectionId')
            ->willReturn('id');
        $productMock
            ->method('getCustomOption')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['bundle_selection_ids'] => $customOptionMock,
                ['selection_qty_' . 'id'] => $qtyOptionMock
            });
        $qtyOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($qtyOption);
        $selectionItemMock->expects($this->once())
            ->method('getWeight')
            ->willReturn($weight);

        $this->assertEquals($weight * $qtyOption, $this->model->getWeight($productMock));
    }

    /**
     * @return void
     */
    public function testIsVirtualWithoutCustomOption(): void
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(false);

        $this->assertFalse($this->model->isVirtual($productMock));
    }

    /**
     * @return void
     */
    public function testIsVirtual(): void
    {
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->onlyMethods(['getValue'])
            ->addMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['isVirtual', 'getItems', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->willReturn($customOptionMock);
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($serializeIds);

        $selectionMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock
            ->method('getData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['_cache_instance_used_selections'] => $selectionMock,
                ['_cache_instance_used_selections_ids'] => $selectionIds
            });
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$selectionItemMock]);
        $selectionItemMock->expects($this->once())
            ->method('isVirtual')
            ->willReturn(true);
        $selectionItemMock->expects($this->once())
            ->method('isVirtual')
            ->willReturn(true);
        $selectionMock->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->assertTrue($this->model->isVirtual($productMock));
    }

    /**
     * @param int $expected
     * @param int $firstId
     * @param int $secondId
     *
     * @return void
     * @dataProvider shakeSelectionsDataProvider
     */
    public function testShakeSelections($expected, $firstId, $secondId): void
    {
        $firstItemMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['__wakeup'])
            ->addMethods(['getOption', 'getOptionId', 'getPosition', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $secondItemMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['__wakeup'])
            ->addMethods(['getOption', 'getOptionId', 'getPosition', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionFirstMock = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->onlyMethods(['getPosition', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionSecondMock = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->onlyMethods(['getPosition', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $firstItemMock->expects($this->once())
            ->method('getOption')
            ->willReturn($optionFirstMock);
        $optionFirstMock->expects($this->once())
            ->method('getPosition')
            ->willReturn('option_position');
        $firstItemMock->expects($this->once())
            ->method('getOptionId')
            ->willReturn('option_id');
        $firstItemMock->expects($this->once())
            ->method('getPosition')
            ->willReturn('position');
        $firstItemMock->expects($this->once())
            ->method('getSelectionId')
            ->willReturn($firstId);
        $secondItemMock->expects($this->once())
            ->method('getOption')
            ->willReturn($optionSecondMock);
        $optionSecondMock->expects($this->any())
            ->method('getPosition')
            ->willReturn('option_position');
        $secondItemMock->expects($this->once())
            ->method('getOptionId')
            ->willReturn('option_id');
        $secondItemMock->expects($this->once())
            ->method('getPosition')
            ->willReturn('position');
        $secondItemMock->expects($this->once())
            ->method('getSelectionId')
            ->willReturn($secondId);

        $this->assertEquals($expected, $this->model->shakeSelections($firstItemMock, $secondItemMock));
    }

    /**
     * @return array
     */
    public static function shakeSelectionsDataProvider(): array
    {
        return [
            [0, 0, 0],
            [1, 1, 0],
            [-1, 0, 1]
        ];
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetSelectionsByIds(): void
    {
        $selectionIds = [1, 2, 3];
        $usedSelectionsIds = [4, 5, 6];
        $storeId = 2;
        $websiteId = 1;
        $storeFilter = 'store_filter';
        $this->expectProductEntityMetadata();
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usedSelectionsMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->onlyMethods(
                [
                    'addAttributeToSelect',
                    'setFlag',
                    'addStoreFilter',
                    'setStoreId',
                    'setPositionOrder',
                    'addFilterByRequiredOptions',
                    'setSelectionIdsFilter',
                    'joinPrices'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $productGetMap = [
            ['_cache_instance_used_selections', null, null],
            ['_cache_instance_used_selections_ids', null, $usedSelectionsIds],
            ['_cache_instance_store_filter', null, $storeFilter],
        ];
        $productMock->expects($this->any())
            ->method('getData')
            ->willReturnMap($productGetMap);
        $productSetMap = [
            ['_cache_instance_used_selections', $usedSelectionsMock, $productMock],
            ['_cache_instance_used_selections_ids', $selectionIds, $productMock],
        ];
        $productMock->expects($this->any())
            ->method('setData')
            ->willReturnMap($productSetMap);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $storeMock = $this->getMockBuilder(Store::class)
            ->onlyMethods(['getWebsiteId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->bundleCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($usedSelectionsMock);

        $usedSelectionsMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*')
            ->willReturnSelf();
        $flagMap = [
            ['product_children', true, $usedSelectionsMock],
        ];
        $usedSelectionsMock->expects($this->any())
            ->method('setFlag')
            ->willReturnMap($flagMap);
        $usedSelectionsMock->expects($this->once())
            ->method('addStoreFilter')
            ->with($storeFilter)
            ->willReturnSelf();
        $usedSelectionsMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $usedSelectionsMock->expects($this->once())
            ->method('setPositionOrder')
            ->willReturnSelf();
        $usedSelectionsMock->expects($this->once())
            ->method('addFilterByRequiredOptions')
            ->willReturnSelf();
        $usedSelectionsMock->expects($this->once())
            ->method('setSelectionIdsFilter')
            ->with($selectionIds)
            ->willReturnSelf();

        $usedSelectionsMock->expects($this->once())
            ->method('joinPrices')
            ->with($websiteId)
            ->willReturnSelf();

        $this->catalogData->expects($this->once())
            ->method('isPriceGlobal')
            ->willReturn(false);

        $this->model->getSelectionsByIds($selectionIds, $productMock);
    }

    /**
     * @return void
     */
    public function testGetOptionsByIds(): void
    {
        $optionsIds = [1, 2, 3];
        $usedOptionsIds = [4, 5, 6];
        $productId = 3;
        $storeId = 2;
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usedOptionsMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->addMethods(['getResourceCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $resourceClassName = AbstractCollection::class;
        $dbResourceMock = $this->getMockBuilder($resourceClassName)
            ->addMethods(['setProductIdFilter', 'setPositionOrder', 'joinValues', 'setIdFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(Store::class)
            ->onlyMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $this->bundleOptionFactory->expects($this->once())
            ->method('create')
            ->willReturn($usedOptionsMock);
        $usedOptionsMock->expects($this->once())
            ->method('getResourceCollection')
            ->willReturn($dbResourceMock);
        $dbResourceMock->expects($this->once())
            ->method('setProductIdFilter')
            ->with($productId)
            ->willReturnSelf();
        $dbResourceMock->expects($this->once())
            ->method('setPositionOrder')
            ->willReturnSelf();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $dbResourceMock->expects($this->once())
            ->method('joinValues')
            ->willReturnSelf();
        $dbResourceMock->expects($this->once())
            ->method('setIdFilter')
            ->with($optionsIds)
            ->willReturnSelf();
        $productMock
            ->method('getData')
            ->withConsecutive(
                ['_cache_instance_used_options'],
                ['_cache_instance_used_options_ids']
            )
            ->willReturnOnConsecutiveCalls(null, $usedOptionsIds);
        $productMock
            ->method('setData')
            ->withConsecutive(
                ['_cache_instance_used_options', $dbResourceMock],
                ['_cache_instance_used_options_ids', $optionsIds]
            )
            ->willReturnOnConsecutiveCalls($productMock, $productMock);

        $this->model->getOptionsByIds($optionsIds, $productMock);
    }

    /**
     * @return void
     */
    public function testIsSalableFalse(): void
    {
        $product = new DataObject(
            [
                'is_salable' => false,
                'status' => Status::STATUS_ENABLED
            ]
        );

        $this->assertFalse($this->model->isSalable($product));
    }

    /**
     * @return void
     */
    public function testIsSalableCache(): void
    {
        $product = new DataObject(
            [
                'is_salable' => true,
                'status' => Status::STATUS_ENABLED,
                'all_items_salable' => true
            ]
        );

        $this->assertTrue($this->model->isSalable($product));
    }

    /**
     * @param array $selectedOptions
     *
     * @return MockObject
     */
    private function getSelectionCollectionMock(array $selectedOptions): MockObject
    {
        $selectionCollectionMock = $this->getMockBuilder(
            \Magento\Bundle\Model\ResourceModel\Selection\Collection::class
        )->disableOriginalConstructor()
            ->getMock();

        $selectionCollectionMock
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($selectedOptions));

        return $selectionCollectionMock;
    }

    /**
     * @param bool $isManageStock
     *
     * @return StockItemInterface|MockObject
     */
    protected function getStockItem(bool $isManageStock): MockObject
    {
        $result = $this->getMockBuilder(StockItemInterface::class)
            ->getMock();
        $result->method('getManageStock')
            ->willReturn($isManageStock);

        return $result;
    }

    /**
     * @param MockObject|DefaultType $group
     * @param MockObject|Option $option
     * @param MockObject|DataObject $buyRequest
     * @param MockObject|Product $product
     *
     * @return void
     */
    protected function parentClass($group, $option, $buyRequest, $product): void
    {
        $group->expects($this->once())
            ->method('setOption')
            ->willReturnSelf();
        $group->expects($this->once())
            ->method('setProduct')
            ->willReturnSelf();
        $group->expects($this->once())
            ->method('setRequest')
            ->willReturnSelf();
        $group->expects($this->once())
            ->method('setProcessMode')
            ->willReturnSelf();
        $group->expects($this->once())
            ->method('prepareForCart')
            ->willReturn('someString');

        $option->expects($this->once())
            ->method('getType');
        $option->expects($this->once())
            ->method('groupFactory')
            ->willReturn($group);

        $buyRequest->expects($this->once())
            ->method('getData');
        $buyRequest->expects($this->once())
            ->method('getOptions');
        $buyRequest->expects($this->once())
            ->method('getSuperProductConfig')
            ->willReturn([]);
        $buyRequest->expects($this->any())
            ->method('unsetData')
            ->willReturnSelf();
        $buyRequest->expects($this->any())
            ->method('getQty');

        $product->expects($this->once())
            ->method('getOptions')
            ->willReturn([$option]);
        $product->expects($this->once())
            ->method('getHasOptions')
            ->willReturn(true);
        $product->expects($this->once())
            ->method('prepareCustomOptions');
        $product->expects($this->any())
            ->method('addCustomOption')
            ->willReturnSelf();
        $product->expects($this->any())
            ->method('setCartQty')
            ->willReturnSelf();
        $product->expects($this->once())
            ->method('setQty');

        $this->catalogProduct->expects($this->once())
            ->method('getSkipSaleableCheck')
            ->willReturn(false);
    }

    /**
     * @return void
     */
    public function testGetSelectionsCollection(): void
    {
        $optionIds = [1, 2, 3];
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStoreId', 'getData', 'hasData', 'setData', 'getId'])
            ->addMethods(['_wakeup'])
            ->getMock();
        $this->expectProductEntityMetadata();
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getWebsiteId'])
            ->getMock();

        $product->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $selectionCollection = $this->getSelectionCollection();
        $this->bundleCollectionFactory->expects($this->once())->method('create')->willReturn($selectionCollection);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getWebsiteId')->willReturn('website_id');
        $selectionCollection->expects($this->any())->method('joinPrices')->with('website_id')->willReturnSelf();

        $this->assertEquals($selectionCollection, $this->model->getSelectionsCollection($optionIds, $product));
    }

    /**
     * @return MockObject
     */
    private function getSelectionCollection(): MockObject
    {
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectionCollection->expects($this->any())->method('addAttributeToSelect')->willReturnSelf();
        $selectionCollection->expects($this->any())->method('setFlag')->willReturnSelf();
        $selectionCollection->expects($this->any())->method('setPositionOrder')->willReturnSelf();
        $selectionCollection->expects($this->any())->method('addStoreFilter')->willReturnSelf();
        $selectionCollection->expects($this->any())->method('setStoreId')->willReturnSelf();
        $selectionCollection->expects($this->any())->method('addFilterByRequiredOptions')->willReturnSelf();
        $selectionCollection->expects($this->any())->method('setOptionIdsFilter')->willReturnSelf();
        $selectionCollection->expects($this->any())->method('addPriceData')->willReturnSelf();
        $selectionCollection->expects($this->any())->method('addTierPriceData')->willReturnSelf();

        return $selectionCollection;
    }

    /**
     * @return void
     */
    public function testProcessBuyRequest(): void
    {
        $result = ['bundle_option' => [], 'bundle_option_qty' => []];
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $buyRequest = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getBundleOption', 'getBundleOptionQty'])
            ->getMock();

        $buyRequest->expects($this->once())->method('getBundleOption')->willReturn('bundleOption');
        $buyRequest->expects($this->once())->method('getBundleOptionQty')->willReturn('optionId');

        $this->assertEquals($result, $this->model->processBuyRequest($product, $buyRequest));
    }

    /**
     * @return void
     */
    public function testGetProductsToPurchaseByReqGroups(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->expectProductEntityMetadata();
        $resourceClassName = AbstractCollection::class;
        $dbResourceMock = $this->getMockBuilder($resourceClassName)
            ->onlyMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId', 'getRequired'])
            ->getMock();
        $selectionCollection = $this->getSelectionCollection();
        $this->bundleCollectionFactory->expects($this->once())->method('create')->willReturn($selectionCollection);

        $selectionItem = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->any())->method('hasData')->willReturn(true);
        $product
            ->method('getData')
            ->withConsecutive(['_cache_instance_options_collection'])
            ->willReturnOnConsecutiveCalls($dbResourceMock);
        $dbResourceMock->expects($this->once())->method('getItems')->willReturn([$item]);
        $item->expects($this->once())->method('getId')->willReturn('itemId');
        $item->expects($this->once())->method('getRequired')->willReturn(true);

        $selectionCollection
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$selectionItem]));
        $this->assertEquals([[$selectionItem]], $this->model->getProductsToPurchaseByReqGroups($product));
    }

    /**
     * @return void
     */
    public function testGetSearchableData(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getStoreId'])
            ->addMethods(['_wakeup', 'getHasOptions'])
            ->getMock();
        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSearchableData'])
            ->getMock();

        $product->expects($this->once())->method('getHasOptions')->willReturn(false);
        $product->expects($this->once())->method('getId')->willReturn('productId');
        $product->expects($this->once())->method('getStoreId')->willReturn('storeId');
        $this->bundleOptionFactory->expects($this->once())->method('create')->willReturn($option);
        $option->expects($this->once())->method('getSearchableData')->willReturn(['optionSearchdata']);

        $this->assertEquals(['optionSearchdata'], $this->model->getSearchableData($product));
    }

    /**
     * @return void
     */
    public function testHasOptions(): void
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasData', 'getData', 'setData', 'getId', 'getStoreId'])
            ->addMethods(['_wakeup'])
            ->getMock();
        $this->expectProductEntityMetadata();
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllIds'])
            ->getMock();
        $selectionCollection = $this->getSelectionCollection();
        $selectionCollection
            ->expects($this->any())
            ->method('getSize')
            ->willReturn(1);
        $this->bundleCollectionFactory->expects($this->once())->method('create')->willReturn($selectionCollection);

        $product->expects($this->any())->method('getStoreId')->willReturn(0);
        $product->expects($this->once())
            ->method('setData')
            ->with('_cache_instance_store_filter', 0)
            ->willReturnSelf();
        $product->expects($this->any())->method('hasData')->willReturn(true);
        $product
            ->method('getData')
            ->withConsecutive(['_cache_instance_options_collection'])
            ->willReturnOnConsecutiveCalls($optionCollection);
        $optionCollection->expects($this->once())->method('getAllIds')->willReturn(['ids']);

        $this->assertTrue($this->model->hasOptions($product));
    }

    /**
     * Bundle product without options should not be possible to buy.
     *
     * @return void
     */
    public function testCheckProductBuyStateEmptyOptionsException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Please specify product option');

        $this->mockBundleCollection();
        $product = $this->getProductMock();
        $this->expectProductEntityMetadata();
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => '[]'])],
            ['info_buyRequest', new DataObject(['value' => json_encode(['bundle_option' => ''])])]
        ]);
        $product->setCustomOption(json_encode([]));

        $this->model->checkProductBuyState($product);
    }

    /**
     * Previously selected options are not more available for buying.
     *
     * @param object $element
     * @param string $expectedMessage
     * @param bool $check
     *
     * @return void
     * @throws LocalizedException
     * @dataProvider notAvailableOptionProvider
     */
    public function testCheckProductBuyStateMissedOptionException($element, $expectedMessage, $check): void
    {
        $this->expectException(LocalizedException::class);

        $this->mockBundleCollection();
        $product = $this->getProductMock();
        $this->expectProductEntityMetadata();
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => json_encode([1])])],
            ['info_buyRequest', new DataObject(['value' => json_encode(['bundle_option' => [1]])])],
        ]);
        $product->setCustomOption(json_encode([]));

        $this->bundleCollectionFactory->method('getItemById')->willReturn($element);
        $this->catalogProduct->setSkipSaleableCheck($check);

        try {
            $this->model->checkProductBuyState($product);
        } catch (LocalizedException $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * In case of missed selection for required options, bundle product should be not able to buy.
     *
     * @return void
     */
    public function testCheckProductBuyStateRequiredOptionException(): void
    {
        $this->expectException(LocalizedException::class);

        $this->mockBundleCollection();
        $product = $this->getProductMock();
        $this->expectProductEntityMetadata();
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => json_encode([])])],
            ['info_buyRequest', new DataObject(['value' => json_encode(['bundle_option' => [1]])])],
        ]);
        $product->setCustomOption(json_encode([]));

        $falseSelection = $this->getMockBuilder(Selection::class)->disableOriginalConstructor()
            ->addMethods(['isSalable'])
            ->getMock();
        $falseSelection->method('isSalable')->willReturn(false);

        $this->bundleCollectionFactory->method('getItemById')->willReturn($falseSelection);
        $this->catalogProduct->setSkipSaleableCheck(false);

        try {
            $this->model->checkProductBuyState($product);
        } catch (LocalizedException $e) {
            $this->assertStringContainsString(
                'Please select all required options',
                $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Prepare product mock for testing.
     *
     * @return MockObject
     */
    public function getProductMock(): MockObject
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getStoreId', 'getCustomOption', 'getTypeInstance'])
            ->addMethods(['_wakeup', 'getHasOptions', 'setStoreFilter'])
            ->getMock();
        $product->method('getTypeInstance')->willReturn($product);
        $product->method('setStoreFilter')->willReturn($product);
        $optionCollectionCache = new DataObject();
        $optionCollectionCache->setAllIds([]);
        $optionCollectionCache->setItems([
            new DataObject([
                'required' => true,
                'id' => 1
            ]),
        ]);
        $product->setData('_cache_instance_options_collection', $optionCollectionCache);

        return $product;
    }

    /**
     * Preparation mocks for checkProductsBuyState.
     *
     * @return void
     */
    public function mockBundleCollection(): void
    {
        $selectionCollectionMock = $this->getSelectionCollectionMock([]);
        $this->bundleCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($selectionCollectionMock);
        $this->bundleCollectionFactory->method('create')->willReturn($selectionCollectionMock);
        $selectionCollectionMock->method('addAttributeToSelect')->willReturn($selectionCollectionMock);
        $selectionCollectionMock->method('setFlag')->willReturn($selectionCollectionMock);
        $selectionCollectionMock->method('setPositionOrder')->willReturn($selectionCollectionMock);
        $selectionCollectionMock->method('addStoreFilter')->willReturn($selectionCollectionMock);
        $selectionCollectionMock->method('setStoreId')->willReturn($selectionCollectionMock);
        $selectionCollectionMock->method('addFilterByRequiredOptions')->willReturn($selectionCollectionMock);
        $selectionCollectionMock->method('setOptionIdsFilter')->willReturn($selectionCollectionMock);
    }

    /**
     * Data provider for not available option.
     *
     * @return array
     */
    public function notAvailableOptionProvider(): array
    {
        $falseSelection = $this->getMockBuilder(Selection::class)->disableOriginalConstructor()
            ->addMethods(['isSalable'])
            ->getMock();
        $falseSelection->method('isSalable')->willReturn(false);
        return [
            [
                false,
                'The required options you selected are not available',
                false
            ],
            [
                $falseSelection,
                'The required options you selected are not available',
                false
            ]
        ];
    }

    /**
     * @return void
     */
    private function expectProductEntityMetadata(): void
    {
        $entityMetadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->onlyMethods(['getLinkField'])
            ->getMockForAbstractClass();
        $entityMetadataMock->method('getLinkField')->willReturn('test_link_field');
        $this->metadataPool->expects($this->any())->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);
    }
}
