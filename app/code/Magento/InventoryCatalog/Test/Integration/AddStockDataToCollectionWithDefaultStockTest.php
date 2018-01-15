<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\Source\SourceIndexer;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddStockDataToCollectionWithDefaultStockTest extends TestCase
{
    /**
     * @var StockStatus
     */
    private $stockStatus;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    protected function setUp()
    {
        parent::setUp();

        $this->stockStatus = Bootstrap::getObjectManager()->create(StockStatus::class);

        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(SourceIndexer::INDEXER_ID);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @param int $expectedSize
     * @param bool $isFilterInStock
     * @return void
     *
     * @dataProvider getResultCountDataProvider
     */
    public function testGetResultCount(int $expectedSize, bool $isFilterInStock)
    {
        $this->indexer->reindexAll();

        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection = $this->stockStatus->addStockDataToCollection($collection, $isFilterInStock);

        self::assertEquals($expectedSize, $collection->getSize());
    }

    /**
     * @return array
     */
    public function getResultCountDataProvider(): array
    {
        return [
            [2, true],
            [3, false],
        ];
    }
}
