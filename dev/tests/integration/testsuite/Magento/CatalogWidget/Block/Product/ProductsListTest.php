<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogWidget\Block\Product;

use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Test\Fixture\MultiselectAttribute as MultiselectAttributeFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    CoversClass(ProductsList::class),
    DbIsolation(false),
]
class ProductsListTest extends TestCase
{
    /**
     * @var ProductsList
     */
    private $block;

    /**
     * @var CategoryCollection;
     */
    private $categoryCollection;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->create(ProductsList::class);
        $this->categoryCollection = $this->objectManager->create(CategoryCollection::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Make sure that widget conditions are applied to product collection correctly
     *
     * 1. Create new multiselect attribute with several options
     * 2. Create 2 new products and select at least 2 multiselect options for one of these products
     * 3. Create product list widget condition based on the new multiselect attribute
     * 4. Set at least 2 options of multiselect attribute to match products for the product list widget
     * 5. Load collection for product list widget and make sure that number of loaded products is correct
     */
    #[
        DataFixture('Magento/Catalog/_files/products_with_multiselect_attribute.php'),
    ]
    public function testCreateCollection()
    {
        // Reindex EAV attributes to enable products filtration by created multiselect attribute
        /** @var Processor $eavIndexerProcessor */
        $eavIndexerProcessor = $this->objectManager->get(
            Processor::class
        );
        $eavIndexerProcessor->reindexAll();

        // Prepare conditions
        /** @var $attribute Attribute */
        $attribute = Bootstrap::getObjectManager()->create(
            Attribute::class
        );
        $attribute->load('multiselect_attribute', 'attribute_code');
        $multiselectAttributeOptionIds = [];
        foreach ($attribute->getOptions() as $option) {
            if ($option->getValue()) {
                $multiselectAttributeOptionIds[] = $option->getValue();
            }
        }
        $encodedConditions = '^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,'
            . '`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:'
            . '^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,'
            . '`attribute`:`multiselect_attribute`,`operator`:`^[^]`,'
            . '`value`:[`' . implode(',', $multiselectAttributeOptionIds) . '`]^]^]';
        $this->block->setData('conditions_encoded', $encodedConditions);

        // Load products collection filtered using specified conditions and perform assertions
        $productCollection = $this->block->createCollection();
        $productCollection->load();
        $this->assertEquals(
            1,
            $productCollection->count(),
            "Product collection was not filtered according to the widget condition."
        );
    }

    /**
     * Test product list widget can process condition with dropdown type of attribute
     */
    #[
        DataFixture('Magento/Catalog/_files/products_with_dropdown_attribute.php'),
    ]
    public function testCreateCollectionWithDropdownAttribute()
    {
        /** @var $attribute Attribute */
        $attribute = Bootstrap::getObjectManager()->create(
            Attribute::class
        );
        $attribute->load('dropdown_attribute', 'attribute_code');
        $dropdownAttributeOptionIds = [];
        foreach ($attribute->getOptions() as $option) {
            if ($option->getValue()) {
                $dropdownAttributeOptionIds[] = $option->getValue();
            }
        }
        $encodedConditions = '^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,' .
            '`aggregator`:`any`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule|' .
            '|Condition||Product`,`attribute`:`dropdown_attribute`,`operator`:`==`,`value`:`'
            . $dropdownAttributeOptionIds[0] . '`^],`1--2`:^[`type`:`Magento||CatalogWidget||Model||Rule|' .
            '|Condition||Product`,`attribute`:`dropdown_attribute`,`operator`:`==`,`value`:`'
            . $dropdownAttributeOptionIds[1] . '`^]^]';
        $this->block->setData('conditions_encoded', $encodedConditions);
        $this->performAssertions(2);
        $attribute->setUsedInProductListing(0);
        $attribute->save();
        $this->performAssertions(2);
        $attribute->setIsGlobal(1);
        $attribute->save();
        $this->performAssertions(2);
    }

    /**
     * Check product collection includes correct amount of products.
     *
     * @param int $count
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function performAssertions(int $count)
    {
        // Load products collection filtered using specified conditions and perform assertions.
        $productCollection = $this->block->createCollection();
        $productCollection->load();
        $this->assertEquals(
            $count,
            $productCollection->count(),
            "Product collection was not filtered according to the widget condition."
        );
    }

    /**
     * Check that collection returns correct result if use not contains operator for string attribute
     *
     * @param string $encodedConditions
     * @param string $sku
     */
    #[
        DataFixture('Magento/Catalog/_files/product_simple_xss.php'),
        DataFixture('Magento/Catalog/_files/product_virtual.php'),
        DataFixture(ProductFixture::class, ['status' => ProductStatus::STATUS_DISABLED]),
        DataProvider('createCollectionForSkuDataProvider'),
    ]
    public function testCreateCollectionForSku($encodedConditions, $sku)
    {
        $this->block->setData('conditions_encoded', $encodedConditions);
        $productCollection = $this->block->createCollection();
        $productCollection->load();
        $this->assertEquals(
            1,
            $productCollection->count(),
            "Product collection was not filtered according to the widget condition."
        );
        $this->assertEquals($sku, $productCollection->getFirstItem()->getSku());
    }

    /**
     * @return array
     */
    public static function createCollectionForSkuDataProvider()
    {
        return [
            'contains' => ['^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,'
                . '`aggregator`:`all`,`value`:`1`,`new_child`:``^],'
                . '`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,'
                . '`attribute`:`sku`,`operator`:`^[^]`,`value`:`virtual`^]^]' , 'virtual-product'],
            'not contains' => ['^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,'
                . '`aggregator`:`all`,`value`:`1`,`new_child`:``^],'
                . '`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,'
                . '`attribute`:`sku`,`operator`:`!^[^]`,`value`:`virtual`^]^]', 'product-with-xss']
        ];
    }

    /**
     * Check that collection returns correct result if use date attribute.
     */
    #[
        DataFixture('Magento/Catalog/_files/product_simple_with_date_attribute.php'),
    ]
    public function testProductListWithDateAttribute()
    {
        $encodedConditions = '^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,'
            . '`aggregator`:`all`,`value`:`1`,`new_child`:``^],'
            . '`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,'
            . '`attribute`:`date_attribute`,`operator`:`==`,`value`:`' . date('Y-m-d') . '`^]^]';
        $this->block->setData('conditions_encoded', $encodedConditions);

        // Load products collection filtered using specified conditions and perform assertions
        $productCollection = $this->block->createCollection();
        $productCollection->load();
        $this->assertEquals(
            1,
            $productCollection->count(),
            "Product collection was not filtered according to the widget condition."
        );
    }

    /**
     * Make sure CatalogWidget would display anchor category products recursively from children categories.
     *
     * @param string $operator
     * @param string $value
     * @param array $expectedProducts
     * @throws LocalizedException
     */
    #[
        DataProvider('createAnchorCollectionDataProvider'),
        // level 1 categories
        DataFixture(CategoryFixture::class, ['is_anchor' => 1], 'category1'),
        DataFixture(CategoryFixture::class, ['is_anchor' => 1], 'category2'),
        DataFixture(CategoryFixture::class, ['is_anchor' => 0], 'category3'),
        // level 2 categories
        DataFixture(CategoryFixture::class, ['parent_id' => '$category1.id$'], 'category11'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$category2.id$'], 'category21'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$category3.id$'], 'category31'),
        // level 3 categories
        DataFixture(CategoryFixture::class, ['parent_id' => '$category11.id$'], 'category111'),
        // products assigned to level 1 categories
        DataFixture(ProductFixture::class, ['category_ids' => ['$category1.id$']], as: 'product1'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category2.id$']], as: 'product2'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category3.id$']], as: 'product3'),
        // unassigned product
        DataFixture(ProductFixture::class, as: 'product4'),
        // products assigned to level 2 categories
        DataFixture(ProductFixture::class, ['category_ids' => ['$category11.id$']], as: 'product11'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category21.id$']], as: 'product21'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category31.id$']], as: 'product31'),
        // products assigned to level 3 categories
        DataFixture(ProductFixture::class, ['category_ids' => ['$category111.id$']], as: 'product111'),
    ]
    public function testCreateAnchorCollection(
        string $operator,
        string $value,
        array $expectedProducts
    ): void {
        // Reindex EAV attributes to enable products filtration by created multiselect attribute
        /** @var Processor $eavIndexerProcessor */
        $eavIndexerProcessor = $this->objectManager->get(
            Processor::class
        );
        $eavIndexerProcessor->reindexAll();
        $fixtures = DataFixtureStorageManager::getStorage();

        $this->categoryCollection->addNameToResult()->load();
        $value = preg_replace_callback(
            '/(category\d+)/',
            function ($matches) use ($fixtures) {
                return $fixtures->get($matches[1])->getId();
            },
            $value
        );

        $encodedConditions = '^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,
        `aggregator`:`all`,`value`:`1`,`new_child`:``^],
        `1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,
        `attribute`:`category_ids`,
        `operator`:`' . $operator . '`,`value`:`' . $value . '`^]^]';

        $this->block->setData('conditions_encoded', $encodedConditions);

        $productCollection = $this->block->createCollection();
        $productCollection->load();

        $allProducts = [
            'product1',
            'product2',
            'product3',
            'product4',
            'product11',
            'product21',
            'product31',
            'product111',
        ];
        $allProducts = array_combine(
            array_map(fn ($productKey) => $fixtures->get($productKey)->getSku(), $allProducts),
            $allProducts,
        );

        $actualProducts = $productCollection->getColumnValues('sku');

        $this->assertEqualsCanonicalizing(
            $expectedProducts,
            array_map(
                fn ($sku) => $allProducts[$sku],
                array_intersect(
                    $actualProducts,
                    array_keys($allProducts)
                )
            )
        );
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$', '$p2$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle1'),
    ]
    public function testBundleProductList()
    {
        $postParams = $this->block->getAddToCartPostParams($this->fixtures->get('bundle1'));

        $this->assertArrayHasKey(
            'product',
            $postParams['data'],
            'Bundle product options is missing from POST params.'
        );
        $this->assertArrayHasKey(
            'options',
            $postParams['data'],
            'Bundle product options is missing from POST params.'
        );
    }

    /**
     * Test that price rule condition works correctly
     *
     * @param string $operator
     * @param int $value
     * @param array $matches
     */
    #[
        DataFixture('Magento/Catalog/_files/category_with_different_price_products.php'),
        DataFixture('Magento/ConfigurableProduct/_files/product_configurable.php'),
        DataProvider('priceFilterDataProvider'),
    ]
    public function testPriceFilter(string $operator, int $value, array $matches)
    {
        $encodedConditions = '^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,
        `aggregator`:`all`,`value`:`1`,`new_child`:``^],
        `1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,
        `attribute`:`price`,
        `operator`:`' . $operator . '`,`value`:`' . $value . '`^]^]';

        $this->block->setData('conditions_encoded', $encodedConditions);

        $productCollection = $this->block->createCollection();
        $productCollection->load();
        $skus = array_map(
            function ($item) {
                return $item['sku'];
            },
            $productCollection->getItems()
        );
        $this->assertEmpty(array_diff($matches, $skus));
    }

    public static function priceFilterDataProvider(): array
    {
        return [
            [
                '>',
                10,
                [
                    'simple1001',
                ]
            ],
            [
                '>=',
                10,
                [
                    'simple1000',
                    'simple1001',
                    'configurable',
                ]
            ],
            [
                '<',
                10,
                []
            ],
            [
                '<',
                20,
                [
                    'simple1000',
                    'configurable',
                ]
            ],
            [
                '<=',
                20,
                [
                    'simple1000',
                    'simple1001',
                    'configurable',
                ]
            ],
        ];
    }

    #[
        DataProvider('collectionResultWithMultiselectAttributeDataProvider'),
        DataFixture(
            MultiselectAttributeFixture::class,
            [
                'scope' => 'global',
                'options' => ['option_1', 'option_2']
            ],
            'gl_multiselect'
        ),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(
            ProductFixture::class,
            [
                'category_ids' => ['$category.id$']
            ],
            as: 'product1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => [
                    ['attribute_code' => '$gl_multiselect.attribute_code$', 'value' => '$gl_multiselect.option_1$']
                ]
            ],
            as: 'product2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => [
                    ['attribute_code' => '$gl_multiselect.attribute_code$', 'value' => '$gl_multiselect.option_2$']
                ]
            ],
            as: 'product3'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'category_ids' => ['$category.id$'],
                'custom_attributes' => [
                    ['attribute_code' => '$gl_multiselect.attribute_code$', 'value' => '$gl_multiselect.option_1$']
                ]
            ],
            as: 'product4'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'category_ids' => ['$category.id$'],
                'custom_attributes' => [
                    ['attribute_code' => '$gl_multiselect.attribute_code$', 'value' => '$gl_multiselect.option_2$']
                ]
            ],
            as: 'product5'
        )
    ]
    public function testCollectionResultWithMultiselectAttribute(
        array $conditions,
        array $products
    ): void {
        $fixtures = DataFixtureStorageManager::getStorage();
        $conditions = array_map(
            function ($condition) use ($fixtures) {
                if (isset($condition['value']) && is_callable($condition['value'])) {
                    $condition['value'] = $condition['value']($fixtures);
                }
                if (isset($condition['attribute']) && $fixtures->get($condition['attribute'])) {
                    $condition['attribute'] = $fixtures->get($condition['attribute'])->getAttributeCode();
                }
                return $condition;
            },
            $conditions
        );
        $products = array_map(
            function ($product) use ($fixtures) {
                return $fixtures->get($product)->getSku();
            },
            $products
        );

        $this->block->setConditions($conditions);
        $collection = $this->block->createCollection();
        $collection->load();

        $this->assertEqualsCanonicalizing(
            $products,
            $collection->getColumnValues('sku')
        );
    }

    public static function collectionResultWithMultiselectAttributeDataProvider(): array
    {
        return [
            'global multiselect with match ANY' => [
                [
                    '1' => [
                        'type' => \Magento\CatalogWidget\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'any',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => fn ($fixtures) => $fixtures->get('category')->getId(),
                    ],
                    '1--2' => [
                        'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                        'attribute' => 'gl_multiselect',
                        'operator' => '()',
                        'value' => fn ($fixtures) => $fixtures->get('gl_multiselect')->getData('option_1'),
                    ],
                ],
                ['product1', 'product2', 'product4', 'product5']
            ],
            'global multiselect with match AND' => [
                [
                    '1' => [
                        'type' => \Magento\CatalogWidget\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => fn ($fixtures) => $fixtures->get('category')->getId(),
                    ],
                    '1--2' => [
                        'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                        'attribute' => 'gl_multiselect',
                        'operator' => '()',
                        'value' => fn ($fixtures) => $fixtures->get('gl_multiselect')->getData('option_1'),
                    ],
                ],
                ['product4']
            ],
            'global multiselect with single value' => [
                [
                    '1' => [
                        'type' => \Magento\CatalogWidget\Model\Rule\Condition\Combine::class,
                        'aggregator' => 'all',
                        'value' => '1',
                        'new_child' => '',
                    ],
                    '1--1' => [
                        'type' => \Magento\CatalogWidget\Model\Rule\Condition\Product::class,
                        'attribute' => 'gl_multiselect',
                        'operator' => '()',
                        'value' => fn ($fixtures) => $fixtures->get('gl_multiselect')->getData('option_2'),
                    ],
                ],
                ['product3', 'product5']
            ]
        ];
    }

    /**
     * @return array[]
     */
    public static function createAnchorCollectionDataProvider(): array
    {
        return [
            'is - category1,category2' => [
                '==',
                'category1,category2',
                ['product111', 'product21', 'product11', 'product2', 'product1']
            ],
            'is not - category1,category2' => [
                '!=',
                'category1,category2',
                 ['product31', 'product4', 'product3']
            ],
            'contains - category1,category2' => [
                '{}',
                'category1,category2',
               ['product111', 'product21', 'product11', 'product2', 'product1']
            ],
            'does not contain - category1,category2' => [
                '!{}',
                'category1,category2',
                 ['product31', 'product4', 'product3']
            ],
            'is one of - category1,category2' => [
                '()',
                'category1,category2',
                ['product111', 'product21', 'product11', 'product2', 'product1']
            ],
            'is not one of - category1,category2' => [
                '!()',
                'category1,category2',
                ['product31', 'product4', 'product3']
            ],
            // single anchor category
            'is - category1' => [
                '==',
                'category1',
                ['product111', 'product11', 'product1']
            ],
            'is not - category1' => [
                '!=',
                'category1',
                ['product31', 'product21', 'product4', 'product3', 'product2']
            ],
            // single non-anchor category
            'is - category3' => [
                '==',
                'category3',
                ['product3']
            ],
            'is not - category3' => [
                '!=',
                'category3',
                ['product111', 'product31', 'product21', 'product11', 'product4', 'product2', 'product1']
            ],
            // anchor and non-anchor category
            'is - category1,category3' => [
                '==',
                // spaces are intentional to check trimming functionality
                'category1 , category3',
                ['product111', 'product11', 'product3', 'product1']
            ],
            'is not - category1,category3' => [
                '!=',
                // spaces are intentional to check trimming functionality
                'category1 , category3',
                ['product31', 'product21', 'product4', 'product2']
            ],
        ];
    }
}
