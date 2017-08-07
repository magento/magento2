<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Block\Product;

/**
 * Tests for @see \Magento\CatalogWidget\Block\Product\ProductsList
 */
class ProductListTest extends \PHPUnit_Framework_TestCase
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

        // Load products collection filtered using specified conditions and perform assesrions
        $productCollection = $this->block->createCollection();
        $productCollection->load();
        $this->assertEquals(
            1,
            $productCollection->count(),
            "Product collection was not filtered according to the widget condition."
        );
    }
}
