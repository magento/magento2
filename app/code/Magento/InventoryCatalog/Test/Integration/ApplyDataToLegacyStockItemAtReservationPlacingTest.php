<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Inventory\Model\CleanupReservationsInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\AppendReservationsInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;

class ApplyDataToLegacyStockItemAtReservationPlacingTest extends TestCase
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
        $this->markTestIncomplete('https://github.com/magento-engcom/msi/issues/368');
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->legacyStockItemCriteriaFactory = Bootstrap::getObjectManager()->get(
            StockItemCriteriaInterfaceFactory::class
        );
        $this->legacyStockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);

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
     * @magentoConfigFixture current_store cataloginventory/options/can_subtract 1
     */
    public function testApplyDataIfCanSubtractOptionIsEnabled()
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
        self::assertEquals(5.5, $legacyStockItem->getQty());

        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(-2.5)->build()
        ]);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = current($legacyStockItems);
        self::assertEquals(1, $legacyStockItem->getIsInStock());
        self::assertEquals(3, $legacyStockItem->getQty());

        $this->appendReservations->execute([
            // unreserved units for cleanup
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(2.5)->build(),
        ]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoConfigFixture current_store cataloginventory/options/can_subtract 1
     */
    public function testApplyDataIfCanSubtractOptionIsEnabledAndProductBecameOutOfStock()
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
        self::assertEquals(5.5, $legacyStockItem->getQty());

        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(-5.5)->build()
        ]);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = current($legacyStockItems);
        self::assertEquals(0, $legacyStockItem->getIsInStock());
        self::assertEquals(0, $legacyStockItem->getQty());

        $this->appendReservations->execute([
            // unreserved units for cleanup
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(5.5)->build(),
        ]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoConfigFixture current_store cataloginventory/options/can_subtract 0
     */
    public function testApplyDataIfCanSubtractOptionIsDisabled()
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
        self::assertEquals(5.5, $legacyStockItem->getQty());

        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(-2.5)->build()
        ]);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = current($legacyStockItems);
        self::assertEquals(1, $legacyStockItem->getIsInStock());
        self::assertEquals(5.5, $legacyStockItem->getQty());

        $this->appendReservations->execute([
            // unreserved units for cleanup
            $this->reservationBuilder->setStockId(1)->setSku($productSku)->setQuantity(2.5)->build(),
        ]);
    }
}
