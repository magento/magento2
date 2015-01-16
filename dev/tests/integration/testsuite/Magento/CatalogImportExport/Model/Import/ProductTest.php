<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\CatalogImportExport\Model\Import\Product
 *
 * The "CouplingBetweenObjects" warning is caused by tremendous complexity of the original class
 *
 */
namespace Magento\CatalogImportExport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ProductTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

    protected function setUp()
    {
        $this->_uploaderFactory = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\UploaderFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\CatalogImportExport\Model\Import\Product',
            ['uploaderFactory' => $this->_uploaderFactory]
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
     * magentoDataFixture Magento/Catalog/_files/multiple_products.php
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

        $source = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/_files/products_to_import.csv',
            $directory
        );
        $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->isDataValid();

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
     * magentoDataFixture Magento/Catalog/_files/multiple_products.php
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
        $source = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/_files/products_to_import.csv',
            $directory
        );
        $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->isDataValid();

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
     * Tests adding of custom options with existing and new product
     *
     * @param $behavior
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getBehaviorDataProvider
     * @param string $behavior
     * @param string $importFile
     * @param string $sku
     */
    public function testSaveCustomOptions($behavior, $importFile, $sku)
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/' . $importFile;

        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = new \Magento\ImportExport\Model\Import\Source\Csv($pathToFile, $directory);
        $this->_model->setSource($source)->setParameters(['behavior' => $behavior])->isDataValid();
        $this->_model->importData();

        $productModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product = $productModel->loadByAttribute('sku', $sku);
        // product from fixture
        $options = $product->getProductOptionsCollection();

        $expectedData = $this->_getExpectedOptionsData($pathToFile);
        $expectedData = $this->_mergeWithExistingData($expectedData, $options);
        $actualData = $this->_getActualOptionsData($options);

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
     * Test if datetime properly saved after import
     *
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     * TODO MAGETWO-31206
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

        $source = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/_files/products_to_import_with_datetime.csv',
            $directory
        );
        $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->isDataValid();

        $this->_model->importData();

        reset($source);
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
    protected function _getExpectedOptionsData($pathToFile)
    {
        $productData = $this->_csvToArray(file_get_contents($pathToFile));
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
     * @param \Magento\Catalog\Model\Resource\Product\Option\Collection $options
     * @return array
     */
    protected function _mergeWithExistingData(
        array $expected,
        \Magento\Catalog\Model\Resource\Product\Option\Collection $options
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
                $expectedData[$expectedOptionId] = $this->_getOptionData($option);
                if ($optionValues = $this->_getOptionValues($option)) {
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
     * @param \Magento\Catalog\Model\Resource\Product\Option\Collection $options
     * @return array
     */
    protected function _getActualOptionsData(\Magento\Catalog\Model\Resource\Product\Option\Collection $options)
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
            $actualOptions[$actualOptionId] = $lastOptionKey;
            $actualData[$actualOptionId] = $this->_getOptionData($option);
            if ($optionValues = $this->_getOptionValues($option)) {
                $actualValues[$actualOptionId] = $optionValues;
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
    protected function _getOptionData(\Magento\Catalog\Model\Product\Option $option)
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
    protected function _getOptionValues(\Magento\Catalog\Model\Product\Option $option)
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
     * Data provider for test 'testSaveCustomOptionsDuplicate'
     *
     * @return array
     */
    public function getBehaviorDataProvider()
    {
        return [
            'Append behavior with existing product' => [
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                '$importFile' => 'product_with_custom_options.csv',
                '$sku' => 'simple',
            ],
            'Append behavior with new product' => [
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
                '$importFile' => 'product_with_custom_options_new.csv',
                '$sku' => 'simple_new',
            ],
            'Replace behavior with existing product' => [
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
                '$importFile' => 'product_with_custom_options.csv',
                '$sku' => 'simple',
            ],
            'Replace behavior with new product' => [
                '$behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
                '$importFile' => 'product_with_custom_options_new.csv',
                '$sku' => 'simple_new',
            ]
        ];
    }

    /**
     * @magentoDataIsolation enabled
     * @magentoDataFixture mediaImportImageFixture
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveMediaImage()
    {
        $this->markTestSkipped(
            'The test is skipped due to incomplete story https://jira.corp.x.com/browse/MAGETWO-15713'
        );
        $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Entity\Attribute'
        );
        $attribute->loadByCode('catalog_product', 'media_gallery');
        $data = implode(
            ',',
            [
                // minimum required set of attributes + media images
                'sku',
                '_attribute_set',
                '_type',
                '_product_websites',
                'name',
                'price',
                'description',
                'short_description',
                'weight',
                'status',
                'visibility',
                'tax_class_id',
                '_media_attribute_id',
                '_media_image',
                '_media_label',
                '_media_position',
                '_media_is_disabled'
            ]
        ) . "\n";
        $data .= implode(
            ',',
            [
                'test_sku',
                'Default',
                \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE,
                'base',
                'Product Name',
                '9.99',
                'Product description',
                'Short desc.',
                '1',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
                0,
                $attribute->getId(),
                'magento_image.jpg',
                'Image Label',
                '1',
                '0'
            ]
        ) . "\n";
        $data = 'data://text/plain;base64,' . base64_encode($data);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fixture = $objectManager->create(
            'Magento\ImportExport\Model\Import\Source\Csv',
            ['$fileOrStream' => $data]
        );

        foreach (\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Resource\Product\Collection'
        ) as $product) {
            $this->fail("Unexpected precondition - product exists: '{$product->getId()}'.");
        }

        $uploader = $this->getMock(
            'Magento\CatalogImportExport\Model\Import\Uploader',
            ['init'],
            [
                $objectManager->create('Magento\Core\Helper\File\Storage\Database'),
                $objectManager->create('Magento\Core\Helper\File\Storage'),
                $objectManager->create('Magento\Framework\Image\AdapterFactory'),
                $objectManager->create('Magento\Core\Model\File\Validator\NotProtectedExtension')
            ]
        );
        $this->_uploaderFactory->expects($this->any())->method('create')->will($this->returnValue($uploader));

        $this->_model->setSource(
            $fixture
        )->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]
        )->isDataValid();
        $this->_model->importData();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $resource = $objectManager->get('Magento\Catalog\Model\Resource\Product');
        $productId = $resource->getIdBySku('test_sku');
        // fixture
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product'
        );
        $product->load($productId);
        $gallery = $product->getMediaGalleryImages();
        $this->assertInstanceOf('Magento\Framework\Data\Collection', $gallery);
        $items = $gallery->getItems();
        $this->assertCount(1, $items);
        $item = array_pop($items);
        $this->assertInstanceOf('Magento\Framework\Object', $item);
        $this->assertEquals('magento_image.jpg', $item->getFile());
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
    protected function _csvToArray($content, $entityId = null)
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
     * Tests that an import will still work with an invalid import line and
     * SKU data.
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
        $source = new \Magento\ImportExport\Model\Import\Source\Csv($pathToFile, $directory);
        $this->_model->setSource(
            $source
        )->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]
        )->isDataValid();
        $this->_model->importData();

        $productCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Resource\Product\Collection'
        );

        $products = [];
        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $products[$product->getSku()] = $product;
        }
        $this->assertArrayHasKey("simple1", $products, "Simple Product should have been imported");
        $this->assertArrayHasKey("simple3", $products, "Simple Product 3 should have been imported");
        $this->assertArrayNotHasKey("simple2", $products, "Simple Product2 should not have been imported");

        $upsellProductIds = $products["simple3"]->getUpSellProductIds();
        $this->assertEquals(
            0,
            count($upsellProductIds),
            "There should not be any linked upsell SKUs. The original" . " product SKU linked does not import cleanly."
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     */
    public function testValidateInvalidMultiselectValues()
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/products_with_invalid_multiselect_values.csv';
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem'
        );
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = new \Magento\ImportExport\Model\Import\Source\Csv($pathToFile, $directory);
        $validationResult = $this->_model->setSource(
            $source
        )->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]
        )->isDataValid();

        $this->assertFalse($validationResult);

        $errors = $this->_model->getErrorMessages();
        $expectedErrors = [
            "Please correct the value for 'multiselect_attribute'." => [2],
            "Orphan rows that will be skipped due default row errors" => [3,4],
        ];
        foreach ($expectedErrors as $message => $invalidRows) {
            $this->assertArrayHasKey($message, $errors);
            $this->assertEquals($invalidRows, $errors[$message]);
        }
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoDataFixture Magento/Core/_files/store.php
     * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/_files/attribute_with_option.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     */
    public function testProductsWithMultipleStores()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $filesystem = $objectManager->create('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);

        $source = new \Magento\ImportExport\Model\Import\Source\Csv(
            __DIR__ . '/_files/products_multiple_stores.csv',
            $directory
        );
        $this->_model->setParameters(
            ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'entity' => 'catalog_product']
        )->setSource(
            $source
        )->isDataValid();

        $this->_model->importData();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create('Magento\Catalog\Model\Product');
        $id = $product->getIdBySku('Configurable 03');
        $product->load($id);
        $this->assertEquals('1', $product->getHasOptions());

        $objectManager->get('Magento\Store\Model\StoreManagerInterface')->setCurrentStore('fixturestore');

        /** @var \Magento\Catalog\Model\Product $simpleProduct */
        $simpleProduct = $objectManager->create('Magento\Catalog\Model\Product');
        $id = $simpleProduct->getIdBySku('Configurable 03-option_0');
        $simpleProduct->load($id);
        $this->assertEquals('Option Label', $simpleProduct->getAttributeText('attribute_with_option'));
        $this->assertEquals([2, 4], $simpleProduct->getAvailableInCategories());
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
        $source = new \Magento\ImportExport\Model\Import\Source\Csv($pathToFile, $directory);
        $validationResult = $this->_model->setSource(
            $source
        )->setParameters(
                ['behavior' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND]
            )->isDataValid();

        $expectedErrors = ["Product weight is invalid" => [2]];

        $this->assertFalse($validationResult);
        $this->assertEquals($expectedErrors, $this->_model->getErrorMessages());
    }
}
