<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexStructureInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\InventoryApi\Api\UnassignSourceFromStockInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Preconditions:
 *
 * Products to Sources links:
 *   SKU-1 - EU-source-1(id:1) - 5.5qty
 *   SKU-1 - EU-source-2(id:2) - 3qty
 *   SKU-1 - EU-source-3(id:3) - 10qty (out of stock)
 *   SKU-1 - EU-source-4(id:4) - 10qty (disabled source)
 *
 *   SKU-2 - US-source-1(id:3) - 5qty
 *
 * Sources to Stocks links:
 *   EU-source-1(id:1) - EU-stock(id:1)
 *   EU-source-2(id:2) - EU-stock(id:1)
 *   EU-source-3(id:3) - EU-stock(id:1)
 *   EU-source-disabled(id:4) - EU-stock(id:1)
 *
 *   US-source-1(id:5) - US-stock(id:2)
 *
 *   EU-source-1(id:1) - Global-stock(id:3)
 *   EU-source-2(id:2) - Global-stock(id:3)
 *   EU-source-3(id:3) - Global-stock(id:3)
 *   EU-source-disabled(id:4) - Global-stock(id:3)
 *   US-source-1(id:5) - Global-stock(id:3)
 *
 * TODO: fixture via composer
 */
class IndexationTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var Checker
     */
    private $indexerChecker;

    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->indexer->load(StockItemIndexerInterface::INDEXER_ID);
        $this->indexerChecker = Bootstrap::getObjectManager()->create(Checker::class);
    }

    public function tearDown()
    {
        /** @var IndexNameBuilder $indexNameBuilder */
        $indexNameBuilder = Bootstrap::getObjectManager()->get(IndexNameBuilder::class);
        /** @var IndexStructureInterface $indexStructure */
        $indexStructure = Bootstrap::getObjectManager()->get(IndexStructureInterface::class);

        foreach ([1, 2, 3] as $stockId) {
            $indexName = $indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();
            $indexStructure->delete($indexName);
        }
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testReindexRow()
    {
        $this->indexer->reindexRow(1);

        self::assertEquals(8.5, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-1'));
        self::assertEquals(8.5, $this->indexerChecker->execute(3, 'SKU-1'));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testReindexList()
    {
        $this->indexer->reindexList([1, 5]);

        self::assertEquals(8.5, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-1'));
        self::assertEquals(8.5, $this->indexerChecker->execute(3, 'SKU-1'));

        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(2, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(3, 'SKU-2'));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testReindexAll()
    {
        $this->indexer->reindexAll();

        /**
         * Asserts after assign action.
         */
        self::assertEquals(8.5, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-1'));
        self::assertEquals(8.5, $this->indexerChecker->execute(3, 'SKU-1'));

        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(2, 'SKU-2'));
        self::assertEquals(5, $this->indexerChecker->execute(3, 'SKU-2'));

        /** @var UnassignSourceFromStockInterface $assignSourcesToStock */
        $unassignSourcesToStock = Bootstrap::getObjectManager()->get(UnassignSourceFromStockInterface::class);
        $unassignSourcesToStock->execute(2, 3);

        $this->indexer->reindexAll();

        /**
         * Asserts after unassign action.
         */
        self::assertEquals(5.5, $this->indexerChecker->execute(3, 'SKU-1'));
    }
}
