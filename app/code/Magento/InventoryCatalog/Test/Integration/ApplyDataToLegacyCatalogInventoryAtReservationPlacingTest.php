<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\Configuration as CatalogInventoryConfiguration;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\AppendReservationsInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap as TestFrameworkBootstrap;
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

    protected function setUp()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->legacyStockItemCriteriaFactory = Bootstrap::getObjectManager()->get(
            StockItemCriteriaInterfaceFactory::class
        );
        $this->legacyStockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testApplyDataIfCanSubtractOptionIsEnabled()
    {
        $this->setCanSubtractQtyConfig(1);

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
            $this->reservationBuilder->setStockId(1)->setSku('SKU-1')->setQuantity(-2.5)->build()
        ]);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = current($legacyStockItems);
        self::assertEquals(3, $legacyStockItem->getQty());
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testApplyDataIfCanSubtractOptionIsDisabled()
    {
        $this->setCanSubtractQtyConfig(0);

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
            $this->reservationBuilder->setStockId(1)->setSku('SKU-1')->setQuantity(-2.5)->build()
        ]);

        $legacyStockItems = $this->legacyStockItemRepository->getList($legacyStockItemCriteria)->getItems();
        self::assertCount(1, $legacyStockItems);

        $legacyStockItem = current($legacyStockItems);
        self::assertEquals(5.5, $legacyStockItem->getQty());
    }

    /**
     * Set canSubtractQty() flag on the website level
     *
     * @param bool|int $value
     *
     * @return void
     */
    private function setCanSubtractQtyConfig($value)
    {
        $objectManager = TestFrameworkBootstrap::getObjectManager();
        /** @var MutableScopeConfigInterface $scopeConfig */
        $scopeConfig = $objectManager->get(MutableScopeConfigInterface::class);

        $scopeConfig->setValue(
            CatalogInventoryConfiguration::XML_PATH_CAN_SUBTRACT,
            $value,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
