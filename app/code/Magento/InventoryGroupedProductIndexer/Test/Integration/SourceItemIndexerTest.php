<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductIndexer\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SourceItemIndexerTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
    private $sourceItemsSave;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->sourceItemRepository = $objectManager->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->sourceItemsSave = $objectManager->get(SourceItemsSaveInterface::class);
        $this->getStockItemData = $objectManager->get(GetStockItemDataInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/custom_stock_with_eu_website_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     */
    public function testOneSimpleChangesToOutOfStockInOneSource()
    {
        $groupedSku = 'grouped_in_stock';
        $this->changeStockStatusForSku('simple_11', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_11', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);

        // EU-Stock
        $data = $this->getStockItemData->execute($groupedSku, 10);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute($groupedSku, 20);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute($groupedSku, 30);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/custom_stock_with_eu_website_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     */
    public function testAllSimplesChangesToOutOfStockInOneSource()
    {
        $this->changeStockStatusForSku('simple_11', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_11', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_22', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $groupedSku = 'grouped_in_stock';

        // EU-Stock
        $data = $this->getStockItemData->execute($groupedSku, 10);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute($groupedSku, 20);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute($groupedSku, 30);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/custom_stock_with_eu_website_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     */
    public function testAllSimplesChangesToOutOfStockInAllSources()
    {
        $this->changeStockStatusForSku('simple_11', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_22', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_11', 'eu-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_22', 'eu-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_11', 'eu-2', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_22', 'eu-2', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_11', 'eu-3', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_22', 'eu-3', SourceItemInterface::STATUS_OUT_OF_STOCK);

        $groupedSku = 'grouped_in_stock';
        // EU-Stock
        $data = $this->getStockItemData->execute($groupedSku, 10);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute($groupedSku, 20);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute($groupedSku, 30);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/custom_stock_with_eu_website_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     */
    public function testAllSimplesChangesToInStock()
    {
        $this->changeStockStatusForSku('simple_11', 'us-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_22', 'us-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_11', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_22', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_11', 'eu-2', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_22', 'eu-2', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_11', 'eu-3', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_22', 'eu-3', SourceItemInterface::STATUS_IN_STOCK);

        $groupedSku = 'grouped_in_stock';
        // EU-Stock
        $data = $this->getStockItemData->execute($groupedSku, 10);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute($groupedSku, 20);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute($groupedSku, 30);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);
    }

    /**
     * @param string $sku
     * @param string $sourceCode
     * @param int $stockStatus
     */
    private function changeStockStatusForSku(string $sku, string $sourceCode, int $stockStatus)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        foreach ($sourceItems as $sourceItem) {
            $sourceItem->setStatus($stockStatus);
        }

        $this->sourceItemsSave->execute($sourceItems);
    }
}
