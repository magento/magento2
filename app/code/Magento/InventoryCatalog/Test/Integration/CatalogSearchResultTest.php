<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\Source\SourceIndexer;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\InventoryApi\Api\StockRepositoryInterface;

class CatalogSearchResultTest extends TestCase
{
    /**
     * @var Stock
     */
    private $stock;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    protected function setUp()
    {
        $this->stock = Bootstrap::getObjectManager()->create(Stock::class);

        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(SourceIndexer::INDEXER_ID);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testGetResultCount()
    {
        $this->indexer->reindexAll();
        $collection = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );
        $this->stock->addIsInStockFilterToCollection($collection);
        self::assertEquals(2, $collection->getSize());
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
     *
     * @dataProvider testGetResultCountOnNonDefaultSalesChannelDataProvider
     */
    public function testGetResultCountOnNonDefaultSalesChannel(int $stockId, int $expectedSize)
    {
        /** @var SalesChannelInterface $salesChannel */
        $salesChannel = Bootstrap::getObjectManager()->get(SalesChannelInterface::class);
        $salesChannel->setCode('test');
        $salesChannel->setType(SalesChannelInterface::TYPE_WEBSITE);

        /** @var StockRepositoryInterface $stockRepository */
        $stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $stock = $stockRepository->get($stockId);
        $stock->getExtensionAttributes()->setSalesChannels([$salesChannel]);
        $stockRepository->save($stock);

        /** @var StoreManagerInterface $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore('fixture_second_store');

        $this->indexer->reindexAll();
        $collection = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );
        $this->stock->addIsInStockFilterToCollection($collection);

        self::assertEquals($expectedSize, $collection->getSize());

        $stock->getExtensionAttributes()->setSalesChannels([]);
        $stockRepository->save($stock);
    }

    /**
     * Data provider for testGetResultCountOnNonDefaultSalesChannel().
     *
     * @return array
     */
    public function testGetResultCountOnNonDefaultSalesChannelDataProvider()
    {
        return [
            [10, 1],
            [20, 1],
            [30, 2],
        ];
    }
}
