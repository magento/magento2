<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Test\Integration\IsProductSalable;

use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class MinQtyTest extends TestCase
{
    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var array
     */
    private $skus = ['SKU-1', 'SKU-2', 'SKU-3'];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->isProductSalable = Bootstrap::getObjectManager()->get(IsProductSalableInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture default_store cataloginventory/item_options/min_qty 5
     *
     * @param int $stockId
     * @param array $expectedResults
     * @return void
     *
     * @dataProvider executeWithMinQtyDataProvider
     */
    public function testExecuteWithMinQty(int $stockId, array $expectedResults)
    {
        foreach ($this->skus as $key => $sku) {
            $isSalable = $this->isProductSalable->execute($sku, $stockId);
            self::assertEquals($expectedResults[$key], $isSalable);
        }
    }

    /**
     * @return array
     */
    public function executeWithMinQtyDataProvider(): array
    {
        return [
            ['10', [true, false, false]],
            ['20', [false, false, false]],
            ['30', [true, false, false]],
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
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     *
     * @param int $stockId
     * @param array $expectedResults
     * @return void
     *
     * @dataProvider executeWithManageStockFalseAndMinQty
     */
    public function testExecuteWithManageStockFalseAndMinQty(int $stockId, array $expectedResults)
    {
        foreach ($this->skus as $key => $sku) {
            $isSalable = $this->isProductSalable->execute($sku, $stockId);
            self::assertEquals($expectedResults[$key], $isSalable);
        }
    }

    /**
     * @return array
     */
    public function executeWithManageStockFalseAndMinQty(): array
    {
        return [
            ['10', [true, false, true]],
            ['20', [false, true, false]],
            ['30', [true, true, true]],
        ];
    }
}
