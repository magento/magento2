<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

/**
 * Full reindex Test
 */
class FullTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_processor;

    protected function setUp()
    {
        $this->_processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexAll()
    {
        $this->_processor->reindexAll();

        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\CategoryFactory::class
        );
        /** @var \Magento\Catalog\Block\Product\ListProduct $listProduct */
        $listProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Block\Product\ListProduct::class
        );

        /** @var \Magento\Indexer\Model\ResourceModel\FrontendResource $indexerStockFrontendResource */
        $indexerStockFrontendResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\FrontendResource::class
        );

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productCollection->joinField(
            'qty',
            $indexerStockFrontendResource->getMainTable(),
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );

        $this->assertCount(1, $productCollection);

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals('Simple Product', $product->getName());
            $this->assertEquals('Short description', $product->getShortDescription());
            $this->assertEquals(100, $product->getQty());
        }
    }
}
