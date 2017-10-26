<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySales\Test\Integration\Stock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsBackorderedProductInStockTest extends TestCase
{
    const PRODUCT_SKU = 'SKU-2';

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSaveInterface;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepositoryInterface;

    /**
     * @var GetProductQuantityInStockInterface
     */
    private $isProductInStock;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaInterfaceFactory;

    protected function setUp()
    {
        $this->productRepositoryInterface = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->stockItemRepository =  Bootstrap::getObjectManager()->create(StockItemRepositoryInterface::class);
        $this->stockItemCriteriaInterfaceFactory =  Bootstrap::getObjectManager()->create(
            StockItemCriteriaInterfaceFactory::class
        );
        $this->sourceItemRepository = Bootstrap::getObjectManager()->create(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $this->sourceItemsSaveInterface = Bootstrap::getObjectManager()->create(SourceItemsSaveInterface::class);
        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(StockItemIndexerInterface::INDEXER_ID);
        $this->isProductInStock = Bootstrap::getObjectManager()->create(
            IsProductInStockInterface::class
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDbIsolation disabled
     */
    public function testBackorderedZeroQtyProductIsInStock()
    {
        /** @var ProductInterface $product */
        $product = $this->productRepositoryInterface->get(self::PRODUCT_SKU);
        $stockItemSearchCriteria = $this->stockItemCriteriaInterfaceFactory->create();
        $stockItemSearchCriteria->setProductsFilter($product->getId());
        $stockItemsCollection = $this->stockItemRepository->getList($stockItemSearchCriteria);

        /** @var StockItemInterface $stockItem */
        $stockItem = current($stockItemsCollection->getItems());
        $stockItem->setBackorders(1);
        $stockItem->setUseConfigBackorders(0);
        $this->stockItemRepository->save($stockItem);

        $sourceItem = $this->getSourceItemBySKU(self::PRODUCT_SKU);
        $this->changeSourceItemQty($sourceItem, -15);

        $this->assertTrue($this->isProductInStock->execute(self::PRODUCT_SKU, 1));
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testZeroQtyProductIsOutOfStock()
    {
        $sourceItem = $this->getSourceItemBySKU(self::PRODUCT_SKU);
        $this->changeSourceItemQty($sourceItem, 0);

        $this->assertFalse($this->isProductInStock->execute(self::PRODUCT_SKU, 1));
    }

    /**
     * @param string $sku
     * @return SourceItemInterface
     */
    private function getSourceItemBySKU(string $sku): SourceItemInterface
    {
        /** @var SearchCriteriaInterface $sourceItemSearchCriteria */
        $sourceItemSearchCriteria = $this->searchCriteriaBuilder->addFilter('sku', $sku)->create();
        $sourceItemSearchResult = $this->sourceItemRepository->getList($sourceItemSearchCriteria);

        /** @var SourceItemInterface $sourceItem */
        return current($sourceItemSearchResult->getItems());
    }

    /**
     * @param SourceItemInterface $sourceItem
     * @param float $qty
     */
    private function changeSourceItemQty(SourceItemInterface $sourceItem, float $qty)
    {
        $sourceItem->setQuantity($qty);
        $this->sourceItemsSaveInterface->execute([$sourceItem]);
        $this->indexer->reindexRow(5);
    }
}
