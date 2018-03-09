<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Model;

use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\GetStockItemData;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetStockItemDataTest extends TestCase
{
    /**
     * @var GetStockItemData
     */
    private $getStockItemData;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemData::class);
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
     * @param array|null $expectedData
     *
     * @dataProvider getStockItemDataDataProvider
     */
    public function testGetStockItemData(string $sku, int $stockId, $expectedData)
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not receive Stock Item data
     */
    public function testGetStockItemDataException()
    {
        $this->getStockItemData->execute('SKU-1', 10);
    }

    /**
     * @return array
     */
    public function getStockItemDataDataProvider(): array
    {
        return [
            ['SKU-1', 10, [IndexStructure::QUANTITY => 8.5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-1', 20, null],
            ['SKU-1', 30, [IndexStructure::QUANTITY => 8.5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-2', 10, null],
            ['SKU-2', 20, [IndexStructure::QUANTITY => 5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-2', 30, [IndexStructure::QUANTITY => 5, IndexStructure::IS_SALABLE => 1]],
            ['SKU-3', 10, [IndexStructure::QUANTITY => 0, IndexStructure::IS_SALABLE => 0]],
            ['SKU-3', 20, null],
            ['SKU-3', 30, [IndexStructure::QUANTITY => 0, IndexStructure::IS_SALABLE => 0]],
        ];
    }
}
