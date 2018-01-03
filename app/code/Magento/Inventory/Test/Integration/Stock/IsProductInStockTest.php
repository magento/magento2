<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Stock;

use Magento\Framework\Indexer\IndexerInterface;
use Magento\Inventory\Indexer\Stock\StockIndexer;
use Magento\Inventory\Model\CleanupReservationsInterface;
use Magento\Inventory\Test\Integration\Indexer\RemoveIndexData;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\AppendReservationsInterface;
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
     * @var AppendReservationsInterface
     */
    private $appendReservations;

    /**
     * @var CleanupReservationsInterface
     */
    private $cleanupReservations;

    /**
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    /**
     * @var RemoveIndexData
     */
    private $removeIndexData;

    protected function setUp()
    {
        $this->indexer = Bootstrap::getObjectManager()->create(IndexerInterface::class);
        $this->indexer->load(StockIndexer::INDEXER_ID);

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->isProductInStock = Bootstrap::getObjectManager()->get(IsProductInStockInterface::class);

        $this->removeIndexData = Bootstrap::getObjectManager()->get(RemoveIndexData::class);
        $this->removeIndexData->execute([10]);
    }

    protected function tearDown()
    {
        $this->removeIndexData->execute([10]);
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
        $this->indexer->reindexRow(10);

        self::assertTrue($this->isProductInStock->execute('SKU-1', 10));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testProductIsOutOfStockIfReservationsArePresent()
    {
        $this->indexer->reindexRow(10);

        // emulate order placement (reserve -8.5 units)
        $this->appendReservations->execute([
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(-8.5)->build(),
        ]);
        self::assertFalse($this->isProductInStock->execute('SKU-1', 10));

        $this->appendReservations->execute([
            // unreserve 8.5 units for cleanup
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(8.5)->build(),
        ]);
        $this->cleanupReservations->execute();
    }
}
