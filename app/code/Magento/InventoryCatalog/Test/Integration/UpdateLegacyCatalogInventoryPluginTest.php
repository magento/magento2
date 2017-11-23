<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\InventoryApi\Api\ReservationBuilderInterface;
use Magento\InventoryApi\Api\ReservationsAppendInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\InventoryApi\Api\GetProductQuantityInStockInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Inventory\Indexer\SourceItem\SourceItemIndexer;

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

    /**
     * @var GetProductQuantityInStockInterface
     */
    private $getProductQtyInStock;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->reservationBuilder = Bootstrap::getObjectManager()->get(ReservationBuilderInterface::class);
        $this->reservationsAppend = Bootstrap::getObjectManager()->get(ReservationsAppendInterface::class);
        $this->oldStockItemRepository = Bootstrap::getObjectManager()->get(StockItemRepositoryInterface::class);
        $this->stockItemCriteriaFactory = Bootstrap::getObjectManager()->get(StockItemCriteriaInterfaceFactory::class);

        $this->indexer = Bootstrap::getObjectManager()->get(Indexer::class);
        $this->indexer->load(SourceItemIndexer::INDEXER_ID);
        $this->getProductQtyInStock = Bootstrap::getObjectManager()->get(GetProductQuantityInStockInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testUpdateStockItemTable()
    {
        $reservationQuantity = -5;

        /** @var Product $product */
        $product = $this->productRepository->get('SKU-1');

        /** @var StockItemCriteriaInterface  $criteria */
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter([$product->getId()]);

        /** @var StockItemCollectionInterface $collectionBeforeChange */
        $collectionBeforeChange = $this->oldStockItemRepository->getList($criteria);
        /** @var StockItemInterface $oldStockItem */
        $oldStockItem = current($collectionBeforeChange->getItems());
        $initialQuantity = $oldStockItem->getQty();

        $this->reservationsAppend->execute([
            $this->reservationBuilder->setStockId(1)->setSku('SKU-1')->setQuantity($reservationQuantity)->build()
        ]);

        /** @var StockItemCollectionInterface $collectionAfterChange */
        $collectionAfterChange = $this->oldStockItemRepository->getList($criteria);
        $oldStockItem = current($collectionAfterChange->getItems());
        $quantityAfterCheck = $oldStockItem->getQty();

        $this->assertEquals($initialQuantity + $reservationQuantity, $quantityAfterCheck);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     */
    public function testThatReservationPlacedUpdatesBothOldAndNewStocks()
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $reservationQuantity = -5;

        $this->indexer->reindexAll();
        $this->assertEquals(8.5, $this->getProductQtyInStock->execute('SKU-1', 10));

        /** @var Product $product */
        $product = $this->productRepository->get('SKU-1');

        /** @var StockItemCriteriaInterface  $criteria */
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter([$product->getId()]);

        /** @var StockItemCollectionInterface $collectionBeforeChange */
        $collectionBeforeChange = $this->oldStockItemRepository->getList($criteria);

        /** @var StockItemInterface $oldStockItem */
        $oldStockItem = current($collectionBeforeChange->getItems());
        $initialQuantity = $oldStockItem->getQty();
        $this->assertEquals(8.5, $initialQuantity);

        $this->reservationsAppend->execute([
            $this->reservationBuilder->setStockId(10)->setSku('SKU-1')->setQuantity($reservationQuantity)->build()
        ]);

        /** @var StockItemCollectionInterface $collectionAfterChange */
        $collectionAfterChange = $this->oldStockItemRepository->getList($criteria);
        $oldStockItem = current($collectionAfterChange->getItems());
        $quantityAfterCheck = $oldStockItem->getQty();

        $this->assertEquals(8.5 - 5, $this->getProductQtyInStock->execute('SKU-1', 10));
        $this->assertEquals($this->getProductQtyInStock->execute('SKU-1', 10), $quantityAfterCheck);
    }
}
