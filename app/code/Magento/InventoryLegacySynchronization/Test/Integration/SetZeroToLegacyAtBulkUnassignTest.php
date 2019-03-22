<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalogApi\Api\BulkSourceUnassignInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;

class SetZeroToLegacyAtBulkUnassignTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $legacyStockItemCriteriaFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $legacyStockItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var BulkSourceUnassignInterface
     */
    private $bulkSourceUnassign;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->legacyStockItemCriteriaFactory = Bootstrap::getObjectManager()->get(
            StockItemCriteriaInterfaceFactory::class
        );
        $this->legacyStockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);

        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);

        $this->bulkSourceUnassign = Bootstrap::getObjectManager()->get(BulkSourceUnassignInterface::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function testShouldSetLegacyQuantityToZeroOnBulkUnassign(): void
    {
        $productSku = 'SKU-1';
        $product = $this->productRepository->get($productSku);
        $productId = $product->getId();
        $websiteId = 0;

        /** @var StockItemCriteriaInterface $legacyStockItemCriteria */
        $legacyStockItemCriteria = $this->legacyStockItemCriteriaFactory->create();
        $legacyStockItemCriteria->setProductsFilter($productId);
        $legacyStockItemCriteria->setScopeFilter($websiteId);
        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = reset($legacyStockItems);
        self::assertTrue($legacyStockItem->getIsInStock());
        self::assertSame(5.5, (float) $legacyStockItem->getQty());

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $productSku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $this->defaultSourceProvider->getCode())
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        self::assertCount(1, $sourceItems);

        $this->bulkSourceUnassign->execute(['SKU-1'], [$this->defaultSourceProvider->getCode()]);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = reset($legacyStockItems);
        self::assertFalse($legacyStockItem->getIsInStock());
        self::assertSame(0.0, (float) $legacyStockItem->getQty());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function testShouldNotSetLegacyQuantityToZeroOnBulkUnassignNotInvolvingDefaultSource(): void
    {
        $productSku = 'SKU-1';
        $product = $this->productRepository->get($productSku);
        $productId = $product->getId();
        $websiteId = 0;

        /** @var StockItemCriteriaInterface $legacyStockItemCriteria */
        $legacyStockItemCriteria = $this->legacyStockItemCriteriaFactory->create();
        $legacyStockItemCriteria->setProductsFilter($productId);
        $legacyStockItemCriteria->setScopeFilter($websiteId);
        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = reset($legacyStockItems);
        self::assertTrue($legacyStockItem->getIsInStock());
        self::assertSame(5.5, (float) $legacyStockItem->getQty());

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $productSku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $this->defaultSourceProvider->getCode())
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        self::assertCount(1, $sourceItems);

        $this->bulkSourceUnassign->execute(['SKU-1'], ['eu-1']);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = reset($legacyStockItems);
        self::assertTrue($legacyStockItem->getIsInStock());
        self::assertSame(5.5, (float) $legacyStockItem->getQty());
    }
}
