<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

use Magento\Catalog\Model\Product\Attribute\Repository;

/**
 * Integration tests for \Magento\Catalog\Model\Indexer\Product\Flat\Processor.
 */
class ProcessorTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_state;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_processor;

    protected function setUp(): void
    {
        $this->_state = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Indexer\Product\Flat\State::class
        );
        $this->_processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Indexer\Product\Flat\Processor::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testEnableProductFlat()
    {
        $this->assertTrue($this->_state->isFlatEnabled());
        $this->assertTrue($this->_processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testSaveAttribute()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );

        /** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
        $productResource = $product->getResource();
        $productResource->getAttribute('sku')->setData('used_for_sort_by', 1)->save();

        $this->assertTrue($this->_processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute_in_flat.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testDeleteAttribute()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        /** @var Repository $productAttributeRepository */
        $productAttributeRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(Repository::class);
        $productAttrubute = $productAttributeRepository->get('flat_attribute');
        $productAttributeId = $productAttrubute->getAttributeId();
        $model->load($productAttributeId)->delete();

        $this->assertTrue($this->_processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testAddNewStore()
    {
        $this->assertTrue($this->_processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testAddNewStoreGroup()
    {
        /** @var \Magento\Store\Model\Group $storeGroup */
        $storeGroup = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\Group::class
        );
        $storeGroup->setData(
            ['website_id' => 1, 'name' => 'New Store Group', 'root_category_id' => 2, 'group_id' => null]
        );
        $storeGroup->save();
        $this->assertTrue($this->_processor->getIndexer()->isInvalid());
    }
}
