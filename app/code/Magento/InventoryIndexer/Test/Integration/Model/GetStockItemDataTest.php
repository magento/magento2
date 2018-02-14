<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Model;

use Magento\InventoryReservations\Model\CleanupReservationsInterface;
use Magento\InventoryReservationsApi\Api\AppendReservationsInterface;
use Magento\InventoryReservationsApi\Api\ReservationBuilderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\GetStockItemData;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetStockItemDataTest extends TestCase
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
     * @var GetStockItemData
     */
    private $getStockItemData;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->appendReservations = Bootstrap::getObjectManager()->get(AppendReservationsInterface::class);
        $this->cleanupReservations = Bootstrap::getObjectManager()->get(CleanupReservationsInterface::class);
        $this->getStockItemData = Bootstrap::getObjectManager()->get(GetStockItemData::class);
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
        $stockItemData = $this->getStockItemData->execute('SKU-1', 10);
        self::assertEquals(8.5, $stockItemData[IndexStructure::QUANTITY]);
        self::assertEquals(1, $stockItemData[IndexStructure::IS_SALABLE]);
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

        $stockItemData = $this->getStockItemData->execute('SKU-1', 10);
        //reservation doesn't influence to stock item data
        self::assertEquals(8.5, $stockItemData[IndexStructure::QUANTITY]);
        self::assertEquals(1, $stockItemData[IndexStructure::IS_SALABLE]);
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
     * @param array $expectedQty
     * @param array $expectedIsSalable
     * @return void
     *
     * @dataProvider executeWithDifferentQtyDataProvider
     */
    public function testExecuteWithDifferentQty(int $stockId, array $expectedQty, array $expectedIsSalable)
    {
        foreach (['SKU-1', 'SKU-2', 'SKU-3'] as $key => $sku) {
            $stockItemData = $this->getStockItemData->execute($sku, $stockId);
            self::assertEquals($expectedQty[$key], $stockItemData[IndexStructure::QUANTITY] ?? null);
            self::assertEquals($expectedIsSalable[$key], $stockItemData[IndexStructure::IS_SALABLE] ?? null);
        }
    }

    /**
     * @return array
     */
    public function executeWithDifferentQtyDataProvider(): array
    {
        return [
            ['10', [8.5, null, 0], [1, null, 0]],
            ['20', [null, 5, null], [null, 1, null]],
            ['30', [8.5, 5, 0], [1, 1, 0]],
        ];
    }
}
