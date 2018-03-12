<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\InventoryReservationsApi;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\InventoryReservations\Model\CleanupReservationsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class ApplyDataToLegacyStockStatusAtReservationPlacingTest extends TestCase
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
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @var CleanupReservationsInterface
     */
    private $reservationCleanup;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->legacyStockStatusCriteriaFactory = Bootstrap::getObjectManager()->get(
            StockStatusCriteriaInterfaceFactory::class
        );
        $this->legacyStockStatusRepository = Bootstrap::getObjectManager()->get(StockStatusRepositoryInterface::class);

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->reservationCleanup = Bootstrap::getObjectManager()->create(CleanupReservationsInterface::class);

        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    }

    /**
     * We broke transaction during indexation so we need to clean db state manually
     */
    protected function tearDown()
    {
        $this->reservationCleanup->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/options/can_subtract 1
     */
    public function testApplyDataIfCanSubtractOptionIsEnabled()
    {
        $productSku = 'SKU-1';
        $product = $this->productRepository->get($productSku);
        $productId = $product->getId();
        $websiteId = 0;

        $legacyStockStatusCriteria = $this->legacyStockStatusCriteriaFactory->create();
        $legacyStockStatusCriteria->setProductsFilter($productId);
        $legacyStockStatusCriteria->setScopeFilter($websiteId);
        $legacyStockStatuses = $this->legacyStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatuses);

        $legacyStockStatus = reset($legacyStockStatuses);
        self::assertEquals(Status::STATUS_IN_STOCK, $legacyStockStatus->getStockStatus());
        self::assertEquals(5.5, $legacyStockStatus->getQty());

        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(-2.5)->build()
        ]);

        $legacyStockStatuses = $this->legacyStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatuses);

        $legacyStockStatus = current($legacyStockStatuses);
        self::assertEquals(Status::STATUS_IN_STOCK, $legacyStockStatus->getStockStatus());
        self::assertEquals(3, $legacyStockStatus->getQty());

        $this->appendReservations->execute([
            // unreserved units for cleanup
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(2.5)->build(),
        ]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/options/can_subtract 1
     */
    public function testApplyDataIfCanSubtractOptionIsEnabledAndProductBecameOutOfStock()
    {
        $productSku = 'SKU-1';
        $product = $this->productRepository->get($productSku);
        $productId = $product->getId();
        $websiteId = 0;

        $legacyStockStatusCriteria = $this->legacyStockStatusCriteriaFactory->create();
        $legacyStockStatusCriteria->setProductsFilter($productId);
        $legacyStockStatusCriteria->setScopeFilter($websiteId);
        $legacyStockStatuses = $this->legacyStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatuses);

        $legacyStockStatus = reset($legacyStockStatuses);
        self::assertEquals(Status::STATUS_IN_STOCK, $legacyStockStatus->getStockStatus());
        self::assertEquals(5.5, $legacyStockStatus->getQty());

        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(-5.5)->build()
        ]);

        $legacyStockStatuses = $this->legacyStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatuses);

        $legacyStockStatus = current($legacyStockStatuses);
        self::assertEquals(Status::STATUS_OUT_OF_STOCK, $legacyStockStatus->getStockStatus());
        self::assertEquals(0, $legacyStockStatus->getQty());

        $this->appendReservations->execute([
            // unreserved units for cleanup
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(5.5)->build(),
        ]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/options/can_subtract 0
     */
    public function testApplyDataIfCanSubtractOptionIsDisabled()
    {
        $productSku = 'SKU-1';
        $product = $this->productRepository->get($productSku);
        $productId = $product->getId();
        $websiteId = 0;

        $legacyStockStatusCriteria = $this->legacyStockStatusCriteriaFactory->create();
        $legacyStockStatusCriteria->setProductsFilter($productId);
        $legacyStockStatusCriteria->setScopeFilter($websiteId);
        $legacyStockStatuses = $this->legacyStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatuses);

        $legacyStockStatus = reset($legacyStockStatuses);
        self::assertEquals(Status::STATUS_IN_STOCK, $legacyStockStatus->getStockStatus());
        self::assertEquals(5.5, $legacyStockStatus->getQty());

        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(-2.5)->build()
        ]);

        $legacyStockStatuses = $this->legacyStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatuses);

        $legacyStockStatus = current($legacyStockStatuses);
        self::assertEquals(Status::STATUS_IN_STOCK, $legacyStockStatus->getStockStatus());
        self::assertEquals(5.5, $legacyStockStatus->getQty());

        $this->appendReservations->execute([
            // unreserved units for cleanup
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(2.5)->build(),
        ]);
    }
}
