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
     * Test if stock item quantity properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testSaveStockItemQty()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $id1 = $productRepository->get('simple1')->getId();
        $id2 = $productRepository->get('simple2')->getId();
        $id3 = $productRepository->get('simple3')->getId();
        $existingProductIds = [$id1, $id2, $id3];
        $stockItems = [];
        foreach ($existingProductIds as $productId) {
            /** @var $stockRegistry StockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                StockRegistry::class
            );

            $stockItem = $stockRegistry->getStockItem($productId, 1);
            $stockItems[$productId] = $stockItem;
        }

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import.csv',
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

        /** @var $stockItmBeforeImport \Magento\CatalogInventory\Model\Stock\Item */
        foreach ($stockItems as $productId => $stockItmBeforeImport) {
            /** @var $stockRegistry StockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                StockRegistry::class
            );

            $stockItemAfterImport = $stockRegistry->getStockItem($productId, 1);

            $this->assertEquals($stockItmBeforeImport->getQty(), $stockItemAfterImport->getQty());
            $this->assertEquals(1, $stockItemAfterImport->getIsInStock());
            unset($stockItemAfterImport);
        }

        unset($stockItems, $stockItem);
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
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        $id1 = $productRepository->get('simple1')->getId();
        $id2 = $productRepository->get('simple2')->getId();
        $id3 = $productRepository->get('simple3')->getId();
        $existingProductIds = [$id1, $id2, $id3];

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_zero_qty.csv',
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

        /** @var $stockItmBeforeImport \Magento\CatalogInventory\Model\Stock\Item */
        foreach ($existingProductIds as $productId) {
            /** @var $stockRegistry StockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                StockRegistry::class
            );

            $stockItemAfterImport = $stockRegistry->getStockItem($productId, 1);

            $this->assertEquals(0, $stockItemAfterImport->getIsInStock());
            unset($stockItemAfterImport);
        }
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
        /** @var $stockRegistry StockRegistry */
        $stockRegistry = $this->objectManager->create(StockRegistry::class);
        /** @var StockRegistryStorage $stockRegistryStorage */
        $stockRegistryStorage = $this->objectManager->get(StockRegistryStorage::class);
        $status = $stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_IN_STOCK, $status->getStockStatus());
        $this->importDataForMediaTest('disable_product.csv');
        $stockRegistryStorage->clean();
        $status = $stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_OUT_OF_STOCK, $status->getStockStatus());
        $this->importDataForMediaTest('enable_product.csv');
        $stockRegistryStorage->clean();
        $status = $stockRegistry->getStockStatusBySku('simple');
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
        /** @var $stockRegistry StockRegistry */
        $stockRegistry = $this->objectManager->create(StockRegistry::class);
        /** @var StockRegistryStorage $stockRegistryStorage */
        $stockRegistryStorage = $this->objectManager->get(StockRegistryStorage::class);
        $status = $stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_IN_STOCK, $status->getStockStatus());
        $this->importDataForMediaTest('disable_product.csv');
        $indexProcessor->updateMview();
        $stockRegistryStorage->clean();
        $status = $stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_OUT_OF_STOCK, $status->getStockStatus());
        $this->importDataForMediaTest('enable_product.csv');
        $indexProcessor->updateMview();
        $stockRegistryStorage->clean();
        $status = $stockRegistry->getStockStatusBySku('simple');
        $this->assertEquals(Stock::STOCK_IN_STOCK, $status->getStockStatus());
    }
}
