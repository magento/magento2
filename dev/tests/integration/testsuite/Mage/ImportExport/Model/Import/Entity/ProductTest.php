<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Mage_ImportExport
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Model_Import_Entity_Product
 *
 * The "CouplingBetweenObjects" warning is caused by tremendous complexity of the original class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_ImportExport_Model_Import_Entity_ProductTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_ImportExport_Model_Import_Entity_Product
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = Mage::getModel('Mage_ImportExport_Model_Import_Entity_Product');
    }

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Options for assertion
     *
     * @var array
     */
    protected $_assertOptions = array(
        'is_require' => '_custom_option_is_required',
        'price'      => '_custom_option_price',
        'sku'        => '_custom_option_sku',
        'sort_order' => '_custom_option_sort_order',
    );

    /**
     * Option values for assertion
     *
     * @var array
     */
    protected $_assertOptionValues = array('title', 'price', 'sku');

    /**
     * Test if visibility properly saved after import
     *
     * magentoDataFixture Mage/Catalog/_files/multiple_products.php
     */
    public function testSaveProductsVisibility()
    {
        $existingProductIds = array(10, 11, 12);
        $productsBeforeImport = array();
        foreach ($existingProductIds as $productId) {
            $product = Mage::getModel('Mage_Catalog_Model_Product');
            $product->load($productId);
            $productsBeforeImport[] = $product;
        }

        $source = new Mage_ImportExport_Model_Import_Source_Csv(__DIR__ . '/_files/products_to_import.csv');
        $this->_model->setParameters(array(
            'behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE,
            'entity' => 'catalog_product'
        ))->setSource($source)->isDataValid();

        $this->_model->importData();

        /** @var $productBeforeImport Mage_Catalog_Model_Product */
        foreach ($productsBeforeImport as $productBeforeImport) {
            /** @var $productAfterImport Mage_Catalog_Model_Product */
            $productAfterImport = Mage::getModel('Mage_Catalog_Model_Product');
            $productAfterImport->load($productBeforeImport->getId());

            $this->assertEquals(
                $productBeforeImport->getVisibility(),
                $productAfterImport->getVisibility()
            );
            unset($productAfterImport);
        }

        unset($productsBeforeImport, $product);
    }

    /**
     * Test if stock item quantity properly saved after import
     *
     * magentoDataFixture Mage/Catalog/_files/multiple_products.php
     */
    public function testSaveStockItemQty()
    {
        $existingProductIds = array(10, 11, 12);
        $stockItems = array();
        foreach ($existingProductIds as $productId) {
            $stockItem = Mage::getModel('Mage_CatalogInventory_Model_Stock_Item');
            $stockItem->loadByProduct($productId);
            $stockItems[$productId] = $stockItem;
        }

        $source = new Mage_ImportExport_Model_Import_Source_Csv(__DIR__ . '/_files/products_to_import.csv');
        $this->_model->setParameters(array(
            'behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE,
            'entity' => 'catalog_product'
        ))->setSource($source)->isDataValid();

        $this->_model->importData();

        /** @var $stockItmBeforeImport Mage_CatalogInventory_Model_Stock_Item */
        foreach ($stockItems as $productId => $stockItmBeforeImport) {

            /** @var $stockItemAfterImport Mage_CatalogInventory_Model_Stock_Item */
            $stockItemAfterImport = Mage::getModel('Mage_CatalogInventory_Model_Stock_Item');
            $stockItemAfterImport->loadByProduct($productId);

            $this->assertEquals(
                $stockItmBeforeImport->getQty(),
                $stockItemAfterImport->getQty()
            );
            unset($stockItemAfterImport);
        }

        unset($stockItems, $stockItem);
    }

    /**
     * Tests adding of custom options with different behaviours
     *
     * @param $behavior
     *
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     * @dataProvider getBehaviorDataProvider
     * @covers Mage_ImportExport_Model_Import_Entity_Product::_saveCustomOptions
     */
    public function testSaveCustomOptionsDuplicate($behavior)
    {
        // import data from CSV file
        $pathToFile = __DIR__ . '/_files/product_with_custom_options.csv';
        $source = new Mage_ImportExport_Model_Import_Source_Csv($pathToFile);
        $this->_model->setSource($source)
            ->setParameters(array('behavior' => $behavior))
            ->isDataValid();
        $this->_model->importData();

        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load(1); // product from fixture
        $options = $product->getProductOptionsCollection();

        $expectedData = $this->_getExpectedOptionsData($pathToFile);
        $expectedData = $this->_mergeWithExistingData($expectedData, $options);
        $actualData = $this->_getActualOptionsData($options);

        // assert of equal type+titles
        $expectedOptions = $expectedData['options']; // we need to save key values
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
                    unset($actualData['options'][$actualId]); // remove value in case of duplicating key values
                    break;
                }
            }
            $this->assertTrue($elementExist, 'Element must exist.');
        }
    }

    /**
     * Test if datetime properly saved after import
     *
     * @magentoDataFixture Mage/Catalog/_files/multiple_products.php
     */
    public function testSaveDatetimeAttribute()
    {
        $existingProductIds = array(10, 11, 12);
        $productsBeforeImport = array();
        foreach ($existingProductIds as $productId) {
            $product = Mage::getObjectManager()->create('Mage_Catalog_Model_Product');
            $product->load($productId);
            $productsBeforeImport[$product->getSku()] = $product;
        }

        $source = new Mage_ImportExport_Model_Import_Source_Csv(
            __DIR__ . '/_files/products_to_import_with_datetime.csv'
        );
        $this->_model->setParameters(array(
            'behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE,
            'entity' => 'catalog_product'
        ))->setSource($source)->isDataValid();

        $this->_model->importData();

        reset($source);
        foreach ($source as $row) {
            /** @var $productAfterImport Mage_Catalog_Model_Product */
            $productBeforeImport = $productsBeforeImport[$row['sku']];

            /** @var $productAfterImport Mage_Catalog_Model_Product */
            $productAfterImport = Mage::getObjectManager()->create('Mage_Catalog_Model_Product');
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
        $expectedOptions = array();  // array of type and title types, key is element ID
        $expectedData = array();     // array of option data
        $expectedValues = array();   // array of option values data
        foreach ($productData['data'] as $data) {
            if (!empty($data['_custom_option_type']) && !empty($data['_custom_option_title'])) {
                $lastOptionKey = $data['_custom_option_type'] . '|' . $data['_custom_option_title'];
                $expectedOptionId++;
                $expectedOptions[$expectedOptionId] = $lastOptionKey;
                $expectedData[$expectedOptionId] = array();
                foreach ($this->_assertOptions as $assertKey => $assertFieldName) {
                    if (array_key_exists($assertFieldName, $data)) {
                        $expectedData[$expectedOptionId][$assertKey] = $data[$assertFieldName];
                    }
                }
            }
            if (!empty($data['_custom_option_row_title']) && empty($data['_custom_option_store'])) {
                $optionData = array();
                foreach ($this->_assertOptionValues as $assertKey) {
                    $valueKey = Mage_ImportExport_Model_Import_Entity_Product_Option::COLUMN_PREFIX
                        . 'row_' . $assertKey;
                    $optionData[$assertKey] = $data[$valueKey];
                }
                $expectedValues[$expectedOptionId][] = $optionData;
            }
        }

        return array(
            'id'      => $expectedOptionId,
            'options' => $expectedOptions,
            'data'    => $expectedData,
            'values'  => $expectedValues,
        );
    }

    /**
     * Updates expected options data array with existing unique options data
     *
     * @param array $expected
     * @param Mage_Catalog_Model_Resource_Product_Option_Collection $options
     * @return array
     */
    protected function _mergeWithExistingData(array $expected,
        Mage_Catalog_Model_Resource_Product_Option_Collection $options
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

        return array(
            'id'      => $expectedOptionId,
            'options' => $expectedOptions,
            'data'    => $expectedData,
            'values'  => $expectedValues,
        );
    }

    /**
     *  Returns actual product data: current id, options, options data and option values
     *
     * @param Mage_Catalog_Model_Resource_Product_Option_Collection $options
     * @return array
     */
    protected function _getActualOptionsData(Mage_Catalog_Model_Resource_Product_Option_Collection $options)
    {
        $actualOptionId = 0;
        $actualOptions = array();  // array of type and title types, key is element ID
        $actualData = array();     // array of option data
        $actualValues = array();   // array of option values data
        /** @var $option Mage_Catalog_Model_Product_Option */
        foreach ($options->getItems() as $option) {
            $lastOptionKey = $option->getType() . '|' . $option->getTitle();
            $actualOptionId++;
            $actualOptions[$actualOptionId] = $lastOptionKey;
            $actualData[$actualOptionId] = $this->_getOptionData($option);
            if ($optionValues = $this->_getOptionValues($option)) {
                $actualValues[$actualOptionId] = $optionValues;
            }
        }
        return array(
            'id'      => $actualOptionId,
            'options' => $actualOptions,
            'data'    => $actualData,
            'values'  => $actualValues,
        );
    }

    /**
     * Retrieve option data
     *
     * @param Mage_Catalog_Model_Product_Option $option
     * @return array
     */
    protected function _getOptionData(Mage_Catalog_Model_Product_Option $option)
    {
        $result = array();
        foreach (array_keys($this->_assertOptions) as $assertKey) {
            $result[$assertKey] = $option->getData($assertKey);
        }
        return $result;
    }

    /**
     * Retrieve option values or false for options which has no values
     *
     * @param Mage_Catalog_Model_Product_Option $option
     * @return array|bool
     */
    protected function _getOptionValues(Mage_Catalog_Model_Product_Option $option)
    {
        $values = $option->getValues();
        if (!empty($values)) {
            $result = array();
            /** @var $value Mage_Catalog_Model_Product_Option_Value */
            foreach ($values as $value) {
                $optionData = array();
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
        return array(
            'Append behavior' => array(
                '$behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_APPEND
            ),
            'Replace behavior' => array(
                '$behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_REPLACE
            )
        );
    }

    /**
     * @magentoDataIsolation enabled
     * @magentoDataFixture mediaImportImageFixture
     */
    public function testSaveMediaImage()
    {
        if (Magento_Test_Bootstrap::getInstance()->getDbVendorName() != 'mysql') {
            $this->markTestIncomplete('bug: MAGETWO-4227');
        }
        $attribute = Mage::getModel('Mage_Catalog_Model_Entity_Attribute');
        $attribute->loadByCode('catalog_product', 'media_gallery');
        $data = implode(',', array(
            // minimum required set of attributes + media images
            'sku', '_attribute_set', '_type', '_product_websites', 'name', 'price',
            'description', 'short_description', 'weight', 'status', 'visibility', 'tax_class_id',
            '_media_attribute_id', '_media_image', '_media_label', '_media_position', '_media_is_disabled'
        )) . "\n";
        $data .= implode(',', array(
            'test_sku', 'Default', Mage_Catalog_Model_Product_Type::DEFAULT_TYPE, 'base', 'Product Name', '9.99',
            'Product description', 'Short desc.', '1',
            Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH, 0,
            $attribute->getId(), 'magento_image.jpg', 'Image Label', '1', '0'
        )) . "\n";
        $data = 'data://text/plain;base64,' . base64_encode($data);
        $fixture = new Mage_ImportExport_Model_Import_Source_Csv($data);

        foreach (new Mage_Catalog_Model_Resource_Product_Collection as $product) {
            $this->fail("Unexpected precondition - product exists: '{$product->getId()}'.");
        }

        $this->_model->setSource($fixture)
            ->setParameters(array('behavior' => Mage_ImportExport_Model_Import::BEHAVIOR_APPEND))
            ->isDataValid();
        $this->_model->importData();

        $resource = new Mage_Catalog_Model_Resource_Product;
        $productId = $resource->getIdBySku('test_sku'); // fixture
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);
        $gallery = $product->getMediaGalleryImages();
        $this->assertInstanceOf('Varien_Data_Collection', $gallery);
        $items = $gallery->getItems();
        $this->assertCount(1, $items);
        $item = array_pop($items);
        $this->assertInstanceOf('Varien_Object', $item);
        $this->assertEquals('/m/a/magento_image.jpg', $item->getFile());
        $this->assertEquals('Image Label', $item->getLabel());
    }

    /**
     * Copy a fixture image into media import directory
     */
    public static function mediaImportImageFixture()
    {
        $dir = Mage::getBaseDir('media') . '/import';
        mkdir($dir);
        copy(__DIR__ . '/../../../../Catalog/_files/magento_image.jpg', "{$dir}/magento_image.jpg");
    }

    /**
     * Cleanup media import and catalog directories
     */
    public static function mediaImportImageFixtureRollback()
    {
        $media = Mage::getBaseDir('media');
        Varien_Io_File::rmdirRecursive("{$media}/import");
        Varien_Io_File::rmdirRecursive("{$media}/catalog");
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
        $data = array(
            'header' => array(),
            'data'   => array()
        );

        $lines = str_getcsv($content, "\n");
        foreach ($lines as $index => $line) {
            if ($index == 0) {
                $data['header'] = str_getcsv($line);
            } else {
                $row = array_combine($data['header'], str_getcsv($line));
                if ($entityId !== null && !empty($row[$entityId])) {
                    $data['data'][$row[$entityId]] = $row;
                } else {
                    $data['data'][] = $row;
                }
            }
        }
        return $data;
    }
}
