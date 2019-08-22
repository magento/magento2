<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

/**
 * Class RowsTest
 */
class RowsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_processor;

    protected function setUp()
    {
        $this->_processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductUpdate()
    {
        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\CategoryFactory::class
        );
        /** @var \Magento\Catalog\Block\Product\ListProduct $listProduct */
        $listProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Block\Product\ListProduct::class
        );

        /** @var \Magento\Framework\Api\DataObjectHelper $dataObjectHelper */
        $dataObjectHelper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Api\DataObjectHelper::class
        );

        /** @var \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry */
        $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogInventory\Api\StockRegistryInterface::class
        );
        /** @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository */
        $stockItemRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogInventory\Api\StockItemRepositoryInterface::class
        );

        /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $stockItemResource */
        $stockItemResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::class
        );

        /** @var \Magento\Catalog\Model\ProductRepository $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );

        $product = $productRepository->get('simple');

        $stockItem = $stockRegistry->getStockItem($product->getId(), 1);

        $stockItemData = [
            'qty' => $stockItem->getQty() + 12,
        ];

        $dataObjectHelper->populateWithArray(
            $stockItem,
            $stockItemData,
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class
        );
        $stockItemResource->setProcessIndexEvents(false);

        $stockItemRepository->save($stockItem);

        $this->_processor->reindexList([1]);

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

        $this->assertEquals(1, $productCollection->count());
        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals('Simple Product', $product->getName());
            $this->assertEquals('Short description', $product->getShortDescription());
            $this->assertEquals(112, $product->getQty());
        }
    }
}
