<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\ProductTest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\ProductTestBase;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Source\Csv;

/**
 * Integration test for \Magento\CatalogImportExport\Model\Import\Product class.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea adminhtml
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_catalog_product_reindex_schedule.php
 */
class ProductUrlKeyTest extends ProductTestBase
{
    /**
     * Make sure the absence of a url_key column in the csv file won't erase the url key of the existing products.
     * To reach the goal we need to not send the name column, as the url key is generated from it.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testImportWithoutUrlKeysAndName()
    {
        $products = [
            'simple1' => 'url-key',
            'simple2' => 'url-key2',
        ];
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_without_url_keys_and_name.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )
            ->setSource($source)
            ->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @dataProvider validateUrlKeysDataProvider
     * @param $importFile string
     * @param $expectedErrors array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testValidateUrlKeys($importFile, $expectedErrors)
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/' . $importFile,
                'directory' => $directory
            ]
        );
        /** @var \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errors */
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();
        $this->assertEquals(
            $expectedErrors[RowValidatorInterface::ERROR_DUPLICATE_URL_KEY],
            count($errors->getErrorsByCode([RowValidatorInterface::ERROR_DUPLICATE_URL_KEY]))
        );
    }

    /**
     * @return array
     */
    public function validateUrlKeysDataProvider()
    {
        return [
            [
                'products_to_check_valid_url_keys.csv',
                [
                    RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 0
                ]
            ],
            [
                'products_to_check_valid_url_keys_with_different_language.csv',
                [
                    RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 0
                ]
            ],
            [
                'products_to_check_duplicated_url_keys.csv',
                [
                    RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 2
                ]
            ],
            [
                'products_to_check_duplicated_names.csv' ,
                [
                    RowValidatorInterface::ERROR_DUPLICATE_URL_KEY => 1
                ]
            ]
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     */
    public function testValidateUrlKeysMultipleStores()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Filesystem::class
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_check_valid_url_keys_multiple_stores.csv',
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testExistingProductWithUrlKeys()
    {
        $products = [
            'simple1' => 'url-key1',
            'simple2' => 'url-key2',
            'simple3' => 'url-key3'
        ];
        // added by _files/products_to_import_with_valid_url_keys.csv
        $this->importedProducts[] = 'simple3';

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_valid_url_keys.csv',
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

        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_wrong_url_key.php
     */
    public function testAddUpdateProductWithInvalidUrlKeys() : void
    {
        $products = [
            'simple1' => 'cuvee-merlot-cabernet-igp-pays-d-oc-frankrijk',
            'simple2' => 'normal-url',
            'simple3' => 'some-wrong-url'
        ];
        // added by _files/products_to_import_with_invalid_url_keys.csv
        $this->importedProducts[] = 'simple3';

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_invalid_url_keys.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testImportWithoutChangingUrlKeys()
    {
        $filesystem = $this->objectManager->create(Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_without_url_key_column.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )
            ->setSource($source)
            ->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->assertEquals('url-key', $productRepository->get('simple1')->getUrlKey());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     */
    public function testImportWithoutUrlKeys()
    {
        $products = [
            'simple1' => 'simple-1',
            'simple2' => 'simple-2',
            'simple3' => 'simple-3'
        ];
        // added by _files/products_to_import_without_url_keys.csv
        $this->importedProducts[] = 'simple3';

        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_without_url_keys.csv',
                'directory' => $directory
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )
            ->setSource($source)
            ->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_non_latin_url_key.php
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testImportWithNonLatinUrlKeys()
    {
        $productsCreatedByFixture = [
            'ukrainian-with-url-key' => 'nove-im-ja-pislja-importu-scho-stane-url-key',
            'ukrainian-without-url-key' => 'novij-url-key-pislja-importu',
        ];
        $productsImportedByCsv = [
            'imported-ukrainian-with-url-key' => 'importovanij-produkt',
            'imported-ukrainian-without-url-key' => 'importovanij-produkt-bez-url-key',
        ];
        $productSkuMap = array_merge($productsCreatedByFixture, $productsImportedByCsv);
        $this->importedProducts = array_keys($productsImportedByCsv);

        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_non_latin_url_keys.csv',
                'directory' => $directory,
            ]
        );

        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE, 'entity' => 'catalog_product']
        )
            ->setSource($source)
            ->validateData();

        $this->assertEquals($errors->getErrorsCount(), 0);
        $this->_model->importData();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        foreach ($productSkuMap as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_spaces_in_url_key.php
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testImportWithSpacesInUrlKeys()
    {
        $products = [
            'simple1' => 'url-with-spaces-1',
            'simple2' => 'url-with-spaces-2'
        ];
        $filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            \Magento\ImportExport\Model\Import\Source\Csv::class,
            [
                'file' => __DIR__ . '/../_files/products_to_import_with_spaces_in_url_keys.csv',
                'directory' => $directory,
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE, 'entity' => 'catalog_product']
        )
            ->setSource($source)
            ->validateData();

        $this->assertEquals($errors->getErrorsCount(), 0);
        $this->_model->importData();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        foreach ($products as $productSku => $productUrlKey) {
            $this->assertEquals($productUrlKey, $productRepository->get($productSku)->getUrlKey());
        }
    }
}
