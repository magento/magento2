<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Test\Integration\Model;

/**
 * Verify product import from export with additional sources.
 *
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/908874/scenarios/2219966
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/908874/scenarios/2219741
 *
 * @magentoAppArea adminhtml
 */
class ProductTestAdditionalStockTest extends ProductImportExportBase
{
    /**
     * Verify simple and virtual product import from export on additional stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/product_virtual_source_item_on_additional_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testImportSimpleVirtualProductTypesFromExportAdditionalStock(): void
    {
        $productExporter = $this->getProductExporter();
        $productExporter->export();
        $deletedProducts = $this->deleteProducts();
        $productImporter = $this->getProductImporter();
        $errors = $productImporter->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $productImporter->importData();
        $importedProducts = $this->getImportedProducts();

        $this->verifyProducts($deletedProducts, $importedProducts);
    }

    /**
     * Verify grouped product import from export on additional stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/custom_stock_with_eu_website_grouped_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProductIndexer/Test/_files/source_items_grouped_multiple.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testImportGroupedProductTypeFromExportAdditionalStock(): void
    {
        $productExporter = $this->getProductExporter();
        $productExporter->export();
        $deletedProducts = $this->deleteProducts();
        $productImporter = $this->getProductImporter();
        $errors = $productImporter->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $productImporter->importData();
        $importedProducts = $this->getImportedProducts();

        $this->verifyProducts($deletedProducts, $importedProducts);
    }

    /**
     * Verify configurable product import from export on additional stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testImportConfigurableProductTypeFromExportAdditionalStock(): void
    {
        $productExporter = $this->getProductExporter();
        $productExporter->export();
        $deletedProducts = $this->deleteProducts();
        $productImporter = $this->getProductImporter();
        $errors = $productImporter->validateData();
        $this->assertTrue($errors->getErrorsCount() == 0);
        $productImporter->importData();
        $importedProducts = $this->getImportedProducts();

        $this->verifyProducts($deletedProducts, $importedProducts);
    }
}
