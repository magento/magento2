<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Stock;

use Magento\Inventory\Model\CleanupReservationsInterface;
use Magento\InventoryApi\Api\AppendReservationsInterface;
use Magento\InventoryApi\Api\IsProductInStockInterface;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsProductInStockTest extends TestCase
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
     * @var IsProductInStockInterface
     */
    private $isProductInStock;

    /**
     * @var GetIsSalable
     */
    private $getIsSalable;

    /**
     * @var array
     */
    private $skus = ['SKU-1', 'SKU-2', 'SKU-3'];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->isProductInStock = Bootstrap::getObjectManager()->get(IsProductInStockInterface::class);
        $this->getIsSalable = Bootstrap::getObjectManager()->get(GetIsSalable::class);

        parent::setUp();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testProductIsInStock()
    {
        self::assertTrue($this->isProductInStock->execute('SKU-1', 10));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testProductIsOutOfStockIfReservationsArePresent()
    {
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

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @param int $stockId
     * @param array $expectedResults
     *
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testIsSalableWithDifferentQty(int $stockId, array $expectedResults)
    {
        foreach ($this->skus as $key => $sku) {
            $isSalable = $this->getIsSalable->execute($sku, $stockId);
            self::assertEquals($expectedResults[$key], $isSalable);
        }
    }

    /**
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            ['10', [true, false, false]],
            ['20', [false, true, false]],
            ['30', [true, true, false]],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     *
     * @param int $stockId
     * @param array $expectedResults
     *
     * @return void
     * @dataProvider isSalableWithManageStockFalseDataProvider
     */
    public function testIsSalableWithManageStockFalse(int $stockId, array $expectedResults)
    {
        foreach ($this->skus as $key => $sku) {
            $isSalable = $this->getIsSalable->execute($sku, $stockId);
            self::assertEquals($expectedResults[$key], $isSalable);
        }
    }

    /**
     * @return array
     */
    public function isSalableWithManageStockFalseDataProvider(): array
    {
        return [
            ['10', [true, false, true]],
            ['20', [false, true, false]],
            ['30', [true, true, true]],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture default_store cataloginventory/item_options/min_qty 5
     *
     * @param int $stockId
     * @param array $expectedResults
     *
     * @return void
     * @dataProvider isSalableWithMinQtyDataProvider
     */
    public function testIsSalableWithMinQty(int $stockId, array $expectedResults)
    {
        foreach ($this->skus as $key => $sku) {
            $isSalable = $this->getIsSalable->execute($sku, $stockId);
            self::assertEquals($expectedResults[$key], $isSalable);
        }
    }

    /**
     * @return array
     */
    public function isSalableWithMinQtyDataProvider(): array
    {
        return [
            ['10', [true, false, false]],
            ['20', [false, false, false]],
            ['30', [true, false, false]],
        ];
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoConfigFixture default_store cataloginventory/item_options/min_qty 5
     * @magentoConfigFixture default_store cataloginventory/item_options/manage_stock 0
     *
     * @param int $stockId
     * @param array $expectedResults
     *
     * @return void
     * @dataProvider isSalableWithManageStockFalseAndMinQty
     */
    public function testIsSalableWithManageStockFalseAndMinQty(int $stockId, array $expectedResults)
    {
        foreach ($this->skus as $key => $sku) {
            $isSalable = $this->getIsSalable->execute($sku, $stockId);
            self::assertEquals($expectedResults[$key], $isSalable);
        }
    }

    /**
     * @return array
     */
    public function isSalableWithManageStockFalseAndMinQty(): array
    {
        return [
            ['10', [true, false, true]],
            ['20', [false, true, false]],
            ['30', [true, true, true]],
        ];
    }
}
