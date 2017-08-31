<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Model\Indexer;
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

    /**
     * @magentoDataFixture ../../../../app/code/Magento/Inventory/Test/_files/product_list.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source/source_list.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock/stock_list.php
     * @magentoDataFixture ../../../../app/code/Magento/Inventory/Test/_files/source_item.php
     * @magentoDataFixture ../../../../app/code/Magento/Inventory/Test/_files/stock_source_link.php
     */
    public function testReindexAll()
    {
        self::assertEquals(0, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute(2, 'SKU-2'));

        $this->indexer->reindexAll();

        self::assertEquals(8, $this->indexerChecker->execute(1, 'SKU-1'));
        self::assertEquals(5, $this->indexerChecker->execute(2, 'SKU-2'));
    }
}