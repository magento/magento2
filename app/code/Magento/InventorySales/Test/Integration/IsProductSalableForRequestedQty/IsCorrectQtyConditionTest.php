<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\IsProductSalableForRequestedQty;

use Magento\CatalogInventory\Model\Stock as LegacyStock;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\SaveStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsCorrectQtyConditionTest extends TestCase
{
    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfig;

    /**
     * @var SaveStockItemConfigurationInterface
     */
    private $saveStockItemConfig;

    /**
     * @var IsProductSalableForRequestedQtyInterface
     */
    private $isProductSalableForRequestedQty;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->getStockItemConfig = Bootstrap::getObjectManager()->get(GetStockItemConfigurationInterface::class);
        $this->saveStockItemConfig = Bootstrap::getObjectManager()->get(SaveStockItemConfigurationInterface::class);
        $this->isProductSalableForRequestedQty
            = Bootstrap::getObjectManager()->get(IsProductSalableForRequestedQtyInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider executeWithMissingConfigurationDataProvider
     */
    public function testExecuteWithMissingConfiguration($sku, $stockId, $requestedQty, bool $expectedResult)
    {
        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    public function executeWithMissingConfigurationDataProvider(): array
    {
        return [
            ['SKU-2', 10, 1, false],
        ];
    }
    
    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture default_store cataloginventory/item_options/min_qty 5
     * @dataProvider executeWithUseConfigMinQtyDataProvider
     */
    public function testExecuteWithUseConfigMinQty($sku, $stockId, $requestedQty, bool $expectedResult)
    {
        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfig->execute($sku, $stockId);
        $origUseConfigMinQty = $stockItemConfiguration->isUseConfigMinQty();
        $origMinQty = $stockItemConfiguration->getMinQty();

        $stockItemConfiguration->setUseConfigMinQty(true);
        $stockItemConfiguration->setMinQty(10); // set this to show it is ignored since we use config min qty set to 5
        $this->saveStockItemConfig->execute($sku, LegacyStock::DEFAULT_STOCK_ID, $stockItemConfiguration);

        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());

        // rollback changes
        $stockItemConfiguration->setUseConfigMinQty($origUseConfigMinQty);
        $stockItemConfiguration->setMinQty($origMinQty);
        $this->saveStockItemConfig->execute($sku, LegacyStock::DEFAULT_STOCK_ID, $stockItemConfiguration);
    }

    public function executeWithUseConfigMinQtyDataProvider(): array
    {
        return [
            ['SKU-1', 10, 4, false], // 8.5 available, out-of-stock threshold is 5, 3.5 currently available
            ['SKU-1', 10, 3, true],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @dataProvider executeWithMinQtyDataProvider
     */
    public function testExecuteWithMinQty($sku, $stockId, $requestedQty, bool $expectedResult)
    {
        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfig->execute($sku, $stockId);
        $origUseConfigMinQty = $stockItemConfiguration->isUseConfigMinQty();
        $origMinQty = $stockItemConfiguration->getMinQty();

        $stockItemConfiguration->setUseConfigMinQty(false);
        $stockItemConfiguration->setMinQty(6);
        $this->saveStockItemConfig->execute($sku, LegacyStock::DEFAULT_STOCK_ID, $stockItemConfiguration);

        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());

        // rollback changes
        $stockItemConfiguration->setUseConfigMinQty($origUseConfigMinQty);
        $stockItemConfiguration->setMinQty($origMinQty);
        $this->saveStockItemConfig->execute($sku, LegacyStock::DEFAULT_STOCK_ID, $stockItemConfiguration);
    }

    public function executeWithMinQtyDataProvider(): array
    {
        return [
            ['SKU-1', 10, 5, false],
            ['SKU-1', 10, 2, true],
        ];
    }

    public function testExecuteWithUseConfigMinSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    public function testExecuteWithMinSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    public function testExecuteWithUseConfigMaxSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    public function testExecuteWithMaxSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    public function testExecuteWithQtyIncrements()
    {
        $this->markTestIncomplete('Still to implement');
    }
}
