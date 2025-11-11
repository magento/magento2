<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Model\Option as BundleOption;
use Magento\Bundle\Test\Unit\Helper\CollectionTestHelper;
use Magento\Catalog\Test\Unit\Helper\OptionTestHelper;
use Magento\Bundle\Test\Unit\Helper\SelectionTestHelper;
use Magento\Bundle\Test\Unit\Helper\TypeTestHelper;
use Magento\Catalog\Test\Unit\Helper\PriceTestHelper;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\Model\Test\Unit\Helper\AbstractCollectionTestHelper;
use Magento\Framework\DataObject\Test\Unit\Helper\DataObjectTestHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Model\OptionFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\BundleFactory;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Bundle\Model\SelectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Configuration\Item\Option as ProductConfigurationItemOption;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
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
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for bundle product type
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
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
     * @var Data|MockObject
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
     * @var MockObject|null
     */
    private $selectionCollectionMock = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->bundleCollectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->catalogData = $this->createMock(Data::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->bundleOptionFactory = $this->createPartialMock(OptionFactory::class, ['create']);
        $this->stockRegistry = $this->createPartialMock(StockRegistry::class, ['getStockItem']);
        $this->stockState = $this->createPartialMock(StockState::class, ['getStockQty']);
        $this->catalogProduct = $this->createPartialMock(
            \Magento\Catalog\Helper\Product::class,
            ['getSkipSaleableCheck']
        );
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->bundleModelSelection = $this->createPartialMock(SelectionFactory::class, ['create']);
        $this->bundleFactory = $this->createPartialMock(BundleFactory::class, ['create']);
        $this->serializer = $this->createMock(Json::class);
        // Set up serializer to use real JSON encode/decode
        $this->serializer->method('serialize')->willReturnCallback(fn ($data) => json_encode($data));
        $this->serializer->method('unserialize')->willReturnCallback(fn ($data) => json_decode($data, true));
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->arrayUtility = $this->createPartialMock(ArrayUtils::class, ['flatten']);
        $this->catalogRuleProcessor = $this->createMock(CollectionProcessor::class);

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
     * @throws Exception
     */
    public function testPrepareForCartAdvancedWithoutOptions(): void
    {
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
                'getData',
            'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory',
                'getType',
                'getId',
            'getRequired',
                'isMultiSelection']);
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->createPartialMock(SelectionCollection::class, ['getItems']);
        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getTypeInstance',
                'getStoreId',
                'hasData',
            'getData',
                'getHasOptions',
                'setCartQty',
                'getSkipCheckRequiredOption']);
        /** @var MockObject|Type $productType */
        $productType = $this->createPartialMock(Type::class, ['setStoreFilter',
                'getOptionsCollection',
            'getOptionsIds',
                'getSelectionsCollection']);
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->createPartialMock(Collection::class, ['getItems', 'getItemById',
            'appendSelections']);

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(true);
        $product->method('getTypeInstance')->willReturn($productType);
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
     * @throws Exception
     */
    public function testPrepareForCartAdvancedWithShoppingCart(): void
    {
        /** @var MockObject|PriceTestHelper $priceModel */
        $priceModel = $this->createPartialMock(PriceTestHelper::class, ['getSelectionFinalTotalPrice']);
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
                'getData',
            'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionQty',
            'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory',
                'getType',
                'getId',
            'getProduct',
                'getTitle',
                'getRequired',
                'isMultiSelection',
                'getValue']);
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->createPartialMock(SelectionCollection::class, ['getItems',
            'getSize']);
        /** @var MockObject|DataObjectTestHelper $selection */
        $selection = $this->createPartialMock(DataObjectTestHelper::class, ['isSalable',
                'getOptionId',
            'getSelectionCanChangeQty',
                'getSelectionId',
                'getOption',
                'getTypeInstance',
                'getId']);
        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getTypeInstance',
                'getStoreId',
                'hasData',
            'getData',
                'getId',
                'getCustomOption',
                'getPriceModel',
                'getHasOptions',
                'setCartQty',
            'getSkipCheckRequiredOption']);
        /** @var MockObject|TypeTestHelper $productType */
        $productType = $this->createPartialMock(TypeTestHelper::class, ['setStoreFilter',
                'prepareForCart',
                'setParentProductId',
                'addCustomOption',
                'setCartQty',
                'getSelectionId']);
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->createPartialMock(Collection::class, ['getItems', 'getItemById',
            'appendSelections']);

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(true);
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

        $product->method('getId')->willReturn(333);
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
        $selectionCollection->method('getItems')->willReturn([$selection]);
        $selectionCollection->method('getSize')->willReturn(1);
        $selection->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);
        $selection->method('getOptionId')->willReturn(3);
        $selection->method('getOption')->willReturn($option);
        $selection->expects($this->once())
            ->method('getSelectionCanChangeQty')
            ->willReturn(true);
        $selection->expects($this->once())
            ->method('getSelectionId');
        $selection->method('getId')->willReturn(333);
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
     * @throws Exception
     */
    public function testPrepareForCartAdvancedEmptyShoppingCart(): void
    {
        /** @var MockObject|PriceTestHelper $priceModel */
        $priceModel = $this->createPartialMock(PriceTestHelper::class, ['getSelectionFinalTotalPrice']);
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
            'getData',
                'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionQty',
            'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory',
                'getType',
                'getId',
            'getProduct',
                'getTitle',
                'getRequired',
                'isMultiSelection',
                'getValue']);
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->createPartialMock(SelectionCollection::class, ['getItems',
            'getSize']);
        /** @var MockObject|DataObjectTestHelper $selection */
        $selection = $this->createPartialMock(DataObjectTestHelper::class, ['isSalable',
                'getOptionId',
            'getSelectionCanChangeQty',
                'getSelectionId',
                'getOption',
                'getTypeInstance',
                'getId']);
        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getTypeInstance',
                'getStoreId',
                'hasData',
                'getData',
            'getId',
                'getCustomOption',
                'getPriceModel',
                'getHasOptions',
                'setCartQty',
                'getSkipCheckRequiredOption']);
        /** @var MockObject|Type $productType */
        $productType = $this->createPartialMock(Type::class, ['setStoreFilter', 'prepareForCart']);
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->createPartialMock(Collection::class, ['getItems', 'getItemById',
            'appendSelections']);

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(true);
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

        $product->method('getId')->willReturn(333);
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

        $selectionCollection->method('getItems')->willReturn([$selection]);
        $selectionCollection->method('getSize')->willReturn(1);
        $selection->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);
        $selection->method('getOptionId')->willReturn(3);
        $selection->method('getOption')->willReturn($option);
        $selection->expects($this->once())
            ->method('getSelectionCanChangeQty')
            ->willReturn(true);
        $selection->expects($this->once())
            ->method('getSelectionId');
        $selection->method('getId')->willReturn(333);
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
     * @throws Exception
     */
    public function testPrepareForCartAdvancedStringInResult(): void
    {
        /** @var MockObject|PriceTestHelper $priceModel */
        $priceModel = $this->createPartialMock(PriceTestHelper::class, ['getSelectionFinalTotalPrice']);
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
                'getData',
            'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionQty',
            'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory',
                'getType',
                'getId',
            'getProduct',
                'getTitle',
                'getRequired',
                'isMultiSelection',
                'getValue']);
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->createPartialMock(SelectionCollection::class, ['getItems',
            'getSize']);
        /** @var MockObject|DataObjectTestHelper $selection */
        $selection = $this->createPartialMock(DataObjectTestHelper::class, ['isSalable',
                'getOptionId',
            'getSelectionCanChangeQty',
                'getSelectionId',
                'getOption',
                'getTypeInstance',
                'getId']);
        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getTypeInstance',
                'getStoreId',
                'hasData',
                'getData',
            'getId',
                'getCustomOption',
                'getPriceModel',
                'getHasOptions',
                'setCartQty',
                'getSkipCheckRequiredOption']);
        /** @var MockObject|Type $productType */
        $productType = $this->createPartialMock(Type::class, ['setStoreFilter', 'prepareForCart']);
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->createPartialMock(Collection::class, ['getItems', 'getItemById',
            'appendSelections']);

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(true);
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
        $product->method('getId')->willReturn(333);
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

        $selectionCollection->method('getItems')->willReturn([$selection]);
        $selectionCollection->method('getSize')->willReturn(1);
        $selection->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);
        $selection->method('getOptionId')->willReturn(3);
        $selection->method('getOption')->willReturn($option);
        $selection->expects($this->once())
            ->method('getSelectionCanChangeQty')
            ->willReturn(true);
        $selection->expects($this->once())
            ->method('getSelectionId');
        $selection->method('getId')->willReturn(333);
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
     * @throws Exception
     */
    public function testPrepareForCartAdvancedWithoutSelections(): void
    {
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
                'getData',
            'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionQty',
            'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory',
                'getType',
            'getId',
                'getRequired',
                'isMultiSelection']);

        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getTypeInstance',
                'getStoreId',
                'hasData',
                'getData',
            'getId',
                'getHasOptions',
                'setCartQty',
                'getSkipCheckRequiredOption']);
        /** @var MockObject|Type $productType */
        $productType = $this->createPartialMock(Type::class, ['setStoreFilter']);
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->createPartialMock(Collection::class, ['getItems', 'getItemById',
            'appendSelections']);

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(true);
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
     * @throws Exception
     */
    public function testPrepareForCartAdvancedSelectionsSelectionIdsExists(): void
    {
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
                'getData',
            'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory',
                'getType',
                'getId',
            'getRequired',
                'isMultiSelection']);
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->createPartialMock(SelectionCollection::class, ['getItems',
            'getSize']);
        /** @var MockObject|DataObjectTestHelper $selection */
        $selection = $this->createPartialMock(DataObjectTestHelper::class, ['isSalable',
            'getOptionId']);
        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getTypeInstance',
                'getStoreId',
                'hasData',
                'getData',
            'getHasOptions',
                'setCartQty',
                'getSkipCheckRequiredOption']);
        /** @var MockObject|Type $productType */
        $productType = $this->createPartialMock(Type::class, ['setStoreFilter']);
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->createPartialMock(Collection::class, ['getItems', 'getItemById',
            'appendSelections']);

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(true);
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

        $callCount = 0;
        $selectionCollection->method('getItems')
            ->willReturnCallback(function () use (&$callCount, $selection) {
                return $callCount++ === 0 ? [$selection] : [];
            });
        $selectionCollection
            ->method('getSize')
            ->willReturnOnConsecutiveCalls(1, 0);
        $option->method('getId')->willReturn(3);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('Please specify product option(s).', $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws Exception
     */
    public function testPrepareForCartAdvancedSelectRequiredOptions(): void
    {
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
                'getData',
            'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory',
                'getType',
                'getId',
            'getRequired',
                'isMultiSelection']);
        /** @var MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->createPartialMock(SelectionCollection::class, ['getItems',
            'getSize']);
        /** @var MockObject|DataObjectTestHelper $selection */
        $selection = $this->createPartialMock(DataObjectTestHelper::class, ['isSalable',
            'getOptionId']);
        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getTypeInstance',
                'getStoreId',
                'hasData',
                'getData',
            'getHasOptions',
                'setCartQty',
                'getSkipCheckRequiredOption']);
        /** @var MockObject|Type $productType */
        $productType = $this->createPartialMock(Type::class, ['setStoreFilter']);
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->createPartialMock(Collection::class, ['getItems', 'getItemById']);

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(true);
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

        $selectionCollection->method('getItems')->willReturn([$selection]);
        $selectionCollection->method('getSize')->willReturn(1);
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
     * @throws Exception
     */
    public function testPrepareForCartAdvancedParentClassReturnString(): void
    {
        $exceptedResult = 'String message';

        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['getItems']);

        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions', 'getHasOptions']);
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws Exception
     */
    public function testPrepareForCartAdvancedAllRequiredOption(): void
    {
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
                'getData',
            'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory', 'getType', 'getId',
            'getRequired']);
        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getTypeInstance',
                'getStoreId',
                'hasData',
                'getData',
            'getHasOptions',
                'setCartQty',
                'getSkipCheckRequiredOption']);
        /** @var MockObject|Type $productType */
        $productType = $this->createPartialMock(Type::class, ['setStoreFilter']);
        /** @var MockObject|Collection $optionCollection */
        $optionCollection = $this->createPartialMock(Collection::class, ['getItems']);
        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(false);
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
        $callCount = 0;
        $option->method('getId')
            ->willReturnCallback(function () use (&$callCount) {
                return $callCount++ === 0 ? 3 : '';
            });
        $option->expects($this->once())
            ->method('getRequired')
            ->willReturn(true);
        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('Please select all required options.', $result);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testPrepareForCartAdvancedSpecifyProductOptions(): void
    {
        /** @var MockObject|DefaultType $group */
        // Use parent DefaultType class - setRequest and setProcessMode work via DataObject magic methods
        $group = $this->createPartialMock(DefaultType::class, ['setOption',
                'setProduct',
            'validateUserValue',
                'prepareForCart']);
        /** @var MockObject|DataObjectTestHelper $buyRequest */
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['unsetData',
                'getData',
            'getOptions',
                'getSuperProductConfig',
                'getQty',
                'getBundleOption',
                'getBundleOptionsData']);
        /* @var MockObject|OptionTestHelper $option */
        $option = $this->createPartialMock(OptionTestHelper::class, ['groupFactory', 'getType',
            'getId']);
        /** @var MockObject|ProductTestHelper $product */
        $product = $this->createPartialMock(ProductTestHelper::class, ['getOptions',
            'prepareCustomOptions',
                'addCustomOption',
                'setQty',
                'getHasOptions',
                'setCartQty',
            'getSkipCheckRequiredOption']);

        $buyRequest->method('getOptions')
            ->willReturn([333 => ['type' => 'image/jpeg']]);
        $option->method('getId')
            ->willReturn(333);
        $this->parentClass($group, $option, $buyRequest, $product);

        $product->method('getSkipCheckRequiredOption')->willReturn(true);
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
     * @throws Exception
     */
    public function testGetIdentities(): void
    {
        $identities = ['id1', 'id2'];
        $productMock = $this->createMock(Product::class);
        // Use partial mock - getSelections works via magic methods (needs configuration)
        $optionMock = $this->createPartialMock(BundleOption::class, []);
        // Set selections data directly since getSelections() uses getData('selections')
        $optionMock->setData('selections', [$productMock]);
        $optionCollectionMock = $this->createMock(Collection::class);
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
        $this->assertEquals($identities, $this->model->getIdentities($productMock));
    }

    /**
     * @return void
     */
    public function testGetSkuWithType(): void
    {
        $sku = 'sku';
        $productMock = $this->createMock(Product::class);
        $productMock
            ->method('getData')
            ->willReturnCallback(fn ($param) => match ([$param]) {
                ['sku'] => $sku,
                ['sku_type'] => 'some_data'
            });

        $this->assertEquals($sku, $this->model->getSku($productMock));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetSkuWithoutType(): void
    {
        $sku = 'sku';
        $itemSku = 'item';
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->createPartialMock(Product::class, ['__wakeup', 'getData', 'hasCustomOptions',
            'getCustomOption']);
        $customOptionMock = $this->createPartialMock(ProductConfigurationItemOption::class, ['getValue']);
        $selectionItemMock = $this->createPartialMock(DataObjectTestHelper::class, ['getSku',
            'getEntityId']);

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $productMock
            ->method('getCustomOption')
            ->willReturnCallback(fn ($param) => match ([$param]) {
                ['option_ids'] => false,
                ['bundle_selection_ids'] => $customOptionMock
            });
        $customOptionMock->method('getValue')->willReturn($serializeIds);
        $selectionMock = $this->createMock(SelectionCollection::class);
        $productMock
            ->method('getData')
            ->willReturnCallback(fn ($param) => match ([$param]) {
                ['sku'] => $sku,
                ['sku_type'] => null,
                ['_cache_instance_used_selections'] => $selectionMock,
                ['_cache_instance_used_selections_ids'] => $selectionIds
            });
        $selectionMock->expects(($this->any()))
            ->method('getItemByColumnValue')
            ->willReturn($selectionItemMock);
        $callCount = 0;
        $selectionItemMock->method('getEntityId')
            ->willReturnCallback(function () use (&$callCount) {
                return $callCount++ === 0 ? 1 : '';
            });
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
        $productMock = $this->createPartialMock(Product::class, ['__wakeup', 'getData']);

        $productMock
            ->method('getData')
            ->willReturnCallback(fn ($param) => match ([$param]) {
                ['weight_type'] => true,
                ['weight'] => $weight
            });

        $this->assertEquals($weight, $this->model->getWeight($productMock));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetWeightWithCustomOption(): void
    {
        $weight = 5;
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->createPartialMock(Product::class, ['__wakeup', 'getData', 'hasCustomOptions',
            'getCustomOption']);
        $customOptionMock = $this->createPartialMock(ProductConfigurationItemOption::class, ['getValue']);
        $selectionItemMock = $this->createPartialMock(DataObjectTestHelper::class, ['getSelectionId',
            'getWeight']);
        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($serializeIds);

        $selectionMock = $this->createMock(SelectionCollection::class);
        $productMock
            ->method('getData')
            ->willReturnCallback(fn ($param) => match ([$param]) {
                ['weight_type'] => false,
                ['_cache_instance_used_selections'] => $selectionMock,
                ['_cache_instance_used_selections_ids'] => $selectionIds
            });
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$selectionItemMock]);
        $selectionItemMock->method('getSelectionId')->willReturn('id');
        $productMock
            ->method('getCustomOption')
            ->willReturnCallback(function ($param) use ($customOptionMock) {
                if ($param === 'bundle_selection_ids') {
                    return $customOptionMock;
                } elseif ($param === 'selection_qty_id') {
                    return null;
                }
                return null;
            });

        $selectionItemMock->expects($this->once())
            ->method('getWeight')
            ->willReturn($weight);

        $this->assertEquals($weight, $this->model->getWeight($productMock));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetWeightWithSeveralCustomOption(): void
    {
        $weight = 5;
        $qtyOption = 5;
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->createPartialMock(Product::class, ['__wakeup', 'getData', 'hasCustomOptions',
            'getCustomOption']);
        $customOptionMock = $this->createPartialMock(ProductConfigurationItemOption::class, ['getValue']);
        $qtyOptionMock = $this->createPartialMock(ProductConfigurationItemOption::class, ['getValue']);
        $selectionItemMock = $this->createPartialMock(DataObjectTestHelper::class, ['getSelectionId',
            'getWeight']);

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(true);
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($serializeIds);

        $selectionMock = $this->createMock(SelectionCollection::class);
        $productMock
            ->method('getData')
            ->willReturnCallback(
                function ($arg) use ($selectionMock, $selectionIds) {
                    if ($arg === 'weight_type') {
                        return false;
                    } elseif ($arg === '_cache_instance_used_selections') {
                        return $selectionMock;
                    } elseif ($arg === '_cache_instance_used_selections_ids') {
                        return $selectionIds;
                    }
                }
            );
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$selectionItemMock]);
        $selectionItemMock->method('getSelectionId')->willReturn('id');
        $productMock
            ->method('getCustomOption')
            ->willReturnCallback(
                function ($arg) use ($customOptionMock, $qtyOptionMock) {
                    if ($arg === 'bundle_selection_ids') {
                        return $customOptionMock;
                    } elseif ($arg === 'selection_qty_id') {
                        return $qtyOptionMock;
                    }
                }
            );
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
        $productMock = $this->createMock(Product::class);

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->willReturn(false);

        $this->assertFalse($this->model->isVirtual($productMock));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testIsVirtual(): void
    {
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);

        $productMock = $this->createMock(Product::class);
        $customOptionMock = $this->createPartialMock(ProductConfigurationItemOption::class, ['getValue']);
        $selectionItemMock = $this->createPartialMock(DataObjectTestHelper::class, ['isVirtual',
            'getItems']);

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

        $selectionMock = $this->createMock(SelectionCollection::class);
        $productMock
            ->method('getData')
            ->willReturnCallback(
                function ($arg) use ($selectionMock, $selectionIds) {
                    if ($arg === '_cache_instance_used_selections') {
                        return $selectionMock;
                    } elseif ($arg === '_cache_instance_used_selections_ids') {
                        return $selectionIds;
                    }
                }
            );
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
        $isVirtual = (bool)$this->model->isVirtual($productMock);
        $this->assertTrue($isVirtual);
    }

    /**
     * @param int $expected
     * @param int $firstId
     * @param int $secondId
     *
     * @return void
     * @throws Exception
     */
    #[DataProvider('shakeSelectionsDataProvider')]
    public function testShakeSelections($expected, $firstId, $secondId): void
    {
        $firstItemMock = $this->createPartialMock(ProductTestHelper::class, ['getOption',
            'getOptionId', 'getPosition', 'getSelectionId']);
        $secondItemMock = $this->createPartialMock(ProductTestHelper::class, ['getOption',
            'getOptionId', 'getPosition', 'getSelectionId']);
        $optionFirstMock = $this->createPartialMock(\Magento\Bundle\Model\Option::class, ['getPosition',
            '__wakeup']);
        $optionSecondMock = $this->createPartialMock(\Magento\Bundle\Model\Option::class, ['getPosition',
            '__wakeup']);

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
        $optionSecondMock->method('getPosition')->willReturn('option_position');
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
     * @throws Exception
     */
    public function testGetSelectionsByIds(): void
    {
        $selectionIds = [1, 2, 3];
        $usedSelectionsIds = [4, 5, 6];
        $storeId = 2;
        $websiteId = 1;
        $storeFilter = 'store_filter';
        $this->expectProductEntityMetadata();
        $productMock = $this->createMock(Product::class);
        $usedSelectionsMock = $this->createPartialMock(SelectionCollection::class, [
            'addAttributeToSelect',
            'setFlag',
            'addStoreFilter',
            'setStoreId',
            'setPositionOrder',
            'addFilterByRequiredOptions',
            'setSelectionIdsFilter',
            'joinPrices'
        ]);
        $productGetMap = [
            ['_cache_instance_used_selections', null, null],
            ['_cache_instance_used_selections_ids', null, $usedSelectionsIds],
            ['_cache_instance_store_filter', null, $storeFilter],
        ];
        $productMock->expects($this->any())
            ->method('getData')
            ->willReturnMap($productGetMap);
        $productSetMap = [
            ['_cache_instance_used_selections',
                $usedSelectionsMock,
                $productMock],
            ['_cache_instance_used_selections_ids', $selectionIds, $productMock],
        ];
        $productMock->expects($this->any())
            ->method('setData')
            ->willReturnMap($productSetMap);
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $storeMock = $this->createPartialMock(Store::class, ['getWebsiteId', '__wakeup']);
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
     * @throws Exception
     */
    public function testGetOptionsByIds(): void
    {
        $optionsIds = [1, 2, 3];
        $usedOptionsIds = [4, 5, 6];
        $productId = 3;
        $storeId = 2;
        $productMock = $this->createMock(Product::class);
        // Use helper for custom getResourceCollection() method
        $usedOptionsMock = $this->createPartialMock(
            CollectionTestHelper::class,
            ['getResourceCollection']
        );
        $dbResourceMock = $this->createPartialMock(
            AbstractCollectionTestHelper::class,
            ['setProductIdFilter', 'setPositionOrder', 'joinValues', 'setIdFilter']
        );
        $storeMock = $this->createPartialMock(Store::class, ['getId', '__wakeup']);

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
            ->willReturnCallback(
                function ($arg) use ($usedOptionsIds) {
                    if ($arg === '_cache_instance_used_options') {
                        return null;
                    } elseif ($arg === '_cache_instance_used_options_ids') {
                        return $usedOptionsIds;
                    }
                }
            );
        $productMock
            ->method('setData')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($dbResourceMock, $optionsIds, $productMock) {
                    if ($arg1 === '_cache_instance_used_options' && $arg2 === $dbResourceMock) {
                        return $productMock;
                    } elseif ($arg1 === '_cache_instance_used_options_ids' && $arg2 === $optionsIds) {
                        return $productMock;
                    }
                }
            );

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
        $selectionCollectionMock = $this->createMock(SelectionCollection::class);

        $selectionCollectionMock
            ->method('getIterator')->willReturn(new \ArrayIterator($selectedOptions));

        return $selectionCollectionMock;
    }

    /**
     * @param bool $isManageStock
     *
     * @return StockItemInterface|MockObject
     */
    protected function getStockItem(bool $isManageStock): MockObject
    {
        $result = $this->createMock(StockItemInterface::class);
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
        // setRequest and setProcessMode work via DataObject magic methods - no need to configure
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
     * @throws Exception
     */
    public function testGetSelectionsCollection(): void
    {
        $optionIds = [1, 2, 3];
        $product = $this->createPartialMock(Product::class, ['getStoreId', 'getData', 'hasData', 'setData',
            'getId']);
        $this->expectProductEntityMetadata();
        $store = $this->createPartialMock(Store::class, ['getWebsiteId']);

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
        $selectionCollection = $this->createMock(SelectionCollection::class);
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
     * @throws Exception
     */
    public function testProcessBuyRequest(): void
    {
        $result = ['bundle_option' => [], 'bundle_option_qty' => []];
        $product = $this->createMock(Product::class);
        $buyRequest = $this->createPartialMock(DataObjectTestHelper::class, ['getBundleOption',
            'getBundleOptionQty']);

        $buyRequest->expects($this->once())->method('getBundleOption')->willReturn('bundleOption');
        $buyRequest->expects($this->once())->method('getBundleOptionQty')->willReturn('optionId');

        $this->assertEquals($result, $this->model->processBuyRequest($product, $buyRequest));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetProductsToPurchaseByReqGroups(): void
    {
        $product = $this->createMock(Product::class);
        $this->expectProductEntityMetadata();
        $resourceClassName = AbstractCollection::class;
        $dbResourceMock = $this->createPartialMock($resourceClassName, ['getItems']);
        $item = $this->createPartialMock(DataObjectTestHelper::class, ['getId', 'getRequired']);
        $selectionCollection = $this->getSelectionCollection();
        $this->bundleCollectionFactory->expects($this->once())->method('create')->willReturn($selectionCollection);

        $selectionItem = $this->createMock(DataObject::class);

        $product->method('hasData')->willReturn(true);
        $product
            ->method('getData')
            ->willReturnCallback(
                function ($arg) use ($dbResourceMock) {
                    if ($arg === '_cache_instance_options_collection') {
                        return $dbResourceMock;
                    }
                }
            );
        $dbResourceMock->expects($this->once())->method('getItems')->willReturn([$item]);
        $item->expects($this->once())->method('getId')->willReturn('itemId');
        $item->expects($this->once())->method('getRequired')->willReturn(true);

        $selectionCollection
            ->method('getIterator')->willReturn(new \ArrayIterator([$selectionItem]));
        $this->assertEquals([[$selectionItem]], $this->model->getProductsToPurchaseByReqGroups($product));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetSearchableData(): void
    {
        $product = $this->createPartialMock(ProductTestHelper::class, ['getId', 'getStoreId',
            'getHasOptions']);
        $option = $this->createPartialMock(\Magento\Bundle\Model\Option::class, ['getSearchableData']);

        $product->expects($this->once())->method('getHasOptions')->willReturn(false);
        $product->expects($this->once())->method('getId')->willReturn('productId');
        $product->expects($this->once())->method('getStoreId')->willReturn('storeId');
        $this->bundleOptionFactory->expects($this->once())->method('create')->willReturn($option);
        $option->expects($this->once())->method('getSearchableData')->willReturn(['optionSearchdata']);

        $this->assertEquals(['optionSearchdata'], $this->model->getSearchableData($product));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHasOptions(): void
    {
        $product = $this->createPartialMock(Product::class, ['hasData', 'getData', 'setData', 'getId',
            'getStoreId']);
        $this->expectProductEntityMetadata();
        $optionCollection = $this->createPartialMock(Collection::class, ['getAllIds']);
        $selectionCollection = $this->getSelectionCollection();
        $selectionCollection
            ->method('getSize')->willReturn(1);
        $this->bundleCollectionFactory->expects($this->once())->method('create')
            ->willReturn($selectionCollection);

        $product->method('getStoreId')->willReturn(0);
        $product->expects($this->once())
            ->method('setData')
            ->with('_cache_instance_store_filter', 0)
            ->willReturnSelf();
        $product->method('hasData')->willReturn(true);
        $product
            ->method('getData')
            ->willReturnCallback(
                function ($arg) use ($optionCollection) {
                    if ($arg === '_cache_instance_options_collection') {
                        return $optionCollection;
                    }
                }
            );

        $optionCollection->expects($this->once())->method('getAllIds')->willReturn(['ids']);

        $this->assertTrue($this->model->hasOptions($product));
    }

    /**
     * Bundle product without options should not be possible to buy.
     *
     * @return void
     * @throws Exception
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
            ['info_buyRequest',
                new DataObject(['value' => json_encode(['bundle_option' => ''])])]
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
     * @throws LocalizedException|Exception
     */
    #[DataProvider('notAvailableOptionProvider')]
    public function testCheckProductBuyStateMissedOptionException($element, $expectedMessage, $check): void
    {
        if (is_callable($element)) {
            $element = $element($this);
        }
        $this->expectException(LocalizedException::class);

        $this->mockBundleCollection();
        $product = $this->getProductMock();
        $this->expectProductEntityMetadata();
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => json_encode([1])])],
            ['info_buyRequest',
                new DataObject(['value' => json_encode(['bundle_option' => [1]])])],
        ]);
        $product->setCustomOption(json_encode([]));

        $this->selectionCollectionMock->method('getItemById')->willReturn($element);
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
     * @throws Exception
     */
    public function testCheckProductBuyStateRequiredOptionException(): void
    {
        $this->expectException(LocalizedException::class);

        $this->mockBundleCollection();
        $product = $this->getProductMock();
        $this->expectProductEntityMetadata();
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => json_encode([])])],
            ['info_buyRequest',
                new DataObject(['value' => json_encode(['bundle_option' => [1]])])],
        ]);
        $product->setCustomOption(json_encode([]));

        $falseSelection = $this->createPartialMock(SelectionTestHelper::class, ['isSalable']);
        $falseSelection->method('isSalable')->willReturn(false);

        $this->selectionCollectionMock->method('getItemById')->willReturn($falseSelection);
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
     * @throws Exception
     */
    public function getProductMock(): MockObject
    {
        $product = $this->createPartialMock(ProductTestHelper::class, ['getId',
                'getStoreId',
            'getCustomOption',
                'getTypeInstance',
                'getHasOptions',
                'setStoreFilter',
                'setCustomOption']);
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
        $this->selectionCollectionMock = $this->getSelectionCollectionMock([]);
        $this->bundleCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->selectionCollectionMock);
        $this->bundleCollectionFactory->method('create')->willReturn($this->selectionCollectionMock);
        $this->selectionCollectionMock->method('addAttributeToSelect')->willReturn($this->selectionCollectionMock);
        $this->selectionCollectionMock->method('setFlag')->willReturn($this->selectionCollectionMock);
        $this->selectionCollectionMock->method('setPositionOrder')->willReturn($this->selectionCollectionMock);
        $this->selectionCollectionMock->method('addStoreFilter')->willReturn($this->selectionCollectionMock);
        $this->selectionCollectionMock->method('setStoreId')->willReturn($this->selectionCollectionMock);
        $this->selectionCollectionMock->method('addFilterByRequiredOptions')
            ->willReturn($this->selectionCollectionMock);
        $this->selectionCollectionMock->method('setOptionIdsFilter')->willReturn($this->selectionCollectionMock);
    }

    protected function getMockForSectionClass()
    {
        $falseSelection = $this->createPartialMock(SelectionTestHelper::class, ['isSalable']);
        $falseSelection->method('isSalable')->willReturn(false);
        return $falseSelection;
    }

    /**
     * Data provider for not available option.
     *
     * @return array
     */
    public static function notAvailableOptionProvider(): array
    {
        $falseSelection = static fn (self $testCase) => $testCase->getMockForSectionClass();

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
     * @throws Exception
     */
    private function expectProductEntityMetadata(): void
    {
        $entityMetadataMock = $this->createMock(EntityMetadataInterface::class);
        $entityMetadataMock->method('getLinkField')->willReturn('test_link_field');
        $this->metadataPool->expects($this->any())->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($entityMetadataMock);
    }
}
