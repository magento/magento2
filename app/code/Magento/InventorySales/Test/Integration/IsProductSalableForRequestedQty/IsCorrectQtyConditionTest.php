<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\IsProductSalableForRequestedQty;

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
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var SaveStockItemConfigurationInterface
     */
    private $saveStockItemConfiguration;

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
        $this->getStockItemConfiguration = Bootstrap::getObjectManager()->get(
            GetStockItemConfigurationInterface::class
        );
        $this->saveStockItemConfiguration = Bootstrap::getObjectManager()->get(
            SaveStockItemConfigurationInterface::class
        );
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
    
    public function testExecuteWithUseConfigMinSaleQty()
    {
        $this->markTestIncomplete('Still to implement');
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @param string $sku
     * @param int $stockId
     * @param int $requestedQty
     * @param bool $expectedResult
     *
     * @return void
     * @dataProvider executeWithMinSaleQtyDataProvider
     */
    public function testExecuteWithMinSaleQty(
        string $sku,
        int $stockId,
        int $requestedQty,
        bool $expectedResult
    ): void {
        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $stockItemConfiguration->setUseConfigMinSaleQty(false);
        $stockItemConfiguration->setMinSaleQty(7);
        $this->saveStockItemConfiguration->execute($sku, $stockId, $stockItemConfiguration);

        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    /**
     * @return array
     */
    public function executeWithMinSaleQtyDataProvider(): array
    {
        return [
            ['SKU-1', 10, 1, false],
            ['SKU-1', 10, 7, true],
            ['SKU-1', 10, 8, true],
            ['SKU-3', 10, 1, false],
            ['SKU-3', 10, 7, false],
            ['SKU-2', 20, 1, false],
            ['SKU-2', 20, 7, false],
            ['SKU-1', 30, 1, false],
            ['SKU-1', 30, 7, true],
            ['SKU-1', 30, 8, true],
            ['SKU-2', 30, 1, false],
            ['SKU-2', 30, 7, false],
            ['SKU-3', 30, 1, false],
            ['SKU-3', 30, 7, false],
        ];
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
