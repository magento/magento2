<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\AppendReservationsInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;

class ApplyDataToLegacyCatalogInventoryAtReservationPlacingTest extends TestCase
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
     * @var StockStatusCriteriaInterfaceFactory
     */
    private $legacyStockStatusCriteriaFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $legacyStockItemRepository;

    /**
     * @var StockStatusRepositoryInterface
     */
    private $legactStockStatusRepository;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->legacyStockItemCriteriaFactory = Bootstrap::getObjectManager()->get(
            StockItemCriteriaInterfaceFactory::class
        );
        $this->legacyStockStatusCriteriaFactory = Bootstrap::getObjectManager()->get(
            StockStatusCriteriaInterfaceFactory::class
        );

        $this->legacyStockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->legactStockStatusRepository = Bootstrap::getObjectManager()->get(StockStatusRepositoryInterface::class);

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
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

        $legacyStockStatusCriteria = $this->legacyStockStatusCriteriaFactory->create();
        $legacyStockStatusCriteria->setProductsFilter($productId);
        $legacyStockStatusCriteria->setScopeFilter($websiteId);
        $legacyStockStatusItems = $this->legactStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatusItems);

        $legacyStockStatusItem = reset($legacyStockStatusItems);
        self::assertEquals(1, $legacyStockStatusItem->getStockStatus());
        self::assertEquals(5.5, $legacyStockStatusItem->getQty());


        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(1)->setSku('SKU-1')->setQuantity(-2.5)->build()
        ]);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = current($legacyStockItems);
        self::assertEquals(3, $legacyStockItem->getQty());

        $legacyStockStatusItems = $this->legactStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatusItems);

        $legacyStockStatusItem = current($legacyStockItems);
        self::assertEquals(3, $legacyStockStatusItem->getQty());
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

        $legacyStockStatusCriteria = $this->legacyStockStatusCriteriaFactory->create();
        $legacyStockStatusCriteria->setProductsFilter($productId);
        $legacyStockStatusCriteria->setScopeFilter($websiteId);
        $legacyStockStatusItems = $this->legactStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatusItems);

        $legacyStockStatusItem = reset($legacyStockStatusItems);
        self::assertEquals(1, $legacyStockStatusItem->getStockStatus());
        self::assertEquals(5.5, $legacyStockStatusItem->getQty());

        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(1)->setSku('SKU-1')->setQuantity(-2.5)->build()
        ]);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = current($legacyStockItems);
        self::assertEquals(5.5, $legacyStockItem->getQty());

        $legacyStockStatusItems = $this->legactStockStatusRepository->getList($legacyStockStatusCriteria)->getItems();
        self::assertCount(1, $legacyStockStatusItems);

        $legacyStockStatusItem = current($legacyStockItems);
        self::assertEquals(5.5, $legacyStockStatusItem->getQty());


    }
}
