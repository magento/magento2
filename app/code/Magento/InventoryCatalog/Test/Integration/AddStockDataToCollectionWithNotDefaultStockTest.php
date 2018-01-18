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
use Magento\InventoryIndexer\Indexer\Source\SourceIndexer;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test catalog search with different stocks on second website.
 */
class AddStockDataToCollectionWithNotDefaultStockTest extends TestCase
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
     * @var SalesChannelInterface
     */
    private $salesChannel;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected function setUp()
    {
        parent::setUp();

        $this->stockStatus = Bootstrap::getObjectManager()->create(StockStatus::class);
        $this->salesChannel = Bootstrap::getObjectManager()->get(SalesChannelInterface::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);

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
     * @param int $expectedSize
     * @param bool $isFilterInStock
     * @return void
     *
     * @dataProvider addStockDataToCollectionDataProvider
     */
    public function testAddStockDataToCollection(int $stockId, int $expectedSize, bool $isFilterInStock)
    {
        // this is not in fixture, because we set salesChannel for different stockId received from data provider
        $this->salesChannel->setCode('test');
        $this->salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);

        $stock = $this->stockRepository->get($stockId);
        $stock->getExtensionAttributes()->setSalesChannels([$this->salesChannel]);
        $this->stockRepository->save($stock);

        // switch to second website
        $this->storeManager->setCurrentStore('fixture_second_store');

        $this->indexer->reindexAll();

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $this->stockStatus->addStockDataToCollection($collection, $isFilterInStock);

        self::assertEquals($expectedSize, $collection->getSize());

        //need delete sales channels from stock to give ability to fixture to delete stock.
        $stock->getExtensionAttributes()->setSalesChannels([]);
        $this->stockRepository->save($stock);
    }

    /**
     * @return array
     */
    public function addStockDataToCollectionDataProvider(): array
    {
        return [
            [10, 1, true],
            [20, 1, true],
            [30, 2, true],
            [10, 2, false],
            [20, 1, false],
            [30, 3, false],
        ];
    }
}
