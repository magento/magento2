<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Test\Integration;

use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;
use Magento\InventoryIndexer\Model\ResourceModel\GetStockItemData;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\InventoryIndexer\Test\Integration\Indexer\RemoveIndexData;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;

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
     * @var LinkManagementInterface
     */
    private $linkManagement;

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
    protected function setUp()
    {
        parent::setUp();

        $this->stockIndexer = Bootstrap::getObjectManager()->get(StockIndexer::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemData::class);
        $this->linkManagement = Bootstrap::getObjectManager()->get(LinkManagementInterface::class);
        $this->getSourceItemsBySku = Bootstrap::getObjectManager()->get(GetSourceItemsBySkuInterface::class);
        $this->sourceItemSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepository::class);
        $this->removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);
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
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testReindexList()
    {
        $configurableSku = 'configurable_1';

        $this->stockIndexer->executeList([10, 20, 30]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 10);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 20);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 30);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
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
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testReindexListSetAllSimplesOutOfStock()
    {
        $configurableSku = 'configurable_1';
        $store = $this->storeRepository->get('store_for_us_website');
        $this->storeManager->setCurrentStore($store->getId());
        $children = $this->linkManagement->getChildren($configurableSku);

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

        $stockItemData = $this->getStockItemData->execute($configurableSku, 10);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 20);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 30);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
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
     *
     * @magentoDbIsolation disabled
     */
    // @codingStandardsIgnoreEnd
    public function testReindexListSetAllEuSimplesOutOfStock()
    {
        $configurableSku = 'configurable_1';

        $sourceCodes = ['eu-1', 'eu-2', 'eu-3'];
        $store = $this->storeRepository->get('store_for_us_website');
        $this->storeManager->setCurrentStore($store->getId());
        $children = $this->linkManagement->getChildren($configurableSku);

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

        $stockItemData = $this->getStockItemData->execute($configurableSku, 10);
        self::assertEquals(0, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 20);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);

        $stockItemData = $this->getStockItemData->execute($configurableSku, 30);
        self::assertEquals(1, $stockItemData[GetStockItemDataInterface::IS_SALABLE]);
    }
}
