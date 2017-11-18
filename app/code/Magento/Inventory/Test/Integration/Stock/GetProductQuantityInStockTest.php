<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Test\Integration\Stock;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\Alias;
use Magento\Inventory\Indexer\IndexNameBuilder;
use Magento\Inventory\Indexer\IndexStructureInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;
use Magento\Inventory\Model\GetProductQuantityInStock;
use Magento\Inventory\Model\ReservationCleanupInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetProductQuantityInStockTest extends TestCase
{
    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var ReservationsAppendInterface
     */
    private $reservationsAppend;

    /**
     * @var ReservationCleanupInterface
     */
    private $reservationCleanup;

    /**
     * @var GetProductQuantityInStock
     */
    private $getProductQtyInStock;

    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(StockItemIndexerInterface::INDEXER_ID);

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->reservationsAppend = Bootstrap::getObjectManager()->get(ReservationsAppendInterface::class);
        $this->reservationCleanup = Bootstrap::getObjectManager()->create(ReservationCleanupInterface::class);

        $this->getProductQtyInStock = Bootstrap::getObjectManager()->create(
            GetProductQuantityInStockInterface::class
        );
    }

    public function tearDown()
    {
        /** @var IndexNameBuilder $indexNameBuilder */
        $indexNameBuilder = Bootstrap::getObjectManager()->get(IndexNameBuilder::class);
        /** @var IndexStructureInterface $indexStructure */
        $indexStructure = Bootstrap::getObjectManager()->get(IndexStructureInterface::class);

        foreach ([10, 20, 30] as $stockId) {
            $indexName = $indexNameBuilder
                ->setIndexId(StockItemIndexerInterface::INDEXER_ID)
                ->addDimension('stock_', $stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();
            $indexStructure->delete($indexName, ResourceConnection::DEFAULT_CONNECTION);
        }

        // Cleanup reservations
        $this->reservationCleanup->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testGetProductQuantity()
    {
        $this->indexer->reindexRow(1);

        // condition before start the test
        self::assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 10));

        $this->reservationsAppend->execute([
            // reserve 5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(-5)->build(),
            // unreserved 1.5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(1.5)->build(),
        ]);

        // checks the expected result after reservation
        self::assertEquals(5, $this->getProductQtyInStock->execute('SKU-1', 10));

        $this->reservationsAppend->execute([
            // unreserved 3.5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(3.5)->build(),
        ]);
    }
}
