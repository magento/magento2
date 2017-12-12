<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class UpdateCatalogInventoryOnSourceItemsSaveTest extends TestCase
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StockItemRepositoryInterface
     */
    private $oldStockItemRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    protected function setUp()
    {
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->oldStockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->stockItemCriteriaFactory = Bootstrap::getObjectManager()->get(StockItemCriteriaInterfaceFactory::class);
        $this->stockRegistry = Bootstrap::getObjectManager()->get(StockRegistryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
        $this->sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDbIsolation enabled
     */
    public function testIfDefaultStockItemIsUpdatedWhenSourceItemIsSaved()
    {
        /** @var Product $product */
        $productSku = 'SKU-4';
        $product = $this->productRepository->get($productSku);

        /** @var StockItemCriteriaInterface  $criteria */
        $defaultStock = $this->stockRegistry->getStock();
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter([$product->getId()]);
        $criteria->setStockFilter($defaultStock);

        /** @var SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $productSku, 'eq')
            ->addFilter(SourceItemInterface::SOURCE_ID, $this->defaultSourceProvider->getId(), 'eq')
            ->create();

        /** @var StockItemCollectionInterface $collectionBeforeChange */
        $stockItemsBeforeUpdate = $this->oldStockItemRepository->getList($criteria)->getItems();
        $this->assertCount(1, $stockItemsBeforeUpdate);

        /** @var StockItemInterface $stockItem */
        $stockItem = current($stockItemsBeforeUpdate);
        $stockItemQtyBeforeUpdate = $stockItem->getQty();
        $this->assertEquals(10, $stockItemQtyBeforeUpdate);

        $sourceItemsBeforeUpdate = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $this->assertCount(1, $sourceItemsBeforeUpdate);

        /** @var SourceItemInterface $sourceItem */
        $sourceItem = current($sourceItemsBeforeUpdate);
        $sourceItemQtyBeforeUpdate = $sourceItem->getQuantity();
        $this->assertEquals(10, $sourceItemQtyBeforeUpdate);

        $sourceItem->setQuantity(20);
        $this->sourceItemsSave->execute([$sourceItem]);

        /** @var StockItemCollectionInterface $collectionBeforeChange */
        $stockItemsAfterUpdate = $this->oldStockItemRepository->getList($criteria)->getItems();
        $this->assertCount(1, $stockItemsAfterUpdate);

        /** @var StockItemInterface $stockItem */
        $stockItem = current($stockItemsAfterUpdate);
        $stockItemQtyAfterUpdate = $stockItem->getQty();
        $this->assertEquals(20, $stockItemQtyAfterUpdate);

        $sourceItemsAfterUpdate = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $this->assertCount(1, $sourceItemsAfterUpdate);

        $sourceItem = current($sourceItemsAfterUpdate);
        $sourceItemQtyAfterUpdate = $sourceItem->getQuantity();
        $this->assertEquals(20, $sourceItemQtyAfterUpdate);
    }
}
