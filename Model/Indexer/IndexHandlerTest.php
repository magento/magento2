<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\IndexerInterface;

/**
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Elasticsearch/_files/products.php
 */
class IndexHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * Setup method
     */
    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(
            'Magento\Indexer\Model\Indexer'
        );
        $this->indexer->load('catalogsearch_fulltext');
    }

    /**
     * Test reindex process
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testReindexAll()
    {
        $this->indexer->reindexAll();
    }

    /**
     * Return product by SKU
     *
     * @param string $sku
     * @return Product
     */
    protected function getProductBySku($sku)
    {
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product'
        );
        return $product->loadByAttribute('sku', $sku);
    }
}
