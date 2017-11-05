<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

class UpdateLegacyCatalogInventoryPluginTest extends TestCase
{
    /**
     * @var ReservationBuilderInterface
     */
    private $reservationBuilder;

    /**
     * @var ReservationsAppendInterface
     */
    private $reservationsAppend;
    /**
     * @var StockItemRepositoryInterface
     */
    private $oldStockItemRepository;
    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    protected function setUp()
    {
        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->reservationsAppend = Bootstrap::getObjectManager()->get(ReservationsAppendInterface::class);
        $this->oldStockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->stockItemCriteriaFactory = Bootstrap::getObjectManager()->get(StockItemCriteriaInterfaceFactory::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDataFixture ../../../../dev/tests/integration/testsuite/Magento/Catalog/_files/products.php
     */
    public function testUpdateStockItemTable()
    {

        /** @var StockItemCriteriaInterface  $criteria */
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter([1]);

        /** @var StockItemCollectionInterface $collectionBeforeChange */
        $collectionBeforeChange = $this->oldStockItemRepository->getList($criteria);
        /** @var StockItemInterface $oldStockItem */
        $oldStockItem = current($collectionBeforeChange->getItems());
        $initialQuantity = $oldStockItem->getQty();

        $this->reservationsAppend->execute([
            $this->reservationBuilder->setStockId(1)->setSku('simple')->setQuantity(-5)->build()
        ]);

        /** @var StockItemCollectionInterface $collectionAfterChange */
        $collectionAfterChange = $this->oldStockItemRepository->getList($criteria);
        $oldStockItem = current($collectionAfterChange->getItems());
        $quantityAfterCheck = $oldStockItem->getQty();

        $this->assertEquals(5, $initialQuantity - $quantityAfterCheck);
    }
}
