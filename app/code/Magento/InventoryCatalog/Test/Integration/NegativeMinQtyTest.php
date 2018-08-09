<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\SaveStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class NegativeMinQtyTest extends TestCase
{
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
        $this->isProductSalableForRequestedQty = Bootstrap::getObjectManager()->get(
            IsProductSalableForRequestedQtyInterface::class
        );
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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @dataProvider isProductSalableForRequestedQtyWithBackordersEnabledAtProductLevelDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testIsProductSalableForRequestedQtyWithBackordersEnabledAtProductLevel(
        $sku,
        $stockId,
        $minQty,
        $requestedQty,
        $expectedSalability
    ) {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $stockItemConfiguration->setUseConfigBackorders(false);
        $stockItemConfiguration->setBackorders(StockItemConfigurationInterface::BACKORDERS_YES_NONOTIFY);
        $stockItemConfiguration->setUseConfigMinQty(false);
        $stockItemConfiguration->setMinQty($minQty);
        $this->saveStockItemConfiguration->execute($sku, $stockId, $stockItemConfiguration);

        $this->assertEquals(
            $expectedSalability,
            $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty)->isSalable()
        );
    }

    public function isProductSalableForRequestedQtyWithBackordersEnabledAtProductLevelDataProvider()
    {
        return [
            'salable_qty' => ['SKU-1', 10, -4.5, 13, true],
            'not_salable_qty' => ['SKU-1', 10, -4.5, 14, false],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoConfigFixture default_store cataloginventory/item_options/min_qty -4.5
     * @magentoConfigFixture default_store cataloginventory/item_options/backorders 1
     * @dataProvider isProductSalableForRequestedQtyWithBackordersEnabledGloballyDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testIsProductSalableForRequestedQtyWithBackordersEnabledGlobally(
        $sku,
        $stockId,
        $requestedQty,
        $expectedSalability
    ) {
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $stockItemConfiguration->setUseConfigBackorders(true);
        $stockItemConfiguration->setUseConfigMinQty(true);
        $this->saveStockItemConfiguration->execute($sku, $stockId, $stockItemConfiguration);

        $this->assertEquals(
            $expectedSalability,
            $this->isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty)->isSalable()
        );
    }

    public function isProductSalableForRequestedQtyWithBackordersEnabledGloballyDataProvider()
    {
        return [
            'salable_qty' => ['SKU-1', 10, 13, true],
            'not_salable_qty' => ['SKU-1', 10, 14, false],
        ];
    }
}
