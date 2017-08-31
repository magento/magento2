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


class StockItemTest extends \PHPUnit\Framework\TestCase
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
     * @magentoDataFixture ../../../../app/code/Magento/Inventory/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/Inventory/Test/_files/source.php
     * @magentoDataFixture ../../../../app/code/Magento/Inventory/Test/_files/source_item.php
     * @magentoDataFixture ../../../../app/code/Magento/Inventory/Test/_files/stock.php
     * @magentoDataFixture ../../../../app/code/Magento/Inventory/Test/_files/stock_source_link.php
     */
    public function testIndexRow()
    {
        self::assertEquals(0, $this->indexerChecker->execute(1, 'inventory_1'));
        $this->indexer->reindexAll();
        self::assertEquals(10,$this->indexerChecker->execute(1, 'inventory_1'));
        self::assertEquals(0, $this->indexerChecker->execute(1, 'inventory_2'));
    }
}