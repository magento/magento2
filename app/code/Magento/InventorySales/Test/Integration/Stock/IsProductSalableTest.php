<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\Stock;

use Magento\InventoryReservations\Model\CleanupReservationsInterface;
use Magento\InventoryReservations\Model\ReservationBuilderInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class IsProductSalableTest extends TestCase
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
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->isProductSalable = Bootstrap::getObjectManager()->get(IsProductSalableInterface::class);
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
     * @param bool $isSalable
     *
     * @dataProvider productIsSalableDataProvider
     */
    public function testProductIsSalable(string $sku, int $stockId, bool $isSalable)
    {
        self::assertEquals($isSalable, $this->isProductSalable->execute($sku, $stockId));
    }

    /**
     * @return array
     */
    public function productIsSalableDataProvider(): array
    {
        return [
            ['SKU-1', 10, true],
            ['SKU-1', 20, false],
            ['SKU-1', 30, true],
            ['SKU-2', 10, false],
            ['SKU-2', 20, true],
            ['SKU-2', 30, true],
            ['SKU-3', 10, false],
            ['SKU-3', 20, false],
            ['SKU-3', 30, false],
        ];
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
        self::assertFalse($this->isProductSalable->execute('SKU-1', 10));

        $this->appendReservations->execute([
            // unreserve 8.5 units for cleanup
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity(8.5)->build(),
        ]);
        $this->cleanupReservations->execute();
    }
}
