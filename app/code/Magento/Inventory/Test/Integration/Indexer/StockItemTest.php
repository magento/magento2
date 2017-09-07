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
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Preconditions:
 *
 * SourceItems:
 *   SKU-1 - Source-1 - 5qty
 *   SKU-1 - Source-2 - 3qty
 *   SKU-2 - Source-3 - 5qty
 *
 * Sources to Stock links:
 *   Source-1 - Stock-1
 *   Source-2 - Stock-1
 *   Source-3 - Stock-2
 *
 * TODO: fixture via composer
 */
class StockItemTest extends TestCase
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

        foreach ([1, 2] as $stockId) {
            $indexName = $indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->create();
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
        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));

        $this->indexer->reindexRow(1);

        self::assertEquals(8, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));
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
        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));

        $this->indexer->reindexList([1]);

        self::assertEquals(8, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));
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
        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));

        $this->indexer->reindexAll();

        self::assertEquals(8, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(5, $this->indexerChecker->execute(2, 'SKU-2'));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items_out_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testReindexAllOneDisabled()
    {
        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));

        $this->indexer->reindexAll();

        self::assertEquals(8, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources_disable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items_out_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testReindexAllSourcesDisabled()
    {
        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));

        $this->indexer->reindexAll();

        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));
    }
}
