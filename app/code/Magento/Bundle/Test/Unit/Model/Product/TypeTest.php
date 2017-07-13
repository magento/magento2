<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Bundle\Model\Selection;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class TypeTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Model\ResourceModel\BundleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bundleFactory;

    /**
     * @var \Magento\Bundle\Model\SelectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bundleModelSelection;

    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $model;

    /**
     * @var \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Bundle\Model\OptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $bundleOptionFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockState;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogProduct;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->bundleCollectionFactory =
            $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory::class)
            ->setMethods(
                [
                    'create',
                    'addAttributeToSelect',
                    'setFlag',
                    'setPositionOrder',
                    'addStoreFilter',
                    'setStoreId',
                    'addFilterByRequiredOptions',
                    'setOptionIdsFilter',
                    'getItemById'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogData = $this->getMockBuilder(\Magento\Catalog\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleOptionFactory = $this->getMockBuilder(\Magento\Bundle\Model\OptionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->setMethods(['getStockItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockState = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockState::class)
            ->setMethods(['getStockQty'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProduct = $this->getMockBuilder(\Magento\Catalog\Helper\Product::class)
            ->setMethods(['getSkipSaleableCheck'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->setMethods(['convert'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->bundleModelSelection = $this->getMockBuilder(\Magento\Bundle\Model\SelectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->bundleFactory = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\BundleFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->catalogRuleProcessor = $this->getMockBuilder(
            \Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockBuilder(Json::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject(
            \Magento\Bundle\Model\Product\Type::class,
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
            ]
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedWithoutOptions()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                ['__wakeup', 'getOptions', 'getSuperProductConfig', 'unsetData', 'getData', 'getQty', 'getBundleOption']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(['groupFactory', 'getType', 'getId', 'getRequired', 'isMultiSelection'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Product\Type $productType */
        $productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(['setStoreFilter', 'getOptionsCollection', 'getOptionsIds', 'getSelectionsCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getItems', 'getItemById', 'appendSelections'])
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
        $option->expects($this->at(3))
            ->method('getId')
            ->willReturn(3);
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
    public function testPrepareForCartAdvancedWithShoppingCart()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Type\Price $priceModel */
        $priceModel = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Price::class)
            ->setMethods(['getSelectionFinalTotalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'unsetData',
                    'getData',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionQty'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(
                [
                    'groupFactory',
                    'getType',
                    'getId',
                    'getRequired',
                    'isMultiSelection',
                    'getProduct',
                    'getValue',
                    'getTitle'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->setMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $selection = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData',
                    'getId',
                    'getCustomOption',
                    'getPriceModel'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Product\Type $productType */
        $productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(
                [
                    'setStoreFilter',
                    'prepareForCart',
                    'setParentProductId',
                    'addCustomOption',
                    'setCartQty',
                    'getSelectionId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getItems', 'getItemById', 'appendSelections'])
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
            ->method('appendSelections');
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([3 => 5]);
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
        $option->expects($this->at(3))
            ->method('getId')
            ->willReturn(3);
        $option->expects($this->at(9))
            ->method('getId')
            ->willReturn(3);
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

        $this->priceCurrency->expects($this->once())
            ->method('convert')
            ->willReturn(3.14);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals([$product, $productType], $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedEmptyShoppingCart()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Type\Price $priceModel */
        $priceModel = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Price::class)
            ->setMethods(['getSelectionFinalTotalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'unsetData',
                    'getData',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionQty'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(
                [
                    'groupFactory',
                    'getType',
                    'getId',
                    'getRequired',
                    'isMultiSelection',
                    'getProduct',
                    'getValue',
                    'getTitle'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->setMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $selection = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData',
                    'getId',
                    'getCustomOption',
                    'getPriceModel'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Product\Type $productType */
        $productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(['setStoreFilter', 'prepareForCart'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getItems', 'getItemById', 'appendSelections'])
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
            ->method('appendSelections');
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([3 => 5]);
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
        $option->expects($this->at(3))
            ->method('getId')
            ->willReturn(3);
        $option->expects($this->at(9))
            ->method('getId')
            ->willReturn(3);
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

        $this->priceCurrency->expects($this->once())
            ->method('convert')
            ->willReturn(3.14);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('We can\'t add this item to your shopping cart right now.', $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedStringInResult()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Type\Price $priceModel */
        $priceModel = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\Price::class)
            ->setMethods(['getSelectionFinalTotalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'unsetData',
                    'getData',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionQty'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(
                [
                    'groupFactory',
                    'getType',
                    'getId',
                    'getRequired',
                    'isMultiSelection',
                    'getProduct',
                    'getValue',
                    'getTitle'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->setMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $selection = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData',
                    'getId',
                    'getCustomOption',
                    'getPriceModel'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Product\Type $productType */
        $productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(['setStoreFilter', 'prepareForCart'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getItems', 'getItemById', 'appendSelections'])
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
            ->method('appendSelections');
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([3 => 5]);
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
        $option->expects($this->at(3))
            ->method('getId')
            ->willReturn(3);
        $option->expects($this->at(9))
            ->method('getId')
            ->willReturn(3);
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
            ->willReturn('string');

        $this->priceCurrency->expects($this->once())
            ->method('convert')
            ->willReturn(3.14);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('string', $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedWithoutSelections()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                [
                    '__wakeup',
                    'getOptions',
                    'getSuperProductConfig',
                    'unsetData',
                    'getData',
                    'getQty',
                    'getBundleOption',
                    'getBundleOptionQty'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(['groupFactory', 'getType', 'getId', 'getRequired', 'isMultiSelection'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData',
                    'getId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Product\Type $productType */
        $productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(['setStoreFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getItems', 'getItemById', 'appendSelections'])
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
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([]);
        $buyRequest->expects($this->once())
            ->method('getBundleOptionQty')
            ->willReturn([3 => 5]);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product, 'single');
        $this->assertEquals([$product], $result);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testPrepareForCartAdvancedSelectionsSelectionIdsExists()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                ['__wakeup', 'getOptions', 'getSuperProductConfig', 'unsetData', 'getData', 'getQty', 'getBundleOption']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(['groupFactory', 'getType', 'getId', 'getRequired', 'isMultiSelection'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->setMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $selection = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['__wakeup', 'isSalable', 'getOptionId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Product\Type $productType */
        $productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(['setStoreFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getItems', 'getItemById', 'appendSelections'])
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
            ->method('appendSelections');
        $productType->expects($this->once())
            ->method('setStoreFilter');
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([3 => 5]);
        $selectionCollection->expects($this->at(0))
            ->method('getItems')
            ->willReturn([$selection]);
        $selectionCollection->expects($this->at(0))
            ->method('getSize')
            ->willReturn(1);
        $selectionCollection->expects($this->at(1))
            ->method('getItems')
            ->willReturn([]);
        $selectionCollection->expects($this->at(1))
            ->method('getSize')
            ->willReturn(0);
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
    public function testPrepareForCartAdvancedSelectRequiredOptions()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                ['__wakeup', 'getOptions', 'getSuperProductConfig', 'unsetData', 'getData', 'getQty', 'getBundleOption']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(['groupFactory', 'getType', 'getId', 'getRequired', 'isMultiSelection'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|SelectionCollection $selectionCollection */
        $selectionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->setMethods(['getItems', 'getSize'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $selection = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['__wakeup', 'isSalable', 'getOptionId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Product\Type $productType */
        $productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(['setStoreFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getItems', 'getItemById'])
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
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([3 => 5]);
        $selectionCollection->expects($this->any())
            ->method('getItems')
            ->willReturn([$selection]);
        $selectionCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(1);
        $selection->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);
        $option->expects($this->at(3))
            ->method('getId')
            ->willReturn(3);
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
    public function testPrepareForCartAdvancedParentClassReturnString()
    {
        $exceptedResult = 'String message';

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getItems', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions'
                ]
            )
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
    public function testPrepareForCartAdvancedAllrequiredOption()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                ['__wakeup', 'getOptions', 'getSuperProductConfig', 'unsetData', 'getData', 'getQty', 'getBundleOption']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(['groupFactory', 'getType', 'getId', 'getRequired'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption',
                    'getTypeInstance',
                    'getStoreId',
                    'hasData',
                    'getData'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Bundle\Model\Product\Type $productType */
        $productType = $this->getMockBuilder(\Magento\Bundle\Model\Product\Type::class)
            ->setMethods(['setStoreFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|Collection $optionCollection */
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getItems'])
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
        $option->expects($this->at(3))
            ->method('getId')
            ->willReturn(3);
        $option->expects($this->once())
            ->method('getRequired')
            ->willReturn(true);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('Please select all required options.', $result);
    }

    /**
     * @return void
     */
    public function testPrepareForCartAdvancedSpecifyProductOptions()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DefaultType $group */
        $group = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(
                ['setOption', 'setProduct', 'setRequest', 'setProcessMode', 'validateUserValue', 'prepareForCart']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest */
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
                ['__wakeup', 'getOptions', 'getSuperProductConfig', 'unsetData', 'getData', 'getQty', 'getBundleOption']
            )
            ->disableOriginalConstructor()
            ->getMock();
        /* @var $option \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option */
        $option = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option::class)
            ->setMethods(['groupFactory', 'getType', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(
                [
                    'getOptions',
                    'getHasOptions',
                    'prepareCustomOptions',
                    'addCustomOption',
                    'setCartQty',
                    'setQty',
                    'getSkipCheckRequiredOption'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->parentClass($group, $option, $buyRequest, $product);

        $product->expects($this->once())
            ->method('getSkipCheckRequiredOption')
            ->willReturn(true);
        $buyRequest->expects($this->once())
            ->method('getBundleOption')
            ->willReturn([0, '', 'str']);

        $result = $this->model->prepareForCartAdvanced($buyRequest, $product);
        $this->assertEquals('Please specify product option(s).', $result);
    }

    /**
     * @return void
     */
    public function testHasWeightTrue()
    {
        $this->assertTrue($this->model->hasWeight(), 'This product has no weight, but it should');
    }

    /**
     * @return void
     */
    public function testGetIdentities()
    {
        $identities = ['id1', 'id2'];
        $productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $optionMock = $this->createPartialMock(\Magento\Bundle\Model\Option::class, ['getSelections', '__wakeup']);
        $optionCollectionMock = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $cacheKey = '_cache_instance_options_collection';
        $productMock->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($identities));
        $productMock->expects($this->once())
            ->method('hasData')
            ->with($cacheKey)
            ->will($this->returnValue(true));
        $productMock->expects($this->once())
            ->method('getData')
            ->with($cacheKey)
            ->will($this->returnValue($optionCollectionMock));
        $optionCollectionMock
            ->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$optionMock]));
        $optionMock
            ->expects($this->exactly(2))
            ->method('getSelections')
            ->will($this->returnValue([$productMock]));
        $this->assertEquals($identities, $this->model->getIdentities($productMock));
    }

    /**
     * @return void
     */
    public function testGetSkuWithType()
    {
        $sku = 'sku';
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('sku')
            ->will($this->returnValue($sku));
        $productMock->expects($this->at(2))
            ->method('getData')
            ->with('sku_type')
            ->will($this->returnValue('some_data'));

        $this->assertEquals($sku, $this->model->getSku($productMock));
    }

    /**
     * @return void
     */
    public function testGetSkuWithoutType()
    {
        $sku = 'sku';
        $itemSku = 'item';
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getSku', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('sku')
            ->will($this->returnValue($sku));
        $productMock->expects($this->at(1))
            ->method('getCustomOption')
            ->with('option_ids')
            ->will($this->returnValue(false));
        $productMock->expects($this->at(2))
            ->method('getData')
            ->with('sku_type')
            ->will($this->returnValue(null));
        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(true));
        $productMock->expects($this->at(4))
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->will($this->returnValue($customOptionMock));
        $customOptionMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($serializeIds));
        $selectionMock = $this->getSelectionsByIdsMock($selectionIds, $productMock, 5, 6);
        $selectionMock->expects(($this->any()))
            ->method('getItems')
            ->will($this->returnValue([$selectionItemMock]));
        $selectionItemMock->expects($this->any())
            ->method('getSku')
            ->will($this->returnValue($itemSku));

        $this->assertEquals($sku . '-' . $itemSku, $this->model->getSku($productMock));
    }

    /**
     * @return void
     */
    public function testGetWeightWithoutCustomOption()
    {
        $weight = 5;
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('weight_type')
            ->will($this->returnValue(true));
        $productMock->expects($this->at(1))
            ->method('getData')
            ->with('weight')
            ->will($this->returnValue($weight));

        $this->assertEquals($weight, $this->model->getWeight($productMock));
    }

    /**
     * @return void
     */
    public function testGetWeightWithCustomOption()
    {
        $weight = 5;
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getSelectionId', 'getWeight', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('weight_type')
            ->will($this->returnValue(false));
        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(true));
        $productMock->expects($this->at(2))
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->will($this->returnValue($customOptionMock));
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($serializeIds));
        $selectionMock = $this->getSelectionsByIdsMock($selectionIds, $productMock, 3, 4);
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$selectionItemMock]));
        $selectionItemMock->expects($this->any())
            ->method('getSelectionId')
            ->will($this->returnValue('id'));
        $productMock->expects($this->at(5))
            ->method('getCustomOption')
            ->with('selection_qty_' . 'id')
            ->will($this->returnValue(null));
        $selectionItemMock->expects($this->once())
            ->method('getWeight')
            ->will($this->returnValue($weight));

        $this->assertEquals($weight, $this->model->getWeight($productMock));
    }

    /**
     * @return void
     */
    public function testGetWeightWithSeveralCustomOption()
    {
        $weight = 5;
        $qtyOption = 5;
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getData', 'hasCustomOptions', 'getCustomOption'])
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $qtyOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['getSelectionId', 'getWeight', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('weight_type')
            ->will($this->returnValue(false));
        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(true));
        $productMock->expects($this->at(2))
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->will($this->returnValue($customOptionMock));
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($serializeIds));
        $selectionMock = $this->getSelectionsByIdsMock($selectionIds, $productMock, 3, 4);
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$selectionItemMock]));
        $selectionItemMock->expects($this->any())
            ->method('getSelectionId')
            ->will($this->returnValue('id'));
        $productMock->expects($this->at(5))
            ->method('getCustomOption')
            ->with('selection_qty_' . 'id')
            ->will($this->returnValue($qtyOptionMock));
        $qtyOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($qtyOption));
        $selectionItemMock->expects($this->once())
            ->method('getWeight')
            ->will($this->returnValue($weight));

        $this->assertEquals($weight * $qtyOption, $this->model->getWeight($productMock));
    }

    /**
     * @return void
     */
    public function testIsVirtualWithoutCustomOption()
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(false));

        $this->assertFalse($this->model->isVirtual($productMock));
    }

    /**
     * @return void
     */
    public function testIsVirtual()
    {
        $selectionIds = [1, 2, 3];
        $serializeIds = json_encode($selectionIds);

        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customOptionMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option::class)
            ->setMethods(['getValue', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectionItemMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(['isVirtual', 'getItems', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->once())
            ->method('hasCustomOptions')
            ->will($this->returnValue(true));
        $productMock->expects($this->once())
            ->method('getCustomOption')
            ->with('bundle_selection_ids')
            ->will($this->returnValue($customOptionMock));
        $customOptionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($serializeIds));
        $selectionMock = $this->getSelectionsByIdsMock($selectionIds, $productMock, 2, 3);
        $selectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$selectionItemMock]));
        $selectionItemMock->expects($this->once())
            ->method('isVirtual')
            ->will($this->returnValue(true));
        $selectionItemMock->expects($this->once())
            ->method('isVirtual')
            ->will($this->returnValue(true));
        $selectionMock->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $this->assertTrue($this->model->isVirtual($productMock));
    }

    /**
     * @param array $selectionIds
     * @param \PHPUnit_Framework_MockObject_MockObject $productMock
     * @param int $getSelectionsIndex
     * @param int $getSelectionsIdsIndex
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getSelectionsByIdsMock($selectionIds, $productMock, $getSelectionsIndex, $getSelectionsIdsIndex)
    {
        $usedSelectionsMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at($getSelectionsIndex))
            ->method('getData')
            ->with('_cache_instance_used_selections')
            ->will($this->returnValue($usedSelectionsMock));
        $productMock->expects($this->at($getSelectionsIdsIndex))
            ->method('getData')
            ->with('_cache_instance_used_selections_ids')
            ->will($this->returnValue($selectionIds));

        return $usedSelectionsMock;
    }

    /**
     * @param int $expected
     * @param int $firstId
     * @param int $secondId
     * @return void
     * @dataProvider shakeSelectionsDataProvider
     */
    public function testShakeSelections($expected, $firstId, $secondId)
    {
        $firstItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getOption', 'getOptionId', 'getPosition', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $secondItemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['__wakeup', 'getOption', 'getOptionId', 'getPosition', 'getSelectionId'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionFirstMock = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->setMethods(['getPosition', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionSecondMock = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->setMethods(['getPosition', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $firstItemMock->expects($this->once())
            ->method('getOption')
            ->will($this->returnValue($optionFirstMock));
        $optionFirstMock->expects($this->once())
            ->method('getPosition')
            ->will($this->returnValue('option_position'));
        $firstItemMock->expects($this->once())
            ->method('getOptionId')
            ->will($this->returnValue('option_id'));
        $firstItemMock->expects($this->once())
            ->method('getPosition')
            ->will($this->returnValue('position'));
        $firstItemMock->expects($this->once())
            ->method('getSelectionId')
            ->will($this->returnValue($firstId));
        $secondItemMock->expects($this->once())
            ->method('getOption')
            ->will($this->returnValue($optionSecondMock));
        $optionSecondMock->expects($this->any())
            ->method('getPosition')
            ->will($this->returnValue('option_position'));
        $secondItemMock->expects($this->once())
            ->method('getOptionId')
            ->will($this->returnValue('option_id'));
        $secondItemMock->expects($this->once())
            ->method('getPosition')
            ->will($this->returnValue('position'));
        $secondItemMock->expects($this->once())
            ->method('getSelectionId')
            ->will($this->returnValue($secondId));

        $this->assertEquals($expected, $this->model->shakeSelections($firstItemMock, $secondItemMock));
    }

    /**
     * @return array
     */
    public function shakeSelectionsDataProvider()
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
    public function testGetSelectionsByIds()
    {
        $selectionIds = [1, 2, 3];
        $usedSelectionsIds = [4, 5, 6];
        $storeId = 2;
        $websiteId = 1;
        $storeFilter = 'store_filter';
        $this->expectProductEntityMetadata();
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usedSelectionsMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Selection\Collection::class)
            ->setMethods(
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
            ->will($this->returnValueMap($productGetMap));
        $productSetMap = [
            ['_cache_instance_used_selections', $usedSelectionsMock, $productMock],
            ['_cache_instance_used_selections_ids', $selectionIds, $productMock],
        ];
        $productMock->expects($this->any())
            ->method('setData')
            ->will($this->returnValueMap($productSetMap));
        $productMock->expects($this->once())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getWebsiteId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->will($this->returnValue($websiteId));

        $this->bundleCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($usedSelectionsMock));

        $usedSelectionsMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*')
            ->will($this->returnSelf());
        $flagMap = [
            ['product_children', true, $usedSelectionsMock],
        ];
        $usedSelectionsMock->expects($this->any())
            ->method('setFlag')
            ->will($this->returnValueMap($flagMap));
        $usedSelectionsMock->expects($this->once())
            ->method('addStoreFilter')
            ->with($storeFilter)
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->once())
            ->method('setPositionOrder')
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->once())
            ->method('addFilterByRequiredOptions')
            ->will($this->returnSelf());
        $usedSelectionsMock->expects($this->once())
            ->method('setSelectionIdsFilter')
            ->with($selectionIds)
            ->will($this->returnSelf());

        $usedSelectionsMock->expects($this->once())
            ->method('joinPrices')
            ->with($websiteId)
            ->will($this->returnSelf());

        $this->catalogData->expects($this->once())
            ->method('isPriceGlobal')
            ->will($this->returnValue(false));

        $this->model->getSelectionsByIds($selectionIds, $productMock);
    }

    /**
     * @return void
     */
    public function testGetOptionsByIds()
    {
        $optionsIds = [1, 2, 3];
        $usedOptionsIds = [4, 5, 6];
        $productId = 3;
        $storeId = 2;
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $usedOptionsMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getResourceCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $resourceClassName = \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class;
        $dbResourceMock = $this->getMockBuilder($resourceClassName)
            ->setMethods(['setProductIdFilter', 'setPositionOrder', 'joinValues', 'setIdFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $productMock->expects($this->at(0))
            ->method('getData')
            ->with('_cache_instance_used_options')
            ->will($this->returnValue(null));
        $productMock->expects($this->at(1))
            ->method('getData')
            ->with('_cache_instance_used_options_ids')
            ->will($this->returnValue($usedOptionsIds));
        $productMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($productId));
        $this->bundleOptionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($usedOptionsMock));
        $usedOptionsMock->expects($this->once())
            ->method('getResourceCollection')
            ->will($this->returnValue($dbResourceMock));
        $dbResourceMock->expects($this->once())
            ->method('setProductIdFilter')
            ->with($productId)
            ->will($this->returnSelf());
        $dbResourceMock->expects($this->once())
            ->method('setPositionOrder')
            ->will($this->returnSelf());
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $storeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($storeId));
        $dbResourceMock->expects($this->once())
            ->method('joinValues')
            ->will($this->returnSelf());
        $dbResourceMock->expects($this->once())
            ->method('setIdFilter')
            ->with($optionsIds)
            ->will($this->returnSelf());
        $productMock->expects($this->at(3))
            ->method('setData')
            ->with('_cache_instance_used_options', $dbResourceMock)
            ->will($this->returnSelf());
        $productMock->expects($this->at(4))
            ->method('setData')
            ->with('_cache_instance_used_options_ids', $optionsIds)
            ->will($this->returnSelf());

        $this->model->getOptionsByIds($optionsIds, $productMock);
    }

    /**
     * @return void
     */
    public function testIsSalableFalse()
    {
        $product = new \Magento\Framework\DataObject(
            [
                'is_salable' => false,
                'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            ]
        );

        $this->assertFalse($this->model->isSalable($product));
    }

    /**
     * @return void
     */
    public function testIsSalableWithoutOptions()
    {
        $optionCollectionMock = $this->getOptionCollectionMock([]);
        $product = new \Magento\Framework\DataObject(
            [
                'is_salable' => true,
                '_cache_instance_options_collection' => $optionCollectionMock,
                'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            ]
        );

        $this->assertFalse($this->model->isSalable($product));
    }

    /**
     * @return void
     */
    public function testIsSalableWithRequiredOptionsTrue()
    {
        $option1 = $this->getRequiredOptionMock(10, 10);
        $option2 = $this->getRequiredOptionMock(20, 10);

        $option3 = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->setMethods(['getRequired', 'getOptionId', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $option3->method('getRequired')
            ->willReturn(false);
        $option3->method('getOptionId')
            ->willReturn(30);
        $option3->method('getId')
            ->willReturn(30);

        $this->expectProductEntityMetadata();

        $optionCollectionMock = $this->getOptionCollectionMock([$option1, $option2, $option3]);
        $selectionCollectionMock = $this->getSelectionCollectionMock([$option1, $option2]);
        $this->bundleCollectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($selectionCollectionMock));

        $product = new \Magento\Framework\DataObject(
            [
                'is_salable' => true,
                '_cache_instance_options_collection' => $optionCollectionMock,
                'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            ]
        );

        $this->assertTrue($this->model->isSalable($product));
    }

    /**
     * @return void
     */
    public function testIsSalableCache()
    {
        $product = new \Magento\Framework\DataObject(
            [
                'is_salable' => true,
                'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                'all_items_salable' => true
            ]
        );

        $this->assertTrue($this->model->isSalable($product));
    }

    /**
     * @return void
     */
    public function testIsSalableWithEmptySelectionsCollection()
    {
        $option = $this->getRequiredOptionMock(1, 10);
        $optionCollectionMock = $this->getOptionCollectionMock([$option]);
        $selectionCollectionMock = $this->getSelectionCollectionMock([]);
        $this->expectProductEntityMetadata();

        $this->bundleCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($selectionCollectionMock));

        $product = new \Magento\Framework\DataObject(
            [
                'is_salable' => true,
                '_cache_instance_options_collection' => $optionCollectionMock,
                'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            ]
        );

        $this->assertFalse($this->model->isSalable($product));
    }

    /**
     * @return void
     */
    public function testIsSalableWithNonSalableRequiredOptions()
    {
        $option1 = $this->getRequiredOptionMock(10, 10);
        $option2 = $this->getRequiredOptionMock(20, 10);
        $optionCollectionMock = $this->getOptionCollectionMock([$option1, $option2]);
        $this->expectProductEntityMetadata();

        $selection1 = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['isSalable'])
            ->disableOriginalConstructor()
            ->getMock();

        $selection1->expects($this->once())
            ->method('isSalable')
            ->willReturn(true);

        $selection2 = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['isSalable'])
            ->disableOriginalConstructor()
            ->getMock();

        $selection2->expects($this->once())
            ->method('isSalable')
            ->willReturn(false);

        $selectionCollectionMock1 = $this->getSelectionCollectionMock([$selection1]);
        $selectionCollectionMock2 = $this->getSelectionCollectionMock([$selection2]);

        $this->bundleCollectionFactory->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls(
                $selectionCollectionMock1,
                $selectionCollectionMock2
            ));

        $product = new \Magento\Framework\DataObject(
            [
                'is_salable' => true,
                '_cache_instance_options_collection' => $optionCollectionMock,
                'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
            ]
        );

        $this->assertFalse($this->model->isSalable($product));
    }

    /**
     * @param int $id
     * @param int $selectionQty
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getRequiredOptionMock($id, $selectionQty)
    {
        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->setMethods(
                [
                    'getRequired',
                    'isSalable',
                    'hasSelectionQty',
                    'getSelectionQty',
                    'getOptionId',
                    'getId',
                    'getSelectionCanChangeQty'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $option->method('getRequired')
            ->willReturn(true);
        $option->method('isSalable')
            ->willReturn(true);
        $option->method('hasSelectionQty')
            ->willReturn(true);
        $option->method('getSelectionQty')
            ->willReturn($selectionQty);
        $option->method('getOptionId')
            ->willReturn($id);
        $option->method('getSelectionCanChangeQty')
            ->willReturn(false);
        $option->method('getId')
            ->willReturn($id);

        return $option;
    }

    /**
     * @param array $selectedOptions
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSelectionCollectionMock(array $selectedOptions)
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
     * @param array $options
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getOptionCollectionMock(array $options)
    {
        $optionCollectionMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->setMethods(['getIterator'])
            ->disableOriginalConstructor()
            ->getMock();

        $optionCollectionMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator($options)));

        return $optionCollectionMock;
    }

    /**
     * @param bool $isManageStock
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStockItem($isManageStock)
    {
        $result = $this->getMockBuilder(\Magento\CatalogInventory\Api\Data\StockItemInterface::class)
            ->getMock();
        $result->method('getManageStock')
            ->willReturn($isManageStock);

        return $result;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|DefaultType $group
     * @param \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product\Option $option
     * @param \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject $buyRequest
     * @param \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function parentClass($group, $option, $buyRequest, $product)
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
            ->method('validateUserValue')
            ->willReturnSelf();
        $group->expects($this->once())
            ->method('prepareForCart')
            ->willReturn('someString');

        $option->expects($this->once())
            ->method('getType');
        $option->expects($this->once())
            ->method('groupFactory')
            ->willReturn($group);
        $option->expects($this->at(0))
            ->method('getId')
            ->willReturn(333);

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

    public function testGetSelectionsCollection()
    {
        $optionIds = [1, 2, 3];
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    '_wakeup',
                    'getStoreId',
                    'getData',
                    'hasData',
                    'setData',
                    'getId'
                ]
            )
            ->getMock();
        $this->expectProductEntityMetadata();
        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSelectionCollection()
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

    public function testProcessBuyRequest()
    {
        $result = ['bundle_option' => [], 'bundle_option_qty' => []];
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $buyRequest = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBundleOption', 'getBundleOptionQty'])
            ->getMock();

        $buyRequest->expects($this->once())->method('getBundleOption')->willReturn('bundleOption');
        $buyRequest->expects($this->once())->method('getBundleOptionQty')->willReturn('optionId');

        $this->assertEquals($result, $this->model->processBuyRequest($product, $buyRequest));
    }

    public function testGetProductsToPurchaseByReqGroups()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->expectProductEntityMetadata();
        $resourceClassName = \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::class;
        $dbResourceMock = $this->getMockBuilder($resourceClassName)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getRequired'])
            ->getMock();
        $selectionCollection = $this->getSelectionCollection();
        $this->bundleCollectionFactory->expects($this->once())->method('create')->willReturn($selectionCollection);

        $selectionItem = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $product->expects($this->any())->method('hasData')->willReturn(true);
        $product->expects($this->at(1))
            ->method('getData')
            ->with('_cache_instance_options_collection')
            ->willReturn($dbResourceMock);
        $dbResourceMock->expects($this->once())->method('getItems')->willReturn([$item]);
        $item->expects($this->once())->method('getId')->willReturn('itemId');
        $item->expects($this->once())->method('getRequired')->willReturn(true);

        $selectionCollection
            ->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$selectionItem]));
        $this->assertEquals([[$selectionItem]], $this->model->getProductsToPurchaseByReqGroups($product));
    }

    public function testGetSearchableData()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['_wakeup', 'getHasOptions', 'getId', 'getStoreId'])
            ->getMock();
        $option = $this->getMockBuilder(\Magento\Bundle\Model\Option::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSearchableData'])
            ->getMock();

        $product->expects($this->once())->method('getHasOptions')->willReturn(false);
        $product->expects($this->once())->method('getId')->willReturn('productId');
        $product->expects($this->once())->method('getStoreId')->willReturn('storeId');
        $this->bundleOptionFactory->expects($this->once())->method('create')->willReturn($option);
        $option->expects($this->once())->method('getSearchableData')->willReturn(['optionSearchdata']);

        $this->assertEquals(['optionSearchdata'], $this->model->getSearchableData($product));
    }

    public function testHasOptions()
    {
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['_wakeup', 'hasData', 'getData', 'setData', 'getId', 'getStoreId'])
            ->getMock();
        $this->expectProductEntityMetadata();
        $optionCollection = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllIds'])
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
        $product->expects($this->at(3))
            ->method('getData')
            ->with('_cache_instance_options_collection')
            ->willReturn($optionCollection);
        $optionCollection->expects($this->once())->method('getAllIds')->willReturn(['ids']);

        $this->assertTrue($this->model->hasOptions($product));
    }

    /**
     * Bundle product without options should not be possible to buy.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please specify product option
     */
    public function testCheckProductBuyStateEmptyOptionsException()
    {
        $this->mockBundleCollection();
        $product = $this->getProductMock();
        $this->expectProductEntityMetadata();
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => '[]'])],
            ['info_buyRequest', new DataObject(['value' => json_encode(['bundle_option' => ''])])],
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
     * @throws LocalizedException
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider notAvailableOptionProvider
     */
    public function testCheckProductBuyStateMissedOptionException($element, $expectedMessage, $check)
    {
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
            $this->assertContains(
                $expectedMessage,
                $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * In case of missed selection for required options, bundle product should be not able to buy.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCheckProductBuyStateRequiredOptionException()
    {
        $this->mockBundleCollection();
        $product = $this->getProductMock();
        $this->expectProductEntityMetadata();
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => json_encode([])])],
            ['info_buyRequest', new DataObject(['value' => json_encode(['bundle_option' => [1]])])],
        ]);
        $product->setCustomOption(json_encode([]));

        $falseSelection = $this->getMockBuilder(Selection::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSalable'])
            ->getMock();
        $falseSelection->method('isSalable')->willReturn(false);

        $this->bundleCollectionFactory->method('getItemById')->willReturn($falseSelection);
        $this->catalogProduct->setSkipSaleableCheck(false);

        try {
            $this->model->checkProductBuyState($product);
        } catch (LocalizedException $e) {
            $this->assertContains(
                'Please select all required options',
                $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Prepare product mock for testing.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getProductMock()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods([
                '_wakeup',
                'getHasOptions',
                'getId',
                'getStoreId',
                'getCustomOption',
                'getTypeInstance',
                'setStoreFilter',
            ])
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
     */
    public function mockBundleCollection()
    {
        $selectionCollectionMock = $this->getSelectionCollectionMock([]);
        $this->bundleCollectionFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($selectionCollectionMock));
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
     * @return array
     */
    public function notAvailableOptionProvider()
    {
        $falseSelection = $this->getMockBuilder(Selection::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSalable'])
            ->getMock();
        $falseSelection->method('isSalable')->willReturn(false);
        return [
            [
                false,
                'The required options you selected are not available',
                false,
            ],
            [
                $falseSelection,
                'The required options you selected are not available',
                false
            ],
        ];
    }

    /**
     * @return void
     */
    private function expectProductEntityMetadata()
    {
        $entityMetadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->metadataPool->expects($this->any())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($entityMetadataMock);
    }
}
