<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Indexer;

use Magento\InventoryIndexer\Indexer\SourceItem\GetSourceItemId;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;
use Magento\InventoryIndexer\Model\GetStockItemData;
use Magento\InventorySales\Model\GetStockItemDataInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourceItemIndexerTest extends TestCase
{
    /**
     * @var SourceItemIndexer
     */
    private $sourceItemIndexer;

    /**
     * @var GetStockItemData
     */
    private $getStockItemData;

    /**
     * @var GetSourceItemId
     */
    private $getSourceItemId;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    protected function setUp()
    {
        $this->sourceItemIndexer = Bootstrap::getObjectManager()->get(SourceItemIndexer::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemData::class);
        $this->getSourceItemId = Bootstrap::getObjectManager()->get(GetSourceItemId::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
        $this->removeIndexData->execute([10, 20, 30]);
    }

    /**
     * We broke transaction during indexation so we need to clean db state manually
     */
    protected function tearDown()
    {
        $this->removeIndexData->execute([10, 20, 30]);
    }

    /**
     * Source 'eu-1' is assigned on EU-stock(id:10) and Global-stock(id:30)
     * Thus these stocks stocks be reindexed only for SKU-1
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     *
     * @param string $sku
     * @param int $stockId
     * @param array|null $expectedData
     *
     * @dataProvider reindexRowDataProvider
     */
    public function testReindexRow(string $sku, int $stockId, $expectedData)
    {
        $this->sourceItemIndexer->executeRow($this->getSourceItemId->execute('SKU-1', 'eu-1'));

        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * @return array
     */
    public function reindexRowDataProvider(): array
    {
        return [
            ['SKU-1', 10, [GetStockItemDataInterface::QUANTITY => 8.5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-1', 30, [GetStockItemDataInterface::QUANTITY => 8.5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-2', 10, null],
            ['SKU-2', 30, null],
            ['SKU-3', 10, null],
            ['SKU-3', 30, null],
        ];
    }

    /**
     * Source 'eu-1' and 'us-1' are assigned on EU-stock(id:10), US-stock(id:20) and Global-stock(id:30)
     * Thus these stocks should be reindexed only for SKU-1 and for SKU-2
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     *
     * @param string $sku
     * @param int $stockId
     * @param array|null $expectedData
     *
     * @dataProvider reindexListDataProvider
     */
    public function testReindexList(string $sku, int $stockId, $expectedData)
    {
        $this->sourceItemIndexer->executeList([
            $this->getSourceItemId->execute('SKU-1', 'eu-1'),
            $this->getSourceItemId->execute('SKU-2', 'us-1'),
        ]);

        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * @return array
     */
    public function reindexListDataProvider(): array
    {
        return [
            ['SKU-1', 10, [GetStockItemDataInterface::QUANTITY => 8.5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-1', 20, null],
            ['SKU-1', 30, [GetStockItemDataInterface::QUANTITY => 8.5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-2', 10, null],
            ['SKU-2', 20, [GetStockItemDataInterface::QUANTITY => 5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-2', 30, [GetStockItemDataInterface::QUANTITY => 5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-3', 10, null],
            ['SKU-3', 20, null],
            ['SKU-3', 30, null],
        ];
    }

    /**
     * All of stocks should be reindexed for all of skus
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     *
     * @param string $sku
     * @param int $stockId
     * @param array|null $expectedData
     *
     * @dataProvider reindexAllDataProvider
     */
    public function testReindexAll(string $sku, int $stockId, $expectedData)
    {
        $this->sourceItemIndexer->executeFull();

        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        self::assertEquals($expectedData, $stockItemData);
    }

    /**
     * @return array
     */
    public function reindexAllDataProvider(): array
    {
        return [
            ['SKU-1', 10, [GetStockItemDataInterface::QUANTITY => 8.5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-1', 20, null],
            ['SKU-1', 30, [GetStockItemDataInterface::QUANTITY => 8.5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-2', 10, null],
            ['SKU-2', 20, [GetStockItemDataInterface::QUANTITY => 5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-2', 30, [GetStockItemDataInterface::QUANTITY => 5, GetStockItemDataInterface::IS_SALABLE => 1]],
            ['SKU-3', 10, [GetStockItemDataInterface::QUANTITY => 0, GetStockItemDataInterface::IS_SALABLE => 0]],
            ['SKU-3', 20, null],
            ['SKU-3', 30, [GetStockItemDataInterface::QUANTITY => 0, GetStockItemDataInterface::IS_SALABLE => 0]],
        ];
    }
}
