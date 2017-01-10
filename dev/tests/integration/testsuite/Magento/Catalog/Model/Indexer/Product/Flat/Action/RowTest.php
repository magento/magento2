<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

/**
 * Class RowTest
 */
class RowTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

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
        $this->_product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $this->_category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        $this->_processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Indexer\Product\Flat\Processor::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/row_fixture.php
     * @magentoConfigFixture current_store catalog/frontend/flat_catalog_product 1
     * @magentoAppArea frontend
     */
    public function testProductUpdate()
    {
        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\CategoryFactory::class);
        $listProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Block\Product\ListProduct::class);

        $this->_processor->getIndexer()->setScheduled(false);
        $this->assertFalse(
            $this->_processor->getIndexer()->isScheduled(),
            'Indexer is in scheduled mode when turned to update on save mode'
        );
        $this->_processor->reindexAll();

        $this->_product->load(1);
        $this->_product->setName('Updated Product');
        $this->_product->save();

        $category = $categoryFactory->create()->load(9);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $layer->getProductCollection();
        $this->assertTrue(
            $productCollection->isEnabledFlat(),
            'Product collection is not using flat resource when flat is on'
        );

        $this->assertEquals(2, $productCollection->count(), 'Product collection items count must be exactly 2');

        foreach ($productCollection as $product) {
            /** @var $product \Magento\Catalog\Model\Product */
            if ($product->getId() == 1) {
                $this->assertEquals(
                    'Updated Product',
                    $product->getName(),
                    'Product name from flat does not match with updated name'
                );
            }
        }
    }
}
