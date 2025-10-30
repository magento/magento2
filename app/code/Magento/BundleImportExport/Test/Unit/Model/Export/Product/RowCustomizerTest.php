<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\BundleImportExport\Test\Unit\Model\Export\Product;

use ArrayIterator;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type;
use Magento\BundleImportExport\Model\Export\RowCustomizer;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RowCustomizerTest extends TestCase
{
    /**
     * @var RowCustomizer|MockObject
     */
    protected $rowCustomizerMock;

    /**
     * @var Data|MockObject
     */
    private $catalogDataMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->catalogDataMock = $this->createMock(Data::class);
        $this->rowCustomizerMock = new RowCustomizer(
            $this->configureStoreManager(),
            $this->catalogDataMock
        );
    }

    /**
     * Test prepareData()
     */
    public function testPrepareData()
    {
        $result = $this->rowCustomizerMock->prepareData(
            $this->configureProducts(self::addDataDataProvider()[0][1]),
            [1]
        );
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
     * @dataProvider addDataDataProvider
     */
    public function testAddData(
        bool $isPriceGlobal,
        array $products,
        array $expected
    ) {
        $this->catalogDataMock->method('isPriceGlobal')->willReturn($isPriceGlobal);
        $preparedData = $this->rowCustomizerMock->prepareData($this->configureProducts($products), [1]);
        $attributes = 'attribute=1,sku_type=1,attribute2="Text",price_type=1,price_view=1,weight_type=1,'
            . 'values=values,shipment_type=1,attribute3=One,Two,Three';

        $rows = [];
        foreach ($products as $item) {
            $rows[] = $preparedData->addData(
                ['sku' => $item['sku'], 'additional_attributes' => $attributes],
                $item['entity_id']
            );
        }

        $this->assertEquals($expected, $rows);
    }

    /**
     * Test getAdditionalRowsCount()
     */
    public function testGetAdditionalRowsCount()
    {
        $count = [5];
        $this->assertEquals($count, $this->rowCustomizerMock->getAdditionalRowsCount($count, 0));
    }

    private function configureProducts(array $products = []): Collection
    {
        $productObjects = [];
        foreach ($products as $productData) {
            $product = $this->getMockBuilder(Product::class)
                ->onlyMethods(['getTypeInstance'])
                ->disableOriginalConstructor()
                ->getMock();
            $product->addData($productData);
            $productObjects[] = $product;
            $optionsCollections = [];
            $selectionsCollections = [];
            foreach ($productData['options'] as $storeId => $optionsData) {
                $options = [];
                $selections = [];
                foreach (array_replace_recursive($productData['options'][0], $optionsData) as $optionData) {
                    $option = $this->createPartialMock(Option::class, []);
                    $optionData['id'] = $optionData['option_id'];
                    $option->addData($optionData);
                    $options[$optionData['option_id']] = $option;
                    foreach ($optionData['selections'] as $selectionData) {
                        $selection = $this->createPartialMock(Product::class, ['getSku']);
                        $selection->method('getSku')->willReturn($selectionData['sku']);
                        $selectionData['id'] = $selectionData['selection_id'];
                        $selection->addData($selectionData);
                        $selections[$selection['selection_id']] = $selection;
                    }
                }
                $optionsCollection = $this->createPartialMock(
                    \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
                    ['setOrder', 'getIterator', 'getItems', 'getItemById', 'load']
                );
                $optionsCollection->method('getItems')->willReturn($options);
                $optionsCollection->method('getIterator')->willReturn(new ArrayIterator($options));
                $optionsCollection->method('getItemById')->willReturnCallback(fn($id) => $options[$id] ?? null);
                $optionsCollection->method('setOrder')->willReturnSelf();
                $optionsCollections[$storeId] = $optionsCollection;

                $selectionsCollection = $this->createPartialMock(
                    \Magento\Bundle\Model\ResourceModel\Selection\Collection::class,
                    ['getIterator', 'getItems', 'load', 'addAttributeToSort']
                );
                $selectionsCollection->method('getIterator')->willReturn(new ArrayIterator($selections));
                $selectionsCollection->method('getItems')->willReturn($selections);
                $selectionsCollection->method('addAttributeToSort')->willReturnSelf();
                $selectionsCollections[$storeId] = $selectionsCollection;
            }
            $type = $this->createMock(Type::class);
            $type->method('getOptionsCollection')
                ->willReturnCallback(function () use ($optionsCollections) {
                    return $optionsCollections[func_get_arg(0)->getStoreId()];
                });
            $type->method('getSelectionsCollection')
                ->willReturnCallback(function () use ($selectionsCollections) {
                    return $selectionsCollections[func_get_arg(1)->getStoreId()];
                });
            $product->method('getTypeInstance')->willReturn($type);
        }
        $collection = $this->createPartialMock(Collection::class, ['addAttributeToFilter', 'getIterator']);
        $collection->method('addAttributeToFilter')->willReturnSelf();
        $collection->method('getIterator')->willReturn(new ArrayIterator($productObjects));
        return $collection;
    }

    private function configureStoreManager(): StoreManagerInterface
    {
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $website1 = $this->createMock(WebsiteInterface::class);
        $website2 = $this->createMock(WebsiteInterface::class);
        $storeManager->method('getWebsite')->willReturnMap([[null, $website1], [1, $website1], [2, $website2]]);
        $storeGroup1 = $this->createMock(GroupInterface::class);
        $storeGroup2 = $this->createMock(GroupInterface::class);
        $storeManager->method('getGroup')->willReturnMap([[null, $storeGroup1], [1, $storeGroup1], [2, $storeGroup2]]);
        $store1 = $this->createMock(StoreInterface::class);
        $store2 = $this->createMock(StoreInterface::class);
        $storeManager->method('getStore')->willReturnMap([[null, $store1], [1, $store1], [2, $store2]]);

        $website1->method('getId')->willReturn(1);
        $website1->method('getCode')->willReturn('base');
        $website1->method('getDefaultGroupId')->willReturn(1);

        $website2->method('getId')->willReturn(2);
        $website2->method('getCode')->willReturn('website_2');
        $website2->method('getDefaultGroupId')->willReturn(2);

        $storeGroup1->method('getId')->willReturn(1);
        $storeGroup1->method('getCode')->willReturn('main_website_store');
        $storeGroup1->method('getDefaultStoreId')->willReturn(1);

        $storeGroup2->method('getId')->willReturn(2);
        $storeGroup2->method('getCode')->willReturn('store_2');
        $storeGroup2->method('getDefaultStoreId')->willReturn(2);

        $store1->method('getId')->willReturn(1);
        $store1->method('getCode')->willReturn('default');

        $store2->method('getId')->willReturn(2);
        $store2->method('getCode')->willReturn('store_view_2');
        return $storeManager;
    }

    /**
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function addDataDataProvider(): array
    {
        $productDataTemplate = [
            'sku' => 'bundle-sku-1',
            'entity_id' => 1,
            'price_type' => 1,
            'shipment_type' => 1,
            'sku_type' => 1,
            'weight_type' => 1,
            'price_view' => 1,
            'store_ids' => [1],
            'website_ids' => [1],
            'options' => [
                // global scope
                0 => [
                    [
                        'option_id' => 1,
                        'title' => 'Option 1',
                        'type' => 1,
                        'required' => 1,
                        'selections' => [
                            [
                                'sku' => 'simple-sku-1',
                                'product_id' => 2,
                                'option_id' => 1,
                                'selection_id' => 1,
                                'selection_price_value' => 50,
                                'selection_price_type' => 1,
                                'selection_qty' => 1,
                                'selection_can_change_qty' => 1,
                                'position' => 0,
                                'is_default' => 0
                            ]
                        ]
                    ]
                ],
                // default store
                1 => [],
            ],
        ];

        $expectedDataTemplate = [
            'sku' => 'bundle-sku-1',
            'additional_attributes' => 'attribute=1,attribute2="Text",attribute3=One,Two,Three',
            'bundle_price_type' => 'fixed',
            'bundle_shipment_type' => 'separately',
            'bundle_sku_type' => 'fixed',
            'bundle_price_view' => 'As low as',
            'bundle_weight_type' => 'fixed'
        ];
            
        return [
            [
                true,
                [
                    $productDataTemplate
                ],
                [
                    [
                        ...$expectedDataTemplate,
                        'bundle_values' => 'name=Option 1' .
                            ',type=1,required=1,sku=simple-sku-1,price=50' .
                            ',default=0,default_qty=1,price_type=percent,can_change_qty=1'
                    ]
                ]
            ],
            [
                true,
                [
                    array_replace_recursive(
                        $productDataTemplate,
                        [
                            'store_ids' => [1, 2],
                            'website_ids' => [1, 2],
                            'options' => [
                                1 => [
                                    [
                                        'title' => 'Option 1 Store 1',
                                    ]
                                ],
                                2 => [
                                    [
                                        'title' => 'Option 1 Store 2',
                                    ]
                                ]
                            ]
                        ]
                    )
                ],
                [
                    [
                        ...$expectedDataTemplate,
                        'bundle_values' => 'name=Option 1' .
                            ',name_default=Option 1 Store 1,name_store_view_2=Option 1 Store 2' .
                            ',type=1,required=1,sku=simple-sku-1,price=50' .
                            ',default=0,default_qty=1,price_type=percent,can_change_qty=1'
                    ]
                ]
            ],
            [
                false,
                [
                    array_replace_recursive(
                        $productDataTemplate,
                        [
                            'store_ids' => [1, 2],
                            'website_ids' => [1, 2],
                            'options' => [
                                2 => [
                                    [
                                        'selections' => [
                                            [
                                                'selection_price_value' => 20,
                                                'selection_price_type' => 1,
                                                'price_scope' => 2
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    )
                ],
                [
                    [
                        ...$expectedDataTemplate,
                        'bundle_values' => 'name=Option 1' .
                            ',type=1,required=1,sku=simple-sku-1,price=50' .
                            ',default=0,default_qty=1,price_type=percent,can_change_qty=1' .
                            ',price_website_website_2=20,price_type_website_website_2=percent'
                    ]
                ]
            ]
        ];
    }
}
