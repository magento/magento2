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
use Magento\Inventory\Model\ReservationCleanupInterface;
use Magento\Inventory\Test\Integration\Indexer\Checker;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsProductInStockTest extends TestCase
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
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(StockItemIndexerInterface::INDEXER_ID);

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->reservationsAppend = Bootstrap::getObjectManager()->get(ReservationsAppendInterface::class);
        $this->reservationCleanup = Bootstrap::getObjectManager()->get(ReservationCleanupInterface::class);

        $this->isProductInStock = Bootstrap::getObjectManager()->get(IsProductInStockInterface::class);
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
    public function testProductIsInStock()
    {
        $this->indexer->reindexRow(1);

        $this->reservationsAppend->execute([
            // reserve 5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(-5)->build(),
            // unreserve 1.5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(1.5)->build(),
        ]);

        self::assertTrue($this->isProductInStock->execute('SKU-1', 10));

        $this->reservationsAppend->execute([
            // unreserve 3.5 units
            $this->reservationBuilder->setStockId(1)->setSku('SKU-1')->setQuantity(3.5)->build(),
        ]);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testProductIsNotInStock()
    {
        $this->indexer->reindexRow(1);

        $this->reservationsAppend->execute([
            // reserve 8.5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(-8.5)->build(),
        ]);

        self::assertFalse($this->isProductInStock->execute('SKU-1', 10));

        $this->reservationsAppend->execute([
            // unreserve 8.5 units
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(8.5)->build(),
        ]);
    }
}
