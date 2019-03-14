<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\GetStockItemData;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class BackorderConditionTest extends TestCase
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemDataInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->stockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->stockItemCriteriaFactory = Bootstrap::getObjectManager()->get(
            StockItemCriteriaInterfaceFactory::class
        );
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
    }

    /**
     * Tests inventory_stock_* is_salable value when backorders are globally disabled.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/backorders 0
     * @dataProvider backordersDisabledDataProvider
     *
     * @param string $sku
     * @param int $stockId
     * @param array|null $expectedData
     */
    public function testBackordersDisabled(string $sku, int $stockId, $expectedData)
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * Tests inventory_stock_* is_salable value when backorders are globally enabled.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/backorders 1
     * @dataProvider backordersEnabledDataProvider
     *
     * @param string $sku
     * @param int $stockId
     * @param array|null $expectedData
     */
    public function testGlobalBackordersEnabled(string $sku, int $stockId, $expectedData)
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * Tests inventory_stock_* is_salable value when backorders for stock items are disabled.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/backorders 1
     * @dataProvider backordersDisabledDataProvider
     *
     * @param string $sku
     * @param int $stockId
     * @param array|null $expectedData
     */
    public function testStockItemBackordersDisabled(string $sku, int $stockId, $expectedData)
    {
        $this->setStockItemBackorders($sku, 0);

        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * Tests inventory_stock_* is_salable value when backorders for stock items are enabled.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/backorders 0
     * @dataProvider backordersEnabledDataProvider
     *
     * @param string $sku
     * @param int $stockId
     * @param array|null $expectedData
     */
    public function testStockItemBackordersEnabled(string $sku, int $stockId, $expectedData)
    {
        $this->setStockItemBackorders($sku, 1);

        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * Data provider for test with enabled backorders.
     *
     * @return array
     */
    public function backordersEnabledDataProvider(): array
    {
        return [
            ['SKU-1', 10, [GetStockItemDataInterface::QUANTITY => 8.5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-2', 10, null],
            ['SKU-3', 10, [GetStockItemDataInterface::QUANTITY => 0, GetStockItemDataInterface::IS_SALABLE => 1]],
        ];
    }

    /**
     * Data provider for test with disabled backorders.
     *
     * @return array
     */
    public function backordersDisabledDataProvider(): array
    {
        return [
            ['SKU-1', 10, [GetStockItemDataInterface::QUANTITY => 8.5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-2', 10, null],
            ['SKU-3', 10, [GetStockItemDataInterface::QUANTITY => 0, GetStockItemDataInterface::IS_SALABLE => 0]],
        ];
    }

    /**
     * Set products backorder status.
     *
     * @param string $sku
     * @param int $backordersStatus
     */
    private function setStockItemBackorders(string $sku, int $backordersStatus): void
    {
        $product = $this->productRepository->get($sku);
        $stockItemSearchCriteria = $this->stockItemCriteriaFactory->create();
        $stockItemSearchCriteria->setProductsFilter($product->getId());
        $stockItemsCollection = $this->stockItemRepository->getList($stockItemSearchCriteria);

        /** @var StockItemInterface $legacyStockItem */
        $legacyStockItem = current($stockItemsCollection->getItems());
        $legacyStockItem->setBackorders($backordersStatus);
        $legacyStockItem->setUseConfigBackorders(0);
        $this->stockItemRepository->save($legacyStockItem);

        $sourceItem = $this->getSourceItemBySku($sku);
        $this->sourceItemsSave->execute([$sourceItem]);
    }

    /**
     * Get source item by products sku.
     *
     * @param string $sku
     * @return SourceItemInterface
     */
    private function getSourceItemBySku(string $sku): SourceItemInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $sku)
            ->create();
        $sourceItemSearchResult = $this->sourceItemRepository->getList($searchCriteria);

        return current($sourceItemSearchResult->getItems());
    }
}
