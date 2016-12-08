<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Bundle\Model\Selection;
use Magento\Catalog\Model\Product;

/**
 * Class TypeTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeTest extends \PHPUnit_Framework_TestCase
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
    protected $bundleCollection;

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
     * @return void
     */
    protected function setUp()
    {
        $this->bundleCollection =
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
        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject(
            \Magento\Bundle\Model\Product\Type::class,
            [
                'bundleModelSelection' => $this->bundleModelSelection,
                'bundleFactory' => $this->bundleFactory,
                'bundleCollection' => $this->bundleCollection,
                'bundleOption' => $this->bundleOptionFactory,
                'catalogData' => $this->catalogData,
                'storeManager' => $this->storeManager,
                'stockRegistry' => $this->stockRegistry,
                'stockState' => $this->stockState,
                'catalogProduct' => $this->catalogProduct,
                'priceCurrency' => $this->priceCurrency,
                'serializer' => new Json()
            ]
        );
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
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => ''])],
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
        $product->method('getCustomOption')->willReturnMap([
            ['bundle_selection_ids', new DataObject(['value' => json_encode([1])])],
            ['info_buyRequest', new DataObject(['value' => json_encode(['bundle_option' => [1]])])],
        ]);
        $product->setCustomOption(json_encode([]));

        $this->bundleCollection->method('getItemById')->willReturn($element);
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

        $this->bundleCollection->method('getItemById')->willReturn($falseSelection);
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
        $this->bundleCollection->method('create')->willReturn($this->bundleCollection);
        $this->bundleCollection->method('addAttributeToSelect')->willReturn($this->bundleCollection);
        $this->bundleCollection->method('setFlag')->willReturn($this->bundleCollection);
        $this->bundleCollection->method('setPositionOrder')->willReturn($this->bundleCollection);
        $this->bundleCollection->method('addStoreFilter')->willReturn($this->bundleCollection);
        $this->bundleCollection->method('setStoreId')->willReturn($this->bundleCollection);
        $this->bundleCollection->method('addFilterByRequiredOptions')->willReturn($this->bundleCollection);
        $this->bundleCollection->method('setOptionIdsFilter')->willReturn($this->bundleCollection);
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
}
