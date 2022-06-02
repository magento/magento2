<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 */
class ProductStockTest extends ProductTestBase
{
    /**
     * @var StockRegistryStorage
     */
    private $stockRegistryStorage;

    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->stockRegistryStorage = $this->objectManager->get(StockRegistryStorage::class);
        $this->stockRegistry = $this->objectManager->get(StockRegistry::class);
    }

    /**
     * Test if stock item quantity properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testSaveStockItemQty()
    {
        $id1 = $this->getProductBySku('simple1')->getId();
        $id2 = $this->getProductBySku('simple2')->getId();
        $id3 = $this->getProductBySku('simple3')->getId();
        $stockItem = $this->stockRegistry->getStockItem($id1, 1);
        $id1Qty = $stockItem->getQty();
        $stockItem = $this->stockRegistry->getStockItem($id2, 1);
        $id2Qty = $stockItem->getQty();
        $stockItem = $this->stockRegistry->getStockItem($id3, 1);
        $id3Qty = $stockItem->getQty();

        $this->importFile('products_to_import.csv');

        $stockItem = $this->stockRegistry->getStockItem($id1, 1);
        $this->assertEquals(1, $stockItem->getIsInStock());
        $this->assertEquals($id1Qty, $stockItem->getQty());
        $stockItem = $this->stockRegistry->getStockItem($id2, 1);
        $this->assertEquals(1, $stockItem->getIsInStock());
        $this->assertEquals($id2Qty, $stockItem->getQty());
        $stockItem = $this->stockRegistry->getStockItem($id3, 1);
        $this->assertEquals(1, $stockItem->getIsInStock());
        $this->assertEquals($id3Qty, $stockItem->getQty());
    }

    /**
     * Test that is_in_stock set to 0 when item quantity is 0
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     *
     * @return void
     */
    public function testSaveIsInStockByZeroQty(): void
    {
        $this->importFile('products_to_import_zero_qty.csv');
        $product = $this->getProductBySku('simple1');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(0, $stockItem->getIsInStock());
        $product = $this->getProductBySku('simple2');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(0, $stockItem->getIsInStock());
        $product = $this->getProductBySku('simple3');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(0, $stockItem->getIsInStock());
    }

    /**
     * Test if stock state properly changed after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testStockState()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_qty.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
    }

    /**
     * Test that imported product stock status with backorders functionality enabled can be set to 'out of stock'.
     *
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testImportWithBackordersEnabled(): void
    {
        $this->importFile('products_to_import_with_backorders_enabled_and_0_qty.csv');
        $product = $this->getProductBySku('simple_new');
        $this->assertFalse($product->getDataByKey('quantity_and_stock_status')['is_in_stock']);
    }

    /**
     * Test that imported product stock status with stock quantity > 0 and backorders functionality disabled
     * can be set to 'out of stock'.
     *
     * @magentoDbIsolation enabled
     */
    public function testImportWithBackordersDisabled(): void
    {
        $this->importFile('products_to_import_with_backorders_disabled_and_not_0_qty.csv');
        $product = $this->getProductBySku('simple_new');
        $this->assertFalse($product->getDataByKey('quantity_and_stock_status')['is_in_stock']);
    }

    /**
     * Test that product stock status is updated after import
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testProductStockStatusShouldBeUpdated()
    {
        $status = $this->stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_IN_STOCK, $status->getStockStatus());
        $this->importFile('disable_product.csv');
        $status = $this->stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_OUT_OF_STOCK, $status->getStockStatus());
        $this->importDataForMediaTest('enable_product.csv');
        $status = $this->stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_IN_STOCK, $status->getStockStatus());
    }

    /**
     * Test that product stock status is updated after import on schedule
     *
     * @magentoDataFixture mediaImportImageFixture
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/CatalogImportExport/_files/cataloginventory_stock_item_update_by_schedule.php
     * @magentoDbIsolation disabled
     */
    public function testProductStockStatusShouldBeUpdatedOnSchedule()
    {
        /** * @var $indexProcessor \Magento\Indexer\Model\Processor */
        $indexProcessor = $this->objectManager->create(\Magento\Indexer\Model\Processor::class);
        $status = $this->stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_IN_STOCK, $status->getStockStatus());
        $this->importDataForMediaTest('disable_product.csv');
        $indexProcessor->updateMview();
        $status = $this->stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_OUT_OF_STOCK, $status->getStockStatus());
        $this->importDataForMediaTest('enable_product.csv');
        $indexProcessor->updateMview();
        $status = $this->stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_IN_STOCK, $status->getStockStatus());
    }

    /**
     * Test that product stock status should be 'out of stock' if quantity is 0 regardless of 'is_in_stock' value
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testImportWithQtyZeroAndWithoutStockStatus(): void
    {
        $this->importFile('products_to_import_with_qty_zero_only.csv');
        $product = $this->getProductBySku('simple1');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(0, $stockItem->getIsInStock());
        $product = $this->getProductBySku('simple2');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(0, $stockItem->getIsInStock());
        $product = $this->getProductBySku('simple3');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(0, $stockItem->getIsInStock());
    }

    /**
     * Test that product stock status should be 'in stock' if quantity is 0 and backorders is enabled
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testImportWithQtyZeroAndWithBackOrdersEnabled(): void
    {
        $this->importFile('products_to_import_with_qty_zero_backorders_enabled.csv');
        $product = $this->getProductBySku('simple1');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(1, $stockItem->getIsInStock());
        $product = $this->getProductBySku('simple2');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(1, $stockItem->getIsInStock());
        $product = $this->getProductBySku('simple3');
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), 1);
        $this->assertEquals(1, $stockItem->getIsInStock());
    }

    /**
     * @inheritdoc
     */
    protected function importFile(string $fileName, int $bunchSize = 100): bool
    {
        $this->stockRegistryStorage->clean();
        $result = parent::importFile($fileName, $bunchSize);
        $this->stockRegistryStorage->clean();
        return $result;
    }
}
