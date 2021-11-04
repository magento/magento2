<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 *
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 */
class ProductCategoriesTest extends ProductTestBase
{
    /**
     * @dataProvider categoryTestDataProvider
     */
    public function testProductCategories($fixture, $separator)
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../_files/' . $fixture;
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => $separator
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $resource = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Product::class);
        $productId = $resource->getIdBySku('simple1');
        $this->assertIsNumeric($productId);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load($productId);
        $this->assertFalse($product->isObjectNew());
        $categories = $product->getCategoryIds();
        $this->assertTrue(count($categories) == 2);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testProductPositionInCategory()
    {
        /* @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $collection->addNameToResult()->load();
        /** @var Category $category */
        $category = $collection->getItemByColumnValue('name', 'Category 1');

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);

        $categoryProducts = [];
        $i = 51;
        foreach (['simple1', 'simple2', 'simple3'] as $sku) {
            $categoryProducts[$productRepository->get($sku)->getId()] = $i++;
        }
        $category->setPostedProducts($categoryProducts);
        $category->save();

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product'
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        /** @var \Magento\Framework\App\ResourceConnection $resourceConnection */
        $resourceConnection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\ResourceConnection::class
        );
        $tableName = $resourceConnection->getTableName('catalog_category_product');
        $select = $resourceConnection->getConnection()->select()->from($tableName)
            ->where('category_id = ?', $category->getId());
        $items = $resourceConnection->getConnection()->fetchAll($select);
        $this->assertCount(3, $items);
        foreach ($items as $item) {
            $this->assertGreaterThan(50, $item['position']);
        }
    }

    /**
     * @return array
     */
    public function categoryTestDataProvider()
    {
        return [
            ['import_new_categories_default_separator.csv', ','],
            ['import_new_categories_custom_separator.csv', '|']
        ];
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/CatalogImportExport/_files/update_category_duplicates.php
     */
    public function testProductDuplicateCategories()
    {
        $csvFixture = 'products_duplicate_category.csv';
        // import data from CSV file
        $pathToFile = __DIR__ . '/../_files/' . $csvFixture;
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource($source)->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product'
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() === 0);

        $this->_model->importData();

        $errorProcessor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator::class
        );
        $errorCount = count($errorProcessor->getAllErrors());
        $this->assertTrue($errorCount === 1, 'Error expected');

        $errorMessage = $errorProcessor->getAllErrors()[0]->getErrorMessage();
        $this->assertStringContainsString('URL key for specified store already exists', $errorMessage);
        $this->assertStringContainsString('Default Category/Category 2', $errorMessage);

        $categoryAfter = $this->loadCategoryByName('Category 2');
        $this->assertTrue($categoryAfter === null);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $product->load(1);
        $categories = $product->getCategoryIds();
        $this->assertTrue(count($categories) == 1);
    }

    protected function loadCategoryByName($categoryName)
    {
        /* @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Category\Collection::class);
        $collection->addNameToResult()->load();
        return $collection->getItemByColumnValue('name', $categoryName);
    }
}
