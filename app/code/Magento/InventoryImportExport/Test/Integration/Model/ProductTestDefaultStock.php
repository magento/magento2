<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Test\Integration\Model;

/**
 * Verify product import from export with default source.
 *
 * @see https://app.hiptest.com/projects/69435/test-plan/folders/908874/scenarios/2222692
 *
 * @magentoAppArea adminhtml
 */
class ProductTestDefaultStock extends ProductImportExportBase
{
    /**
     * Verify simple and virtual product import from export on default stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testImportSimpleVirtualProductTypesFromExportDefaultStock(): void
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
     * Verify grouped product import from export on default stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryGroupedProduct/Test/_files/default_stock_grouped_products.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testImportGroupedProductTypeFromExportDefaultStock(): void
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
     * Verify configurable product import from export on default stock.
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/default_stock_configurable_products.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testImportConfigurableProductTypeFromExportDefaultStock(): void
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
