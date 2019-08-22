<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\TestCase;

/**
 * Full reindex Test
 */
class FullTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Processor
     */
    protected $_processor;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->_processor = $this->objectManager->get(Processor::class);
    }

    /**
     * Reindex all
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testReindexAll()
    {
        $this->_processor->reindexAll();

        $categoryFactory = $this->objectManager->get(CategoryFactory::class);
        /** @var ListProduct $listProduct */
        $listProduct = $this->objectManager->get(ListProduct::class);

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productCollection->joinField(
            'qty',
            'cataloginventory_stock_status',
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

    /**
     * Reindex with disabled product
     *
     * @return void
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     */
    public function testReindexAllWithDisabledProduct(): void
    {
        $productCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $productCollection = $productCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', ['eq' => 'simple3'])
            ->addAttributeToSort('created_at', 'DESC')
            ->joinField(
                'stock_status',
                'cataloginventory_stock_status',
                'stock_status',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            )->load();

        $this->assertCount(1, $productCollection);

        /** @var Product $product */
        foreach ($productCollection as $product) {
            $this->assertEquals(1, $product->getData('stock_status'));
        }
    }
}
