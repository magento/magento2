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

/**
 * @magentoAppIsolation enabled
 */
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
     *
     * @param string $sku
     * @param int $stockId
     * @param int $requestedQty
     * @param bool $expectedResult
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider executeWithMissingConfigurationDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testExecuteWithMissingConfiguration(
        string $sku,
        int $stockId,
        int $requestedQty,
        bool $expectedResult
    ) {
        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    /**
     * @return array
     */
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
     *
     * @param string $sku
     * @param int $stockId
     * @param float $requestedQty
     * @param bool $expectedResult
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider executeWithDecimalQtyDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testExecuteWithDecimalQty(
        string $sku,
        int $stockId,
        float $requestedQty,
        bool $expectedResult
    ): void {
        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    /**
     * @return array
     */
    public function executeWithDecimalQtyDataProvider(): array
    {
        return [
            ['SKU-1', 10, 2.5, true],
            ['SKU-1', 10, 2, true],
            ['SKU-2', 30, 2.5, false],
            ['SKU-2', 30, 2, true]
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/min_sale_qty 7
     *
     * @param string $sku
     * @param int $stockId
     * @param int $requestedQty
     * @param bool $expectedResult
     *
     * @return void
     *
     * @dataProvider executeWithUseConfigMinSaleQtyDataProvider
     *
     * @magentoDbIsolation disabled
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteWithUseConfigMinSaleQty(
        string $sku,
        int $stockId,
        int $requestedQty,
        bool $expectedResult
    ): void {
        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    /**
     * @return array
     */
    public function executeWithUseConfigMinSaleQtyDataProvider(): array
    {
        return [
            ['SKU-1', 10, 1, false],
            ['SKU-1', 10, 7, true],
            ['SKU-1', 10, 8, true],
            ['SKU-2', 10, 1, false],
            ['SKU-2', 10, 7, false],
            ['SKU-3', 10, 1, false],
            ['SKU-3', 10, 7, false],
            ['SKU-1', 20, 1, false],
            ['SKU-1', 20, 7, false],
            ['SKU-2', 20, 1, false],
            ['SKU-2', 20, 7, false],
            ['SKU-3', 20, 1, false],
            ['SKU-3', 20, 7, false],
            ['SKU-1', 30, 1, false],
            ['SKU-1', 30, 7, true],
            ['SKU-1', 30, 8, true],
            ['SKU-2', 30, 1, false],
            ['SKU-2', 30, 7, false],
            ['SKU-3', 30, 1, false],
            ['SKU-3', 30, 7, false],
        ];
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     * @dataProvider executeWithMinSaleQtyDataProvider
     *
     * @magentoDbIsolation disabled
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

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/max_sale_qty 6
     *
     * @param string $sku
     * @param int $stockId
     * @param int $requestedQty
     * @param bool $expectedResult
     *
     * @return void
     *
     * @dataProvider executeWithUseConfigMaxSaleQtyDataProvider
     *
     * @magentoDbIsolation disabled
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteWithUseConfigMaxSaleQty(
        string $sku,
        int $stockId,
        int $requestedQty,
        bool $expectedResult
    ): void {
        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    /**
     * @return array
     */
    public function executeWithUseConfigMaxSaleQtyDataProvider(): array
    {
        return [
            ['SKU-1', 10, 1, true],
            ['SKU-1', 10, 6, true],
            ['SKU-1', 10, 7, false],
            ['SKU-2', 10, 1, false],
            ['SKU-2', 10, 7, false],
            ['SKU-3', 10, 1, false],
            ['SKU-3', 10, 7, false],
            ['SKU-1', 20, 1, false],
            ['SKU-1', 20, 7, false],
            ['SKU-2', 20, 1, true],
            ['SKU-2', 20, 6, false],
            ['SKU-2', 20, 7, false],
            ['SKU-3', 20, 1, false],
            ['SKU-3', 20, 7, false],
            ['SKU-1', 30, 1, true],
            ['SKU-1', 30, 6, true],
            ['SKU-1', 30, 7, false],
            ['SKU-2', 30, 1, true],
            ['SKU-2', 30, 6, false],
            ['SKU-2', 30, 7, false],
            ['SKU-3', 30, 1, false],
            ['SKU-3', 30, 7, false],
        ];
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
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     * @dataProvider executeWithMaxSaleQtyDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testExecuteWithMaxSaleQty(
        string $sku,
        int $stockId,
        int $requestedQty,
        bool $expectedResult
    ): void {
        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $stockItemConfiguration->setUseConfigMaxSaleQty(false);
        $stockItemConfiguration->setMaxSaleQty(6);
        $this->saveStockItemConfiguration->execute($sku, $stockId, $stockItemConfiguration);

        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    /**
     * @return array
     */
    public function executeWithMaxSaleQtyDataProvider(): array
    {
        return [
            ['SKU-1', 10, 1, true],
            ['SKU-1', 10, 6, true],
            ['SKU-1', 10, 7, false],
            ['SKU-3', 10, 1, false],
            ['SKU-3', 10, 7, false],
            ['SKU-2', 20, 1, true],
            ['SKU-2', 20, 6, false],
            ['SKU-2', 20, 7, false],
            ['SKU-1', 30, 1, true],
            ['SKU-1', 30, 6, true],
            ['SKU-1', 30, 7, false],
            ['SKU-2', 30, 1, true],
            ['SKU-2', 30, 6, false],
            ['SKU-2', 30, 7, false],
            ['SKU-3', 30, 1, false],
            ['SKU-3', 30, 7, false],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture current_store cataloginventory/item_options/enable_qty_increments 1
     * @magentoConfigFixture current_store cataloginventory/item_options/qty_increments 3
     *
     * @param string $sku
     * @param int $stockId
     * @param int $requestedQty
     * @param bool $expectedResult
     *
     * @return void
     *
     * @dataProvider executeWithUseConfigQtyIncrementsDataProvider
     *
     * @magentoDbIsolation disabled
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testExecuteWithUseConfigQtyIncrements(
        string $sku,
        int $stockId,
        int $requestedQty,
        bool $expectedResult
    ): void {
        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    /**
     * @return array
     */
    public function executeWithUseConfigQtyIncrementsDataProvider(): array
    {
        return [
            ['SKU-1', 10, 1, false],
            ['SKU-1', 10, 3, true],
            ['SKU-1', 10, 6, true],
            ['SKU-1', 10, 9, false],
            ['SKU-2', 10, 1, false],
            ['SKU-2', 10, 3, false],
            ['SKU-3', 10, 1, false],
            ['SKU-3', 10, 3, false],
            ['SKU-1', 20, 1, false],
            ['SKU-1', 20, 3, false],
            ['SKU-2', 20, 1, false],
            ['SKU-2', 20, 3, true],
            ['SKU-2', 20, 6, false],
            ['SKU-3', 20, 1, false],
            ['SKU-3', 20, 3, false],
            ['SKU-1', 30, 1, false],
            ['SKU-1', 30, 3, true],
            ['SKU-1', 30, 6, true],
            ['SKU-1', 30, 9, false],
            ['SKU-2', 30, 1, false],
            ['SKU-2', 30, 3, true],
            ['SKU-2', 30, 6, false],
            ['SKU-3', 30, 1, false],
            ['SKU-3', 30, 3, false],
        ];
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
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException
     * @dataProvider executeWithQtyIncrementsDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testExecuteWithQtyIncrements(
        string $sku,
        int $stockId,
        int $requestedQty,
        bool $expectedResult
    ): void {
        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $stockItemConfiguration->setUseConfigEnableQtyInc(false);
        $stockItemConfiguration->setEnableQtyIncrements(true);
        $stockItemConfiguration->setUseConfigQtyIncrements(false);
        $stockItemConfiguration->setQtyIncrements(3);
        $this->saveStockItemConfiguration->execute($sku, $stockId, $stockItemConfiguration);

        $result = $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
        $this->assertEquals($expectedResult, $result->isSalable());
    }

    /**
     * @return array
     */
    public function executeWithQtyIncrementsDataProvider(): array
    {
        return [
            ['SKU-1', 10, 1, false],
            ['SKU-1', 10, 3, true],
            ['SKU-1', 10, 6, true],
            ['SKU-1', 10, 9, false],
            ['SKU-3', 10, 1, false],
            ['SKU-3', 10, 3, false],
            ['SKU-2', 20, 1, false],
            ['SKU-2', 20, 3, true],
            ['SKU-2', 20, 6, false],
            ['SKU-1', 30, 1, false],
            ['SKU-1', 30, 3, true],
            ['SKU-1', 30, 6, true],
            ['SKU-1', 30, 9, false],
            ['SKU-2', 30, 1, false],
            ['SKU-2', 30, 3, true],
            ['SKU-2', 30, 6, false],
            ['SKU-3', 30, 1, false],
            ['SKU-3', 30, 3, false],
        ];
    }
}
