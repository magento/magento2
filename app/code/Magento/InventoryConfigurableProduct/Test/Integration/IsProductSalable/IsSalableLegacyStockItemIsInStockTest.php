<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\IsProductSalable;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkus;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsSalableLegacyStockItemIsInStockTest extends TestCase
{
    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var GetProductIdsBySkus
     */
    private $getProductIdsBySkus;

    /**
     * @var StockRegistryProviderInterface
     */
    private $stockRegistryProvider;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    protected function setUp()
    {
        $this->isProductSalable = Bootstrap::getObjectManager()->get(IsProductSalableInterface::class);
        $this->getProductIdsBySkus = Bootstrap::getObjectManager()->get(GetProductIdsBySkus::class);
        $this->stockRegistryProvider = Bootstrap::getObjectManager()->get(StockRegistryProviderInterface::class);
        $this->stockConfiguration = Bootstrap::getObjectManager()->get(StockConfigurationInterface::class);
        $this->stockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDbIsolation disabled
     */
    public function testIsProductSalableLegacyStockItemIsOutOfStock(): void
    {
        $sku = 'configurable';
        $stockId = 20;
        $this->setLegacyStockItemIsInStock($sku, 0);

        self::assertEquals(
            false,
            $this->isProductSalable->execute($sku, $stockId)
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDbIsolation disabled
     */
    public function testIsProductSalableLegacyStockItemIsInStock(): void
    {
        $sku = 'configurable';
        $stockId = 20;
        $this->setLegacyStockItemIsInStock($sku, 1);

        self::assertEquals(
            true,
            $this->isProductSalable->execute($sku, $stockId)
        );
    }

    /**
     * @param string $sku
     * @param int $isInStock
     */
    private function setLegacyStockItemIsInStock(string $sku, int $isInStock): void
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $productId = current($this->getProductIdsBySkus->execute([$sku]));
        $stockItem = $this->stockRegistryProvider->getStockItem($productId, $scopeId);
        $stockItem->setIsInStock($isInStock);
        $this->stockItemRepository->save($stockItem);
    }
}
