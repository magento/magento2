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
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test catalog search with default stock on default website.
 */
class CatalogSearchResultDefaultSalesChannelTest extends TestCase
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
}
