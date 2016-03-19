<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Test class for \Magento\CatalogImportExport\Model\Import\Product
 *
 * The "CouplingBetweenObjects" warning is caused by tremendous complexity of the original class
 *
 */
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

/**
 * Class ProductTest
 *
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $_model;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $_uploader;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_stockStateProvider;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogImportExport\Model\Import\Product'
        );
    }

    /**
     * Options for assertion
     *
     * @var array
     */
    protected $_assertOptions = [
        'is_require' => '_custom_option_is_required',
        'price' => '_custom_option_price',
        'sku' => '_custom_option_sku',
        'sort_order' => '_custom_option_sort_order',
    ];

    /**
     * Option values for assertion
     *
     * @var array
     */
    protected $_assertOptionValues = ['title', 'price', 'sku'];

    /**
     * Test if visibility properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveProductsVisibility()
    {
        $existingProductIds = [10, 11, 12];
        $productsBeforeImport = [];
        foreach ($existingProductIds as $productId) {
            $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Catalog\Model\Product'
            );
            $product->load($productId);
            $productsBeforeImport[] = $product;
        }

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/products_to_import.csv',
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

        /** @var $productBeforeImport \Magento\Catalog\Model\Product */
        foreach ($productsBeforeImport as $productBeforeImport) {
            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productAfterImport = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Catalog\Model\Product'
            );
            $productAfterImport->load($productBeforeImport->getId());

            $this->assertEquals($productBeforeImport->getVisibility(), $productAfterImport->getVisibility());
            unset($productAfterImport);
        }

        unset($productsBeforeImport, $product);
    }

    /**
     * Test if stock item quantity properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveStockItemQty()
    {
        $existingProductIds = [10, 11, 12];
        $stockItems = [];
        foreach ($existingProductIds as $productId) {
            /** @var $stockRegistry \Magento\CatalogInventory\Model\StockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\CatalogInventory\Model\StockRegistry'
            );

            $stockItem = $stockRegistry->getStockItem($productId, 1);
            $stockItems[$productId] = $stockItem;
        }

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/products_to_import.csv',
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
            /** @var $stockRegistry \Magento\CatalogInventory\Model\StockRegistry */
            $stockRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\CatalogInventory\Model\StockRegistry'
            );

            $stockItemAfterImport = $stockRegistry->getStockItem($productId, 1);

            $this->assertEquals($stockItmBeforeImport->getQty(), $stockItemAfterImport->getQty());
            $this->assertEquals(1, $stockItemAfterImport->getIsInStock());
            unset($stockItemAfterImport);
        }

        unset($stockItems, $stockItem);
    }

    /**
     * Test if stock state properly changed after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testStockState()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/products_to_import_with_qty.csv',
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
     * Tests adding of custom options with existing and new product
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getBehaviorDataProvider
     * @param string $importFile
     * @param string $sku
     * @magentoAppIsolation enabled
     */
    public function testSaveCustomOptions($importFile, $sku)
    {
        $pathToFile = __DIR__ . '/_files/' . $importFile;
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => $pathToFile,
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

        /** @var \Magento\Catalog\Model\Product $productModel */
        $productModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product = $productModel->loadByAttribute('sku', $sku);

        $this->assertInstanceOf('Magento\Catalog\Model\Product', $product);
        $options = $product->getProductOptionsCollection();

        $expectedData = $this->getExpectedOptionsData($pathToFile);
        $expectedData = $this->mergeWithExistingData($expectedData, $options);
        $actualData = $this->getActualOptionsData($options);

        // assert of equal type+titles
        $expectedOptions = $expectedData['options'];
        // we need to save key values
        $actualOptions = $actualData['options'];
        sort($expectedOptions);
        sort($actualOptions);
        $this->assertEquals($expectedOptions, $actualOptions);

        // assert of options data
        $this->assertCount(count($expectedData['data']), $actualData['data']);
        $this->assertCount(count($expectedData['values']), $actualData['values']);
        foreach ($expectedData['options'] as $expectedId => $expectedOption) {
            $elementExist = false;
            // find value in actual options and values
            foreach ($actualData['options'] as $actualId => $actualOption) {
                if ($actualOption == $expectedOption) {
                    $elementExist = true;
                    $this->assertEquals($expectedData['data'][$expectedId], $actualData['data'][$actualId]);
                    if (array_key_exists($expectedId, $expectedData['values'])) {
                        $this->assertEquals($expectedData['values'][$expectedId], $actualData['values'][$actualId]);
                    }
                    unset($actualData['options'][$actualId]);
                    // remove value in case of duplicating key values
                    break;
                }
            }
            $this->assertTrue($elementExist, 'Element must exist.');
        }
    }

    /**
     * Data provider for test 'testSaveCustomOptionsDuplicate'
     *
     * @return array
     */
    public function getBehaviorDataProvider()
    {
        return [
            'Append behavior with existing product' => [
                '$importFile' => 'product_with_custom_options.csv',
                '$sku' => 'simple',
            ],
            'Append behavior with new product' => [
                '$importFile' => 'product_with_custom_options_new.csv',
                '$sku' => 'simple_new',
            ]
        ];
    }

    /**
     * Test if datetime properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoAppIsolation enabled
     */
    public function testSaveDatetimeAttribute()
    {
        $existingProductIds = [10, 11, 12];
        $productsBeforeImport = [];
        foreach ($existingProductIds as $productId) {
            $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Catalog\Model\Product'
            );
            $product->load($productId);
            $productsBeforeImport[$product->getSku()] = $product;
        }

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/products_to_import_with_datetime.csv',
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

        $source->rewind();
        foreach ($source as $row) {
            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productBeforeImport = $productsBeforeImport[$row['sku']];

            /** @var $productAfterImport \Magento\Catalog\Model\Product */
            $productAfterImport = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Catalog\Model\Product'
            );
            $productAfterImport->load($productBeforeImport->getId());
            $this->assertEquals(
                @strtotime($row['news_from_date']),
                @strtotime($productAfterImport->getNewsFromDate())
            );
            unset($productAfterImport);
        }
        unset($productsBeforeImport, $product);
    }

    /**
     * Returns expected product data: current id, options, options data and option values
     *
     * @param string $pathToFile
     * @return array
     */
    protected function getExpectedOptionsData($pathToFile)
    {
        $productData = $this->csvToArray(file_get_contents($pathToFile));
        $expectedOptionId = 0;
        $expectedOptions = [];
        // array of type and title types, key is element ID
        $expectedData = [];
        // array of option data
        $expectedValues = [];
        // array of option values data
        foreach ($productData['data'] as $data) {
            if (!empty($data['_custom_option_type']) && !empty($data['_custom_option_title'])) {
                $lastOptionKey = $data['_custom_option_type'] . '|' . $data['_custom_option_title'];
                $expectedOptionId++;
                $expectedOptions[$expectedOptionId] = $lastOptionKey;
                $expectedData[$expectedOptionId] = [];
                foreach ($this->_assertOptions as $assertKey => $assertFieldName) {
                    if (array_key_exists($assertFieldName, $data)) {
                        $expectedData[$expectedOptionId][$assertKey] = $data[$assertFieldName];
                    }
                }
            }
            if (!empty($data['_custom_option_row_title']) && empty($data['_custom_option_store'])) {
                $optionData = [];
                foreach ($this->_assertOptionValues as $assertKey) {
                    $valueKey = \Magento\CatalogImportExport\Model\Import\Product\Option::COLUMN_PREFIX .
                        'row_' .
                        $assertKey;
                    $optionData[$assertKey] = $data[$valueKey];
                }
                $expectedValues[$expectedOptionId][] = $optionData;
            }
        }

        return [
            'id' => $expectedOptionId,
            'options' => $expectedOptions,
            'data' => $expectedData,
            'values' => $expectedValues
        ];
    }

    /**
     * Updates expected options data array with existing unique options data
     *
     * @param array $expected
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $options
     * @return array
     */
    protected function mergeWithExistingData(
        array $expected,
        \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $options
    ) {
        $expectedOptionId = $expected['id'];
        $expectedOptions = $expected['options'];
        $expectedData = $expected['data'];
        $expectedValues = $expected['values'];
        foreach ($options->getItems() as $option) {
            $optionKey = $option->getType() . '|' . $option->getTitle();
            if (!in_array($optionKey, $expectedOptions)) {
                $expectedOptionId++;
                $expectedOptions[$expectedOptionId] = $optionKey;
                $expectedData[$expectedOptionId] = $this->getOptionData($option);
                if ($optionValues = $this->getOptionValues($option)) {
                    $expectedValues[$expectedOptionId] = $optionValues;
                }
            }
        }

        return [
            'id' => $expectedOptionId,
            'options' => $expectedOptions,
            'data' => $expectedData,
            'values' => $expectedValues
        ];
    }

    /**
     *  Returns actual product data: current id, options, options data and option values
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $options
     * @return array
     */
    protected function getActualOptionsData(\Magento\Catalog\Model\ResourceModel\Product\Option\Collection $options)
    {
        $actualOptionId = 0;
        $actualOptions = [];
        // array of type and title types, key is element ID
        $actualData = [];
        // array of option data
        $actualValues = [];
        // array of option values data
        /** @var $option \Magento\Catalog\Model\Product\Option */
        foreach ($options->getItems() as $option) {
            $lastOptionKey = $option->getType() . '|' . $option->getTitle();
            $actualOptionId++;
            if (!in_array($lastOptionKey, $actualOptions)) {
                $actualOptions[$actualOptionId] = $lastOptionKey;
                $actualData[$actualOptionId] = $this->getOptionData($option);
                if ($optionValues = $this->getOptionValues($option)) {
                    $actualValues[$actualOptionId] = $optionValues;
                }
            }
        }
        return [
            'id' => $actualOptionId,
            'options' => $actualOptions,
            'data' => $actualData,
            'values' => $actualValues
        ];
    }

    /**
     * Retrieve option data
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return array
     */
    protected function getOptionData(\Magento\Catalog\Model\Product\Option $option)
    {
        $result = [];
        foreach (array_keys($this->_assertOptions) as $assertKey) {
            $result[$assertKey] = $option->getData($assertKey);
        }
        return $result;
    }

    /**
     * Retrieve option values or false for options which has no values
     *
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return array|bool
     */
    protected function getOptionValues(\Magento\Catalog\Model\Product\Option $option)
    {
        $values = $option->getValues();
        if (!empty($values)) {
            $result = [];
            /** @var $value \Magento\Catalog\Model\Product\Option\Value */
            foreach ($values as $value) {
                $optionData = [];
                foreach ($this->_assertOptionValues as $assertKey) {
                    if ($value->hasData($assertKey)) {
                        $optionData[$assertKey] = $value->getData($assertKey);
                    }
                }
                $result[] = $optionData;
            }
            return $result;
        }

        return false;
    }

    /**
     * @magentoDataIsolation enabled
     * @magentoDataFixture mediaImportImageFixture
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveMediaImage()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/import_media.csv',
                'directory' => $directory
            ]
        );
        $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                'entity' => 'catalog_product',
                'import_images_file_dir' => 'pub/media/import'
            ]
        );
        $appParams = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getBootstrap()->getApplication()->getInitParams()[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS];
        $uploader = $this->_model->getUploader();

        $destDir = $directory->getRelativePath($appParams[DirectoryList::MEDIA][DirectoryList::PATH] . '/catalog/product');
        $tmpDir = $directory->getRelativePath($appParams[DirectoryList::MEDIA][DirectoryList::PATH] . '/import');

        $directory->create($destDir);
        $this->assertTrue($uploader->setDestDir($destDir));
        $this->assertTrue($uploader->setTmpDir($tmpDir));
        $errors = $this->_model->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == 0);
        $this->_model->importData();

        $resource = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product');
        $productId = $resource->getIdBySku('simple_new');

        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load($productId);
        $this->assertEquals('/m/a/magento_image.jpg', $product->getData('swatch_image'));
        $gallery = $product->getMediaGalleryImages();
        $this->assertInstanceOf('Magento\Framework\Data\Collection', $gallery);
        $items = $gallery->getItems();
        $this->assertCount(1, $items);
        $item = array_pop($items);
        $this->assertInstanceOf('Magento\Framework\DataObject', $item);
        $this->assertEquals('/m/a/magento_image.jpg', $item->getFile());
        $this->assertEquals('Image Label', $item->getLabel());
    }

    /**
     * Copy a fixture image into media import directory
     */
    public static function mediaImportImageFixture()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
        $mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Filesystem'
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $mediaDirectory->create('import');
        $dirPath = $mediaDirectory->getAbsolutePath('import');
        copy(__DIR__ . '/../../../../Magento/Catalog/_files/magento_image.jpg', "{$dirPath}/magento_image.jpg");
    }

    /**
     * Cleanup media import and catalog directories
     */
    public static function mediaImportImageFixtureRollback()
    {
        /** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
        $mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Filesystem'
        )->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $mediaDirectory->delete('import');
        $mediaDirectory->delete('catalog');
    }

    /**
     * Export CSV string to array
     *
     * @param string $content
     * @param mixed $entityId
     * @return array
     */
    protected function csvToArray($content, $entityId = null)
    {
        $data = ['header' => [], 'data' => []];

        $lines = str_getcsv($content, "\n");
        foreach ($lines as $index => $line) {
            if ($index == 0) {
                $data['header'] = str_getcsv($line);
            } else {
                $row = array_combine($data['header'], str_getcsv($line));
                if (!is_null($entityId) && !empty($row[$entityId])) {
                    $data['data'][$row[$entityId]] = $row;
                } else {
                    $data['data'][] = $row;
                }
            }
        }
        return $data;
    }

    /**
     * Tests that no products imported if source file contains errors
     *
     * In this case, the second product data has an invalid attribute set.
     *
     * @magentoDbIsolation enabled
     */
    public function testInvalidSkuLink()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/products_to_import_invalid_attribute_set.csv';
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem'
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => $pathToFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            [
                'behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
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
            'Magento\Catalog\Model\ResourceModel\Product\Collection'
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
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     * @magentoAppIsolation enabled
     */
    public function testValidateInvalidMultiselectValues()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem'
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/products_with_invalid_multiselect_values.csv',
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
            ."see acceptable values on settings specified for Admin",
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoAppIsolation enabled
     */
    public function testProductsWithMultipleStores()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $filesystem = $objectManager->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/products_multiple_stores.csv',
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

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $id = $product->getIdBySku('Configurable 03');
        $product->load($id);
        $this->assertEquals('1', $product->getHasOptions());

        $objectManager->get('Magento\Store\Model\StoreManagerInterface')->setCurrentStore('fixturestore');

        /** @var \Magento\Catalog\Model\Product $simpleProduct */
        $simpleProduct = $objectManager->create('Magento\Catalog\Model\Product');
        $id = $simpleProduct->getIdBySku('Configurable 03-Option 1');
        $simpleProduct->load($id);
        $this->assertTrue(count($simpleProduct->getWebsiteIds()) == 2);
        $this->assertEquals('Option Label', $simpleProduct->getAttributeText('attribute_with_option'));
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testProductWithInvalidWeight()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/product_to_import_invalid_weight.csv';
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem'
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
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
     * @magentoAppArea adminhtml
     * @dataProvider categoryTestDataProvider
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProductCategories($fixture, $separator)
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/' . $fixture;
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem'
        );

        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
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
        $resource = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product');
        $productId = $resource->getIdBySku('simple1');
        $this->assertTrue(is_numeric($productId));
        /** @var \Magento\Catalog\Model\Product $product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load($productId);
        $this->assertFalse($product->isObjectNew());
        $categories = $product->getCategoryIds();
        $this->assertTrue(count($categories) == 2);
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
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @magentoAppIsolation enabled
     * @dataProvider validateUrlKeysDataProvider
     * @param $importFile string
     * @param $errorsCount int
     */
    public function testValidateUrlKeys($importFile, $errorsCount)
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem'
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/' . $importFile,
                'directory' => $directory
            ]
        );
        $errors = $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->validateData();

        $this->assertTrue($errors->getErrorsCount() == $errorsCount);
        if ($errorsCount >= 1) {
            $this->assertEquals(
            "Specified url key is already exist",
            $errors->getErrorByRowNumber(1)[0]->getErrorMessage()
            );
        }
    }

    /**
     * @return array
     */
    public function validateUrlKeysDataProvider()
    {
        return [
            ['products_to_check_valid_url_keys.csv', 0],
            ['products_to_check_duplicated_url_keys.csv', 2],
            ['products_to_check_duplicated_names.csv' , 1]
        ];
    }

    /**
     * @magentoDataFixture Magento/Store/_files/website.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/Model/ResourceModel/_files/product_simple.php
     * @magentoAppIsolation enabled
     */
    public function testValidateUrlKeysMultipleStores()
    {
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem'
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = $this->objectManager->create(
            '\Magento\ImportExport\Model\Import\Source\Csv',
            [
                'file' => __DIR__ . '/_files/products_to_check_valid_url_keys_multiple_stores.csv',
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
}
