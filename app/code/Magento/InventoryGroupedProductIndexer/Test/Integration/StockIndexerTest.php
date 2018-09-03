<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductIndexer\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\GroupedProduct\Model\Product\Link\CollectionProvider\Grouped;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;
use Magento\InventoryIndexer\Model\ResourceModel\GetStockItemData;
use Magento\InventoryIndexer\Test\Integration\Indexer\RemoveIndexData;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StockIndexerTest extends TestCase
{
    /**
     * @var StockIndexer
     */
    private $stockIndexer;

    /**
     * @var GetStockItemData
     */
    private $getStockItemData;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    /**
     * @var Grouped
     */
    private $groupedProducts;

    /**
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemsBySku;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemSave;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->stockIndexer = $objectManager->get(StockIndexer::class);
        $this->getStockItemData = $objectManager->get(GetStockItemData::class);
        $this->groupedProducts = $objectManager->get(Grouped::class);
        $this->getSourceItemsBySku = $objectManager->get(GetSourceItemsBySkuInterface::class);
        $this->sourceItemSave = $objectManager->get(SourceItemsSaveInterface::class);
        $this->sourceItemRepository = $objectManager->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $this->storeRepository = $objectManager->get(StoreRepository::class);
        $this->removeIndexData = $objectManager->get(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     *
     * @magentoDbIsolation disabled
     */
    public function testReindexList()
    {
        $groupedSku = 'grouped_in_stock';

        $this->stockIndexer->executeList([10, 20, 30]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 10);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 20);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 30);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/custom_stock_with_eu_website_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     *
     * @magentoDbIsolation disabled
     */
    public function testReindexListSetAllSimplesOutOfStock()
    {
        $groupedSku = 'grouped_in_stock';
        $store = $this->storeRepository->get('store_for_us_website');
        $this->storeManager->setCurrentStore($store->getId());
        $groupedProduct = $this->productRepository->get($groupedSku);
        $children = $this->groupedProducts->getLinkedProducts($groupedProduct);

        /** @var Product $child */
        foreach ($children as $child) {
            $sku = $child->getSku();
            $sourceItems = $this->getSourceItemsBySku->execute($sku);
            $changesSourceItems = [];
            foreach ($sourceItems as $sourceItem) {
                $sourceItem->setStatus(SourceItemInterface::STATUS_OUT_OF_STOCK);
                $changesSourceItems[] = $sourceItem;
            }
            $this->sourceItemSave->execute($changesSourceItems);
        }

        $this->removeIndexData->execute([10, 20, 30]);
        $this->stockIndexer->executeList([10, 20, 30]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 10);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 20);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 30);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/custom_stock_with_eu_website_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     *
     * @magentoDbIsolation disabled
     */
    public function testReindexListSetAllEuSimplesOutOfStock()
    {
        $groupedSku = 'grouped_in_stock';
        $sourceCodes = ['eu-1', 'eu-2', 'eu-3'];
        $store = $this->storeRepository->get('store_for_us_website');
        $this->storeManager->setCurrentStore($store->getId());
        $groupedProduct = $this->productRepository->get($groupedSku);
        $children = $this->groupedProducts->getLinkedProducts($groupedProduct);

        foreach ($children as $child) {
            $sku = $child->getSku();
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(SourceItemInterface::SKU, $sku)
                ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCodes, 'in')
                ->create();
            $sourceItems = $this->sourceItemRepository->getList($searchCriteria);
            $changesSourceItems = [];
            foreach ($sourceItems->getItems() as $sourceItem) {
                $sourceItem->setStatus(SourceItemInterface::STATUS_OUT_OF_STOCK);
                $changesSourceItems[] = $sourceItem;
            }
            $this->sourceItemSave->execute($changesSourceItems);
        }

        $this->removeIndexData->execute([10, 20, 30]);
        $this->stockIndexer->executeList([10, 20, 30]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 10);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 20);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($groupedSku, 30);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }
}
