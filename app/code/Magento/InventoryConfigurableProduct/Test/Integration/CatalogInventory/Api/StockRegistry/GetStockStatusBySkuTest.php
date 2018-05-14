<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
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
 * Test StockRegistryInterface::getStockStatusBySku() for configurable product type.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetStockStatusBySkuTest extends TestCase
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var LinkManagementInterface
     */
    private $linkManagement;

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
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockItemCriteriaInterface
     */
    private $stockItemCriteria;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->stockRegistry = Bootstrap::getObjectManager()->get(StockRegistryInterface::class);
        $this->getProductIdsBySkus = Bootstrap::getObjectManager()->get(GetProductIdsBySkusInterface::class);
        $this->linkManagement = Bootstrap::getObjectManager()->get(LinkManagementInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeRepository = Bootstrap::getObjectManager()->get(StoreRepositoryInterface::class);
        $this->getStockForCurrentWebsite = Bootstrap::getObjectManager()->get(GetStockIdForCurrentWebsite::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->create(SourceItemRepositoryInterface::class);
        $this->stockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);
        $this->stockItemCriteria = Bootstrap::getObjectManager()->create(StockItemCriteriaInterface::class);
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
     * Check, configurable and it's children have correct stock status configuration on default source.
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoAppArea frontend
     * @return void
     */
    public function testGetStatusOnDefaultSource()
    {
        $stockId = $this->getStockForCurrentWebsite->execute();
        $productIds = $this->getProductIdsBySkus->execute(['configurable']);
        $configurableProductId = reset($productIds);
        $children = $this->linkManagement->getChildren('configurable');
        $products[] = [
            'product_id' => $configurableProductId,
            'sku' => 'configurable',
            'stock_id' => $stockId,
            'stock_status' => 1,
            'qty' => 0,
        ];
        foreach ($children as $child) {
            $products[] = [
                'product_id' => $child->getId(),
                'sku' => $child->getSku(),
                'stock_id' => $stockId,
                'stock_status' => 1,
                'qty' => 1000,
            ];
        }

        //Check products with 'In Stock' statuses.
        foreach ($products as $product) {
            $stockStatus = $this->stockRegistry->getStockStatusBySku($product['sku']);
            $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
            $this->assertEquals($product['stock_status'], $stockStatus->getStockStatus());
            $this->assertEquals($product['qty'], $stockStatus->getQty());
            $this->assertEquals($product['stock_id'], $stockStatus->getStockId());
            $this->assertEquals($product['product_id'], $stockStatus->getProductId());
        }

        $this->setProductsOutOfStock($products, 'default');

        //Check products with 'Out of Stock' statuses.
        foreach ($products as $product) {
            $stockStatus = $this->stockRegistry->getStockStatusBySku($product['sku']);
            $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
            $this->assertEquals(0, $stockStatus->getStockStatus());
            $this->assertEquals($product['qty'], $stockStatus->getQty());
            $this->assertEquals($product['stock_id'], $stockStatus->getStockId());
            $this->assertEquals($product['product_id'], $stockStatus->getProductId());
        }
    }

    /**
     * Check, configurable and it's children have correct stock status configuration on custom source.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testGetStatusOnCustomSource()
    {
        $store = $this->storeRepository->get('store_for_us_website');
        $this->storeManager->setCurrentStore($store->getId());
        $stockId = $this->getStockForCurrentWebsite->execute();
        $productIds = $this->getProductIdsBySkus->execute(['configurable']);
        $configurableProductId = reset($productIds);
        $children = $this->linkManagement->getChildren('configurable');
        $products[] = [
            'product_id' => $configurableProductId,
            'sku' => 'configurable',
            'stock_id' => $stockId,
            'stock_status' => 1,
            'qty' => 200,
        ];
        foreach ($children as $child) {
            $products[] = [
                'product_id' => $child->getId(),
                'sku' => $child->getSku(),
                'stock_id' => $stockId,
                'stock_status' => 1,
                'qty' => 100,
            ];
        }

        //Check products with 'In Stock' statuses.
        foreach ($products as $product) {
            $stockStatus = $this->stockRegistry->getStockStatusBySku($product['sku']);
            $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
            $this->assertEquals($product['stock_status'], $stockStatus->getStockStatus());
            $this->assertEquals($product['qty'], $stockStatus->getQty());
            $this->assertEquals($product['stock_id'], $stockStatus->getStockId());
            $this->assertEquals($product['product_id'], $stockStatus->getProductId());
        }

        $this->setProductsOutOfStock($products, 'us-1');

        //Check products with 'Out of Stock' statuses.
        foreach ($products as $product) {
            $stockStatus = $this->stockRegistry->getStockStatusBySku($product['sku']);
            $this->assertInstanceOf(StockStatusInterface::class, $stockStatus);
            $this->assertEquals(0, $stockStatus->getStockStatus());
            $this->assertEquals(0, $stockStatus->getQty());
            $this->assertEquals($product['stock_id'], $stockStatus->getStockId());
            $this->assertEquals($product['product_id'], $stockStatus->getProductId());
        }
    }

    /**
     * Set configurable and it's children to 'Out of Stock'.
     *
     * @param array $products
     * @param string $sourceCode
     * @return void
     */
    private function setProductsOutOfStock(array $products, string $sourceCode)
    {
        foreach ($products as $product) {
            if ($product['sku'] === 'configurable') {
                $this->stockItemCriteria->setProductsFilter([$product['product_id']]);
                $stockItems = $this->stockItemRepository->getList($this->stockItemCriteria)->getItems();
                $configurableStockItem = reset($stockItems);
                $configurableStockItem->setIsInStock(false);
                $this->stockItemRepository->save($configurableStockItem);
            } else {
                $searchCriteria = $this->searchCriteriaBuilder
                    ->addFilter(SourceItemInterface::SKU, $product['sku'])
                    ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
                    ->create();
                $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
                $sourceItem = reset($sourceItems);
                $sourceItem->setStatus(0);
                $this->sourceItemSave->execute([$sourceItem]);
            }
        }
    }
}
