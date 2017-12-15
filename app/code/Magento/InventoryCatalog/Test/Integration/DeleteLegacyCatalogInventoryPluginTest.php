<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;

class DeleteLegacyCatalogInventoryPluginTest extends TestCase
{
    /**
     * @var StockItemRepositoryInterface
     */
    private $oldStockItemRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

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
     * @var SourceItemsDeleteInterface
     */
    private $sourceItemsDelete;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    protected function setUp()
    {
        $this->oldStockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->stockItemCriteriaFactory = Bootstrap::getObjectManager()->get(StockItemCriteriaInterfaceFactory::class);

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->create(SearchCriteriaBuilder::class);

        $this->sourceItemsDelete = Bootstrap::getObjectManager()->get(SourceItemsDeleteInterface::class);
        $this->stockRegistry = Bootstrap::getObjectManager()->get(StockRegistryInterface::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDbIsolation enabled
     */
    public function testDeleteDefaultSourceItemTriggersDeleteStockItem()
    {
        /** @var Product $product */
        $productSku = 'SKU-4';
        $product = $this->productRepository->get($productSku);

        /** @var StockItemCriteriaInterface  $criteria */
        $defaultStock = $this->stockRegistry->getStock();
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter([$product->getId()]);
        $criteria->setStockFilter($defaultStock);
        $criteria->addFilter('filter_is_in_stock', StockItemInterface::IS_IN_STOCK, true);

        /** @var StockItemCollectionInterface $collectionBeforeChange */
        $stockItemsBeforeDelete = $this->oldStockItemRepository->getList($criteria)->getItems();
        $this->assertCount(1, $stockItemsBeforeDelete);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $productSku, 'eq')
            ->addFilter(SourceItemInterface::SOURCE_ID, $this->defaultSourceProvider->getId(), 'eq')
            ->create();

        $sourceItemsBeforeDelete = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $this->assertCount(1, $sourceItemsBeforeDelete);

        $this->sourceItemsDelete->execute($sourceItemsBeforeDelete);

        $sourceItemsAfterDelete = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $this->assertCount(0, $sourceItemsAfterDelete);

        /** @var StockItemCollectionInterface $collectionBeforeChange */
        $stockItemsAfterDelete = $this->oldStockItemRepository->getList($criteria)->getItems();
        $this->assertCount(0, $stockItemsAfterDelete);
    }
}
