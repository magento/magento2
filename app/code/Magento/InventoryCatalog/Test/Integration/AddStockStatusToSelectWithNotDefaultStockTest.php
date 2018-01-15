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
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test add stock status to select on not default website.
 */
class AddStockStatusToSelectWithNotDefaultStockTest extends AbstractSalesChannelProvider
{
    /**
     * @var StockStatus
     */
    private $stockStatus;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var Website
     */
    private $website;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stockStatus = Bootstrap::getObjectManager()->create(StockStatus::class);
        $this->website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Website::class);

        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(SourceIndexer::INDEXER_ID);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     *
     * @param int $stockId
     * @param int $expectedIsSalableCount
     * @param int $expectedNotSalableCount
     *
     * @dataProvider addStockStatusToSelectDataProvider
     */
    public function testAddStockStatusToSelect(int $stockId, int $expectedIsSalableCount, $expectedNotSalableCount)
    {
        $this->addSalesChannelTypeWebsiteToStock($stockId, 'base');

        $actualIsSalableCount = $actualNotSalableCount = 0;

        $this->indexer->reindexAll();

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);

        $this->stockStatus->addStockStatusToSelect($collection->getSelect(), $this->website);

        foreach ($collection as $item) {
            $item->getIsSalable() == 1 ? $actualIsSalableCount++ : $actualNotSalableCount++;
        }

        self::assertEquals($expectedIsSalableCount, $actualIsSalableCount);
        self::assertEquals($expectedNotSalableCount, $actualNotSalableCount);
        self::assertEquals($expectedNotSalableCount + $expectedIsSalableCount, $collection->getSize());
    }

    /**
     * Data provider for testAddStockStatusToSelect().
     *
     * @return array
     */
    public function addStockStatusToSelectDataProvider(): array
    {
        return [
            [10, 1, 2],
            [20, 1, 2],
            [30, 2, 1],
        ];
    }
}
