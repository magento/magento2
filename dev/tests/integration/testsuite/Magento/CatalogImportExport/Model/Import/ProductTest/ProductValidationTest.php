<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\Source\Csv;
use Magento\ImportExport\Test\Fixture\CsvFile as CsvFileFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 *
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 */
class ProductValidationTest extends ProductTestBase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     * @magentoAppIsolation enabled
     */
    public function testValidateInvalidMultiselectValues()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_with_invalid_multiselect_values.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 1);
        $this->assertEquals(
            "Value for 'multiselect_attribute' attribute contains incorrect value, "
            . "see acceptable values on settings specified for Admin",
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
        );
    }

    /**
     * @param array $row
     * @param string|null $behavior
     * @param bool $expectedResult
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @dataProvider validateRowDataProvider
     */
    public function testValidateRow(array $row, $behavior, $expectedResult)
    {
        $this->_model->setParameters(['behavior' => $behavior, 'entity' => 'catalog_product']);
        $this->assertSame($expectedResult, $this->_model->validateRow($row, 1));
    }

    /**
     * @return array
     */
    public static function validateRowDataProvider()
    {
        return [
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => null,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => null,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => null,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_DELETE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_DELETE,
                'expectedResult' => false,
            ],
            [
                'row' => ['sku' => 'simple products'],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => ['sku' => 'simple products absent'],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products absent',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => false,
            ],
            [
                'row' => [
                    'sku' => 'simple products',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_REPLACE,
                'expectedResult' => true,
            ],
            [
                'row' => ['sku' => 'sku with whitespace ',
                    'name' => 'Test',
                    'product_type' => 'simple',
                    '_attribute_set' => 'Default',
                    'price' => 10.20,
                ],
                'behavior' => Import::BEHAVIOR_ADD_UPDATE,
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @covers \Magento\ImportExport\Model\Import\Entity\AbstractEntity::_saveValidatedBunches
     */
    public function testValidateData()
    {
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
        )->setSource($source)->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
    }

    /**
     * Test import product with product links and empty value
     *
     * @param string $pathToFile
     * @param bool $expectedResultCrossell
     * @param bool $expectedResultUpsell
     *
     * @magentoDataFixture Magento/CatalogImportExport/_files/product_export_with_product_links_data.php
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @dataProvider getEmptyLinkedData
     */
    public function testProductLinksWithEmptyValue(
        string $pathToFile,
        bool $expectedResultCrossell,
        bool $expectedResultUpsell
    ): void {
        $filesystem = BootstrapHelper::getObjectManager()->create(Filesystem::class);

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            Csv::class,
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setSource(
            $source
        )->setParameters(
            [
                'behavior' => Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product'
            ]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $objectManager = BootstrapHelper::getObjectManager();
        $resource = $objectManager->get(ProductResource::class);
        $productId = $resource->getIdBySku('simple');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = BootstrapHelper::getObjectManager()->create(Product::class);
        $product->load($productId);

        $this->assertEquals(empty($product->getCrossSellProducts()), $expectedResultCrossell);
        $this->assertEquals(empty($product->getUpSellProducts()), $expectedResultUpsell);
    }

    /**
     * Get data for empty linked product
     *
     * @return array[]
     */
    public static function getEmptyLinkedData(): array
    {
        return [
            [
                __DIR__ . '/../_files/products_to_import_with_product_links_with_empty_value.csv',
                true,
                true,
            ],
            [
                __DIR__ . '/../_files/products_to_import_with_product_links_with_empty_data.csv',
                false,
                true,
            ],
        ];
    }

    /**
     * Tests that empty attribute value in the CSV file will be ignored after update a product by the import.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_varchar_attribute.php
     */
    public function testEmptyAttributeValueShouldBeIgnoredAfterUpdateProductByImport()
    {
        $pathToFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR
            . 'import_product_with_empty_attribute_value.csv';
        /** @var ImportProduct $importModel */
        $importModel = $this->createImportModel($pathToFile);
        /** @var ProcessingErrorAggregatorInterface $errors */
        $errors = $importModel->validateData();
        $this->assertTrue($errors->getErrorsCount() === 0, 'Import file validation failed.');
        $importModel->importData();

        $simpleProduct = $this->productRepository->get('simple', false, null, true);
        $this->assertEquals('Varchar default value', $simpleProduct->getData('varchar_attribute'));
        $this->assertEquals('Short description', $simpleProduct->getData('short_description'));
    }

    /**
     * Tests that no products imported if source file contains errors
     *
     * In this case, the second product data has an invalid attribute set.
     */
    public function testInvalidSkuLink()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../_files/products_to_import_invalid_attribute_set.csv';
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
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                Import::FIELD_NAME_VALIDATION_STRATEGY => null,
                'entity' => 'catalog_product'
            ]
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 1);
        $this->assertEquals(
            'Invalid value for Attribute Set column (set doesn\'t exist?)',
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
        );
        $this->_model->importData();

        $productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class
        );

        $products = [];
        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $products[$product->getSku()] = $product;
        }
        $this->assertArrayNotHasKey("simple1", $products, "Simple Product should not have been imported");
        $this->assertArrayNotHasKey("simple3", $products, "Simple Product 3 should not have been imported");
        $this->assertArrayNotHasKey("simple2", $products, "Simple Product2 should not have been imported");
    }

    /**
     * Test invalid weight
     */
    public function testProductWithInvalidWeight()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/../_files/product_to_import_invalid_weight.csv';
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
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 1);
        $this->assertEquals(
            "Value for 'weight' attribute contains incorrect value",
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
        );
    }

    /**
     * Test validate multiselect values with custom separator
     *
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testValidateMultiselectValuesWithCustomSeparator(): void
    {
        $pathToFile = __DIR__ . './../_files/products_with_custom_multiselect_values_separator.csv';
        $filesystem = $this->objectManager->create(Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(Csv::class, ['file' => $pathToFile, 'directory' => $directory]);
        $params = [
            'behavior' => Import::BEHAVIOR_ADD_UPDATE,
            'entity' => 'catalog_product',
            Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR => '|||'
        ];

        $errors = $this->_model->setParameters($params)
            ->setSource($source)
            ->validateData();

        $this->assertEmpty($errors->getAllErrors());
    }

    #[
        DataFixture(AttributeFixture::class, ['is_unique' => 1, 'attribute_code' => 'uniq_test_attr']),
        DataFixture(ProductFixture::class, ['uniq_test_attr' => 'uniq_test_attr_val'], as: 'p1'),
        DataFixture(ProductFixture::class, as: 'p2'),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    ['sku', 'product_type', 'additional_attributes'],
                    ['$p2.sku$', 'simple', 'uniq_test_attr=uniq_test_attr_val'],
                ]
            ],
            'file'
        )
    ]
    public function testUniqueValidationShouldFailIfValueExistForAnotherProduct(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $pathToFile = $fixtures->get('file')->getAbsolutePath();
        $importModel = $this->createImportModel($pathToFile);
        $errors = $importModel->validateData();
        $this->assertErrorsCount(1, $errors);
        $this->assertEquals(
            RowValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE,
            $errors->getErrorByRowNumber(0)[0]->getErrorCode()
        );
    }

    #[
        DataFixture(AttributeFixture::class, ['is_unique' => 1, 'attribute_code' => 'uniq_test_attr']),
        DataFixture(ProductFixture::class, ['uniq_test_attr' => 'uniq_test_attr_val'], as: 'p1'),
        DataFixture(ProductFixture::class, ['uniq_test_attr' => 'uniq_test_attr_val2'], as: 'p2'),
        DataFixture(
            CsvFileFixture::class,
            [
                'rows' => [
                    ['sku', 'product_type', 'additional_attributes'],
                    ['$p1.sku$', 'simple', 'uniq_test_attr=uniq_test_attr_val'],
                ]
            ],
            'file'
        )
    ]
    public function testUniqueValidationShouldNotFailIfValueExistForTheImportedProductOnly(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $pathToFile = $fixtures->get('file')->getAbsolutePath();
        $importModel = $this->createImportModel($pathToFile);
        $errors = $importModel->validateData();
        $this->assertErrorsCount(0, $errors);
        $importModel->importData();
    }
}
