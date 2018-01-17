<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Stock;

use Magento\Inventory\Model\CleanupReservationsInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\AppendReservationsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetProductQuantityInStockTest extends TestCase
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
     * @var GetProductQuantityInStockInterface
     */
    private $getProductQtyInStock;

    protected function setUp()
    {
        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->getProductQtyInStock = Bootstrap::getObjectManager()->get(
            GetProductQuantityInStockInterface::class
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
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testGetProductQuantity()
    {
        self::assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 10));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testGetProductQuantityIfReservationsArePresent()
    {
        $this->appendReservations->execute([
            // emulate order placement reserve 5 units)
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(-5)->build(),
            // emulate partial order canceling (1.5 units)
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(1.5)->build(),
        ]);
        self::assertEquals(5, $this->getProductQtyInStock->execute('SKU-1', 10));

        $this->appendReservations->execute([
            // unreserved 3.5 units for cleanup
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(3.5)->build(),
        ]);
    }
}
