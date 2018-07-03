<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryProductAlert\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\ProductAlert\Model\Observer;
use Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory as StockCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Check product alerts work on multi source.
 */
class ProductAlertTest extends TestCase
{
    /**
     * @var Observer
     */
    private $observer;

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
     * @var StockCollectionFactory
     */
    private $stockCollectionFactory;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->observer = Bootstrap::getObjectManager()->create(Observer::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemsSaveInterface = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        $this->stockCollectionFactory = Bootstrap::getObjectManager()->get(StockCollectionFactory::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/product_alert_customer.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/customer_eu_website_id.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/product_alert_eu_website_customer.php
     * @magentoConfigFixture default_store catalog/productalert/allow_stock 1
     * @magentoConfigFixture store_for_eu_website_store catalog/productalert/allow_stock 1
     *
     * @magentoDbIsolation disabled
     */
    public function testAlertsBothSourceItemsOutOfStock()
    {
        $this->observer->process();
        $stockCollection = $this->stockCollectionFactory->create();
        $count = 0;
        /** @var \Magento\ProductAlert\Model\Stock $stock */
        foreach ($stockCollection as $stock) {
            $count += $stock->getSendCount();
        }
        $this->assertEquals(0, $count);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/product_alert_customer.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/customer_eu_website_id.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/product_alert_eu_website_customer.php
     * @magentoConfigFixture default_store catalog/productalert/allow_stock 1
     * @magentoConfigFixture store_for_eu_website_store catalog/productalert/allow_stock 1
     *
     * @magentoDbIsolation disabled
     */
    public function testAlertsOneSourceItemInStock()
    {
        $this->observer->process();
        $stockCollection = $this->stockCollectionFactory->create();
        $count = 0;
        /** @var \Magento\ProductAlert\Model\Stock $stock */
        foreach ($stockCollection as $stock) {
            $count += $stock->getSendCount();
        }
        $this->assertEquals(0, $count);

        $this->changeProductIsInStock('eu-2', 1);
        $this->observer->process();

        $stockCollection = $this->stockCollectionFactory->create();
        $count = 0;
        /** @var \Magento\ProductAlert\Model\Stock $stock */
        foreach ($stockCollection as $stock) {
            $count += $stock->getSendCount();
        }
        $this->assertEquals(1, $count);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/product_alert_customer.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/customer_eu_website_id.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryProductAlert/Test/_files/product_alert_eu_website_customer.php
     * @magentoConfigFixture default_store catalog/productalert/allow_stock 1
     * @magentoConfigFixture store_for_eu_website_store catalog/productalert/allow_stock 1
     *
     * @magentoDbIsolation disabled
     */
    public function testAlertsBothSourceItemsInStock()
    {
        $this->changeProductIsInStock('eu-2', 1);
        $this->changeProductIsInStock('default', 1);
        $this->observer->process();

        $stockCollection = $this->stockCollectionFactory->create();
        $count = 0;
        /** @var \Magento\ProductAlert\Model\Stock $stock */
        foreach ($stockCollection as $stock) {
            $count += $stock->getSendCount();
        }
        $this->assertEquals(2, $count);
    }

    /**
     * @param string $sourceCode
     * @param int $isInStock
     *
     * @return void
     */
    private function changeProductIsInStock(string $sourceCode, int $isInStock)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, 'SKU-3')
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->create();

        $items = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        /** @var SourceItemInterface $sourceItem */
        $sourceItem = reset($items);
        $sourceItem->setStatus($isInStock);
        if ($isInStock) {
            $sourceItem->setQuantity($sourceItem->getQuantity() ?: 1);
        }
        $this->sourceItemsSaveInterface->execute([$sourceItem]);
    }
}
