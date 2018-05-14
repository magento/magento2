<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Model\Spi\StockRegistryProvider;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test StockRegistryProviderInterface::getStockStatus() for simple product type.
 */
class GetStockStatusTest extends TestCase
{
    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreInterface
     */
    private $defaultStoreView;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockForCurrentWebsite;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemsSave
     */
    private $sourceItemSave;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stockRegistryProvider = Bootstrap::getObjectManager()->get(StockRegistryProviderInterface::class);
        $this->getProductIdsBySkus = Bootstrap::getObjectManager()->get(GetProductIdsBySkusInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
        $this->getStockForCurrentWebsite = Bootstrap::getObjectManager()->get(GetStockIdForCurrentWebsite::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->create(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $this->sourceItemSave = Bootstrap::getObjectManager()->create(SourceItemsSave::class);
        $this->defaultStoreView = $this->storeManager->getDefaultStoreView();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        //Revert custom store assignment.
        $this->storeManager->setCurrentStore($this->defaultStoreView->getId());
        parent::tearDown();
    }

    /**
     * Check, simple product has correct stock status configuration on default source.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testGetStatusOnDefaultSource()
    {
        $sku = 'simple';
        $stockId = $this->getStockForCurrentWebsite->execute();
        $productIds = $this->getProductIdsBySkus->execute([$sku]);
        $productId = reset($productIds);

        //Check product with 'In Stock' status.
        $stockStatus = $this->stockRegistryProvider->getStockStatus($productId, 1);
        $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
        $this->assertEquals(1, $stockStatus->getStockStatus());
        $this->assertEquals(100, $stockStatus->getQty());
        $this->assertEquals($stockId, $stockStatus->getStockId());
        $this->assertEquals($productId, $stockStatus->getProductId());

        $this->setProductOutOfStock($sku, 'default');

        //Check product with 'Out of Stock' status.
        $stockStatus = $this->stockRegistryProvider->getStockStatus($productId, 1);
        $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
        $this->assertEquals(0, $stockStatus->getStockStatus());
        $this->assertEquals(100, $stockStatus->getQty());
        $this->assertEquals($stockId, $stockStatus->getStockId());
        $this->assertEquals($productId, $stockStatus->getProductId());
    }

    /**
     * Check, simple product has correct stock status configuration on custom source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testGetStatusOnCustomSource()
    {
        $sku = 'SKU-2';
        $store = $this->storeRepository->get('store_for_us_website');
        $this->storeManager->setCurrentStore($store->getId());
        $stockId = $this->getStockForCurrentWebsite->execute();
        $productIds = $this->getProductIdsBySkus->execute([$sku]);
        $productId = reset($productIds);

        //Check product with 'In Stock' status.
        $stockStatus = $this->stockRegistryProvider->getStockStatus($productId, 1);
        $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
        $this->assertEquals(1, $stockStatus->getStockStatus());
        $this->assertEquals(5, $stockStatus->getQty());
        $this->assertEquals($stockId, $stockStatus->getStockId());
        $this->assertEquals($productId, $stockStatus->getProductId());

        $this->setProductOutOfStock($sku, 'us-1');

        //Check product with 'Out of Stock' status.
        $stockStatus = $this->stockRegistryProvider->getStockStatus($productId, 1);
        $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
        $this->assertEquals(0, $stockStatus->getStockStatus());
        $this->assertEquals(0, $stockStatus->getQty());
        $this->assertEquals($stockId, $stockStatus->getStockId());
        $this->assertEquals($productId, $stockStatus->getProductId());
    }

    /**
     * Set simple product to 'Out of Stock'.
     *
     * @param string $sku
     * @param string $sourceCode
     * @return void
     */
    private function setProductOutOfStock(string $sku, string $sourceCode)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $sourceItem = reset($sourceItems);
        $sourceItem->setStatus(0);
        $this->sourceItemSave->execute([$sourceItem]);
    }
}
