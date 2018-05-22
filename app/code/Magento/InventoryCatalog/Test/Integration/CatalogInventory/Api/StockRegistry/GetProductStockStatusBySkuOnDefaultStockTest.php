<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Api\StockRegistry;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetProductStockStatusBySkuOnDefaultStockTest extends TestCase
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->stockRegistry = Bootstrap::getObjectManager()->get(StockRegistryInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @param string $sku
     * @param int $status
     *
     * @dataProvider getStatusDataProvider
     */
    public function testGetStatusIfScopeIdParameterIsNotPassed(string $sku, int $status): void
    {
        $productStockStatus = $this->stockRegistry->getProductStockStatusBySku($sku);

        self::assertEquals($status, $productStockStatus);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @param string $sku
     * @param int $status
     *
     * @dataProvider getStatusDataProvider
     */
    public function testGetStatusIfScopeIdParameterIsPassed(string $sku, int $status): void
    {
        $productStockStatus = $this->stockRegistry->getProductStockStatusBySku(
            $sku,
            $this->defaultStockProvider->getId()
        );

        self::assertEquals($status, $productStockStatus);
    }

    /**
     * @return array
     */
    public function getStatusDataProvider(): array
    {
        return [
            ['SKU-1', 1],
            ['SKU-2', 1],
            ['SKU-3', 0],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetStatusIfNotExistedScopeIdIsPassed(): void
    {
        $notExistedScopeId = 100;
        $this->stockRegistry->getProductStockStatusBySku('SKU-1', $notExistedScopeId);
    }
}
