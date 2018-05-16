<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\Stock;

use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;
use Magento\InventoryReservationsApi\Model\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Model\ReservationBuilderInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetProductSalableQtyTest extends TestCase
{
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
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    protected function setUp()
    {
        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->getProductSalableQty = Bootstrap::getObjectManager()->get(
            GetProductSalableQtyInterface::class
        );
    }

    /**
     * We broke transaction during indexation so we need to clean db state manually
     */
    protected function tearDown()
    {
        $this->cleanupReservations->execute();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @param string $sku
     * @param int $stockId
     * @param float $qty
     *
     * @dataProvider getProductQuantityProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testGetProductQuantity(string $sku, int $stockId, float $qty)
    {
        self::assertEquals($qty, $this->getProductSalableQty->execute($sku, $stockId));
    }

    /**
     * @return array
     */
    public function getProductQuantityProvider(): array
    {
        return [
            ['SKU-1', 10, 8.5],
            ['SKU-1', 20, 0],
            ['SKU-1', 30, 8.5],
            ['SKU-2', 10, 0],
            ['SKU-2', 20, 5],
            ['SKU-2', 30, 5],
            ['SKU-3', 10, 0],
            ['SKU-3', 20, 0],
            ['SKU-3', 30, 0],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     */
    public function testGetProductQuantityIfReservationsArePresent()
    {
        $this->appendReservations->execute([
            // emulate order placement reserve 5 units)
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(-5)->build(),
            // emulate partial order canceling (1.5 units)
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(1.5)->build(),
        ]);
        self::assertEquals(5, $this->getProductSalableQty->execute('SKU-1', 10));

        $this->appendReservations->execute([
            // unreserved 3.5 units for cleanup
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(3.5)->build(),
        ]);
    }
}
