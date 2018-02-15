<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Test\Integration\GetStockItemData;

use Magento\Inventory\Model\GetStockItemDataInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class MinQtyTest extends TestCase
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemDataInterface::class);
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
     * @param string $sku
     * @param int $stockId
     * @param $expectedData
     * @return void
     *
     * @dataProvider executeWithMinQtyDataProvider
     */
    public function testExecuteWithMinQty(string $sku, int $stockId, $expectedData)
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * @return array
     */
    public function executeWithMinQtyDataProvider(): array
    {
        return [
            ['SKU-1', 10, [IndexStructure::QUANTITY => 8.5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-1', 20, null],
            ['SKU-1', 30, [IndexStructure::QUANTITY => 8.5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-2', 10, null],
            ['SKU-2', 20, [IndexStructure::QUANTITY => 5, IndexStructure::IS_SALABLE => 0]],
            ['SKU-2', 30, [IndexStructure::QUANTITY => 5, IndexStructure::IS_SALABLE => 0]],
            ['SKU-3', 10, [IndexStructure::QUANTITY => 0, IndexStructure::IS_SALABLE => 0]],
            ['SKU-3', 20, null],
            ['SKU-3', 30, [IndexStructure::QUANTITY => 0, IndexStructure::IS_SALABLE => 0]],
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
     * @param string $sku
     * @param int $stockId
     * @param $expectedData
     * @return void
     *
     * @dataProvider executeWithManageStockFalseAndMinQty
     */
    public function testExecuteWithManageStockFalseAndMinQty(string $sku, int $stockId, $expectedData)
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);

        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * @return array
     */
    public function executeWithManageStockFalseAndMinQty(): array
    {
        return [
            ['SKU-1', 10, [IndexStructure::QUANTITY => 8.5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-1', 20, null],
            ['SKU-1', 30, [IndexStructure::QUANTITY => 8.5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-2', 10, null],
            ['SKU-2', 20, [IndexStructure::QUANTITY => 5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-2', 30, [IndexStructure::QUANTITY => 5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-3', 10, [IndexStructure::QUANTITY => 0, IndexStructure::IS_SALABLE => 1]],
            ['SKU-3', 20, null],
            ['SKU-3', 30, [IndexStructure::QUANTITY => 0, IndexStructure::IS_SALABLE => 1]],
        ];
    }
}
