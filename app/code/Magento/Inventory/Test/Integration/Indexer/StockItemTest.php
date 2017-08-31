<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\StockRepositoryInterface;
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

    /**
     * @var @var StockInterface[]
     */
    private $stocks;

    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $this->indexer->load(StockItemIndexerInterface::INDEXER_ID);
        $this->indexerChecker = Bootstrap::getObjectManager()->create(Checker::class);

        /** @var StockRepositoryInterface $stockRepository */
        $stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        /** @var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);
        $sortOrder = $sortOrderBuilder
            ->setField(StockInterface::NAME)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter(StockInterface::NAME, ['stock-name-1', 'stock-name-2'], 'in')
            ->addSortOrder($sortOrder)
            ->create();
        $this->stocks = array_values($stockRepository->getList($searchCriteria)->getItems());
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
        self::assertEquals(0, $this->indexerChecker->execute($this->stocks[0]->getStockId(), 'SKU-1'));
        self::assertEquals(0, $this->indexerChecker->execute($this->stocks[1]->getStockId(), 'SKU-2'));

        $this->indexer->reindexAll();

        self::assertEquals(8, $this->indexerChecker->execute($this->stocks[0]->getStockId(), 'SKU-1'));
        self::assertEquals(5, $this->indexerChecker->execute($this->stocks[1]->getStockId(), 'SKU-2'));
    }
}