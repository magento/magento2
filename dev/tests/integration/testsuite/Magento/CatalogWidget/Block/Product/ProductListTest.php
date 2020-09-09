<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Block\Product;

use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for @see \Magento\CatalogWidget\Block\Product\ProductsList
 */
class ProductListTest extends TestCase
{
    /**
     * @var ProductsList
     */
    protected $block;

    /**
     * @var CategoryCollection;

     */
    private $categoryCollection;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->create(ProductsList::class);
        $this->categoryCollection = $this->objectManager->create(CategoryCollection::class);
    }

    /**
     * Make sure that widget conditions are applied to product collection correctly
     *
     * 1. Create new multiselect attribute with several options
     * 2. Create 2 new products and select at least 2 multiselect options for one of these products
     * 3. Create product list widget condition based on the new multiselect attribute
     * 4. Set at least 2 options of multiselect attribute to match products for the product list widget
     * 5. Load collection for product list widget and make sure that number of loaded products is correct
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     */
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
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/products_with_dropdown_attribute.php
     */
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
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     * @dataProvider createCollectionForSkuDataProvider
     * @param string $encodedConditions
     * @param string $sku
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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
    public function createCollectionForSkuDataProvider()
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
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_date_attribute.php
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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
     * 1. Create an anchor root category and a sub category inside it
     * 2. Create 2 new products and assign them to the sub categories
     * 3. Create product list widget condition to display products from the anchor root category
     * 4. Load collection for product list widget and make sure that number of loaded products is correct
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_in_nested_anchor_categories.php
     */
    public function testCreateAnchorCollection()
    {
        // Reindex EAV attributes to enable products filtration by created multiselect attribute
        /** @var Processor $eavIndexerProcessor */
        $eavIndexerProcessor = $this->objectManager->get(
            Processor::class
        );
        $eavIndexerProcessor->reindexAll();

        $this->categoryCollection->addNameToResult()->load();
        $rootCategoryId =  $this
            ->categoryCollection
            ->getItemByColumnValue('name', 'Default Category')
            ->getId();

        $encodedConditions = '^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,
        `aggregator`:`all`,`value`:`1`,`new_child`:``^],
        `1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,
        `attribute`:`category_ids`,
        `operator`:`==`,`value`:`' . $rootCategoryId . '`^]^]';

        $this->block->setData('conditions_encoded', $encodedConditions);

        $productCollection = $this->block->createCollection();
        $productCollection->load();

        $this->assertEquals(
            2,
            $productCollection->count(),
            "Anchor root category does not contain products of it's children."
        );
    }

    /**
     * Test that price rule condition works correctly
     *
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/category_with_different_price_products.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @param string $operator
     * @param int $value
     * @param array $matches
     * @dataProvider priceFilterDataProvider
     */
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

    public function priceFilterDataProvider(): array
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
}
