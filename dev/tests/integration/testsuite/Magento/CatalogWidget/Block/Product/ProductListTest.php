<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Block\Product;

/**
 * Tests for @see \Magento\CatalogWidget\Block\Product\ProductsList
 */
class ProductListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogWidget\Block\Product\ProductsList
     */
    protected $block;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->block = $this->objectManager->create(
            \Magento\CatalogWidget\Block\Product\ProductsList::class
        );
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
        /** @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavIndexerProcessor */
        $eavIndexerProcessor = $this->objectManager->get(
            \Magento\Catalog\Model\Indexer\Product\Eav\Processor::class
        );
        $eavIndexerProcessor->reindexAll();

        // Prepare conditions
        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
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
        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
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
}
