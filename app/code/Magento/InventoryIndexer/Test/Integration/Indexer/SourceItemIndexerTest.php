<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Indexer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryIndexer\Indexer\SourceItem\GetSourceItemIds;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;
use Magento\InventoryIndexer\Model\ResourceModel\GetStockItemData;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
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
     * @var GetSourceItemIds
     */
    private $getSourceItemIds;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    protected function setUp()
    {
        $this->sourceItemIndexer = Bootstrap::getObjectManager()->get(SourceItemIndexer::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemData::class);
        $this->getSourceItemIds = Bootstrap::getObjectManager()->get(GetSourceItemIds::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
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
     *
     * @magentoDbIsolation disabled
     */
    public function testReindexRow(string $sku, int $stockId, $expectedData)
    {
        $sourceItem = $this->getSourceItem('SKU-1', 'eu-1');
        $sourceItemIds = $this->getSourceItemIds->execute([$sourceItem]);
        foreach ($sourceItemIds as $sourceItemId) {
            $this->sourceItemIndexer->executeRow((int)$sourceItemId);
        }

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
     *
     * @magentoDbIsolation disabled
     */
    public function testReindexList(string $sku, int $stockId, $expectedData)
    {
        $sourceItemIds = $this->getSourceItemIds->execute(
            [
                $this->getSourceItem('SKU-1', 'eu-1'),
                $this->getSourceItem('SKU-2', 'us-1'),
            ]
        );
        $this->sourceItemIndexer->executeList($sourceItemIds);

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
     *
     * @magentoDbIsolation disabled
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

    /**
     * @param string $sku
     * @param string $sourceCode
     * @return SourceItemInterface
     */
    private function getSourceItem(string $sku, string $sourceCode): SourceItemInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->addFilter(SourceItemInterface::SOURCE_CODE, $sourceCode)
            ->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        return reset($sourceItems);
    }
}
