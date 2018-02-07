<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

/**
 * Class FullTest
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

    protected function setUp()
    {
        $this->_state = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Indexer\Product\Flat\State'
        );
        $this->_processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Indexer\Product\Flat\Processor'
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
            'Magento\Catalog\Model\Product'
        );

        /** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
        $productResource = $product->getResource();
        $productResource->getAttribute('sku')->setData('used_for_sort_by', 1)->save();

        $this->assertTrue($this->_processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     */
    public function testDeleteAttribute()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );

        /** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
        $productResource = $product->getResource();
        $productResource->getAttribute('media_gallery')->delete();

        $this->assertTrue($this->_processor->getIndexer()->isInvalid());
    }

    /**
     * @magentoDbIsolation enabled
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
        $storeGroup = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Group');
        $storeGroup->setData(
            ['website_id' => 1, 'name' => 'New Store Group', 'root_category_id' => 2, 'group_id' => null]
        );
        $storeGroup->save();
        $this->assertTrue($this->_processor->getIndexer()->isInvalid());
    }
}
