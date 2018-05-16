<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Test\Integration;

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
    protected function setUp()
    {
        parent::setUp();

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemDataInterface::class);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testOneSimpleChangesToOutOfStockInOneSource()
    {
        $this->changeStockStatusForSku('simple_11', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);

        $this->changeStockStatusForSku('simple_11', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);

        // EU-Stock
        $data = $this->getStockItemData->execute('configurable_1', 10);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute('configurable_1', 30);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testAllSimplesChangesToOutOfStockInOneSource()
    {
        $this->changeStockStatusForSku('simple_11', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);

        $this->changeStockStatusForSku('simple_11', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_21', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_31', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);

        // EU-Stock
        $data = $this->getStockItemData->execute('configurable_1', 10);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute('configurable_1', 30);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testAllSimplesChangesToOutOfStockInAllSources()
    {
        $this->changeStockStatusForSku('simple_11', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_21', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_31', 'us-1', SourceItemInterface::STATUS_OUT_OF_STOCK);

        $this->changeStockStatusForSku('simple_11', 'eu-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_21', 'eu-1', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_31', 'eu-1', SourceItemInterface::STATUS_OUT_OF_STOCK);

        $this->changeStockStatusForSku('simple_11', 'eu-2', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_21', 'eu-2', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_31', 'eu-2', SourceItemInterface::STATUS_OUT_OF_STOCK);

        $this->changeStockStatusForSku('simple_11', 'eu-3', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_21', 'eu-3', SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->changeStockStatusForSku('simple_31', 'eu-3', SourceItemInterface::STATUS_OUT_OF_STOCK);

        // EU-Stock
        $data = $this->getStockItemData->execute('configurable_1', 10);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute('configurable_1', 30);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/set_simples_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testOneSimpleChangesToInStock()
    {
        $this->changeStockStatusForSku('simple_21', 'us-1', SourceItemInterface::STATUS_IN_STOCK);

        // EU-Stock
        $data = $this->getStockItemData->execute('configurable_1', 10);
        $this->assertEquals(0, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute('configurable_1', 30);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);
    }

    // @codingStandardsIgnoreStart
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/product_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/source_items_configurable_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProductIndexer/Test/_files/set_simples_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @return void
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testAllSimplesChangesToInStock()
    {
        $this->changeStockStatusForSku('simple_11', 'us-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_21', 'us-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_31', 'us-1', SourceItemInterface::STATUS_IN_STOCK);

        $this->changeStockStatusForSku('simple_11', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_21', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_31', 'eu-1', SourceItemInterface::STATUS_IN_STOCK);

        $this->changeStockStatusForSku('simple_11', 'eu-2', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_21', 'eu-2', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_31', 'eu-2', SourceItemInterface::STATUS_IN_STOCK);

        $this->changeStockStatusForSku('simple_11', 'eu-3', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_21', 'eu-3', SourceItemInterface::STATUS_IN_STOCK);
        $this->changeStockStatusForSku('simple_31', 'eu-3', SourceItemInterface::STATUS_IN_STOCK);

        // EU-Stock
        $data = $this->getStockItemData->execute('configurable_1', 10);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // US-Stock
        $data = $this->getStockItemData->execute('configurable_1', 20);
        $this->assertEquals(1, $data[GetStockItemDataInterface::IS_SALABLE]);

        // Global-Stock
        $data = $this->getStockItemData->execute('configurable_1', 30);
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
