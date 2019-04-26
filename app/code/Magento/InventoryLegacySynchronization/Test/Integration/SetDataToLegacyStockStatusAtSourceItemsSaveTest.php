<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class SetDataToLegacyStockStatusAtSourceItemsSaveTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    private $legacyStockStatusCriteriaFactory;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $legacyStockStatusRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->legacyStockStatusCriteriaFactory = Bootstrap::getObjectManager()->get(
            StockStatusCriteriaInterfaceFactory::class
        );
        $this->legacyStockStatusRepository = Bootstrap::getObjectManager()->get(StockStatusRepositoryInterface::class);

        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);

        $this->sourceItemsSave = Bootstrap::getObjectManager()->get(SourceItemsSaveInterface::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testShouldSetDataToLegacyOnSourceItemSave(): void
    {
        $productSku = 'SKU-1';
        $product = $this->productRepository->get($productSku);
        $productId = $product->getId();
        $websiteId = 0;

        /** @var StockStatusCriteriaInterface $legacyStockStatusCriteria */
        $legacyStockStatusCriteria = $this->legacyStockStatusCriteriaFactory->create();
        $legacyStockStatusCriteria->setProductsFilter($productId);
        $legacyStockStatusCriteria->setScopeFilter($websiteId);
        $legacyStockStatuses = $this->legacyStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatuses);

        $legacyStockStatus = reset($legacyStockStatuses);
        self::assertEquals(Status::STATUS_IN_STOCK, $legacyStockStatus->getStockStatus());
        self::assertEquals(5.5, $legacyStockStatus->getQty());

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $productSku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $this->defaultSourceProvider->getCode())
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        self::assertCount(1, $sourceItems);

        $sourceItem = reset($sourceItems);
        $sourceItem->setQuantity(20.0);
        $sourceItem->setStatus(SourceItemInterface::STATUS_OUT_OF_STOCK);
        $this->sourceItemsSave->execute($sourceItems);

        $legacyStockStatuses = $this->legacyStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatuses);

        $legacyStockStatus = current($legacyStockStatuses);
        self::assertEquals(Status::STATUS_OUT_OF_STOCK, $legacyStockStatus->getStockStatus());
        self::assertEquals(20, $legacyStockStatus->getQty());
    }
}
