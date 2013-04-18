<?php
/**
 * Test Product CRUD operations
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @method Mage_Catalog_Model_Product_Api_Helper_Simple _getHelper()
 */
class Mage_Catalog_Model_Product_Api_SimpleTest extends Mage_Catalog_Model_Product_Api_TestCaseAbstract
{
    /**
     * Default helper for current test suite
     *
     * @var string
     */
    protected $_defaultHelper = 'Mage_Catalog_Model_Product_Api_Helper_Simple';

    /**
     * Test product resource post
     * @magentoDbIsolation enabled
     */
    public function testCreateSimpleRequiredFieldsOnly()
    {
        $productData = require __DIR__ . '/_files/_data/simple_product_data.php';
        $productId = $this->_createProductWithApi($productData);

        $actualProduct = Mage::getModel('Mage_Catalog_Model_Product');
        $actualProduct->load($productId);
        $this->assertNotNull($actualProduct->getId());
        $expectedProduct = Mage::getModel('Mage_Catalog_Model_Product');
        $expectedProduct->setData($productData);

        $this->assertProductEquals($expectedProduct, $actualProduct);
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 2
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     */
    public function testCreateLimitationNotReached()
    {
        $this->testCreateSimpleRequiredFieldsOnly();
    }

    /**
     * @magentoConfigFixture limitations/catalog_product 1
     * @magentoDataFixture Mage/Catalog/_files/product_simple.php
     * @expectedException SoapFault
     * @expectedExceptionMessage Maximum allowed number of products is reached.
     */
    public function testCreateLimitationReached()
    {
        $this->_createProductWithApi(require __DIR__ . '/_files/_data/simple_product_data.php');
    }

    /**
     * Test product resource post with all fields
     *
     * @param array $productData
     * @dataProvider dataProviderTestCreateSimpleAllFieldsValid
     * @magentoDbIsolation enabled
     */
    public function testCreateSimpleAllFieldsValid($productData)
    {
        $productId = $this->_createProductWithApi($productData);

        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);
        $this->assertNotNull($product->getId());
        $skipAttributes = array(
            'news_from_date',
            'news_to_date',
            'custom_design_from',
            'custom_design_to',
            'msrp_enabled',
            'msrp_display_actual_price_type',
            'msrp',
            'meta_title',
            'meta_keyword',
            'meta_description',
            'page_layout',
            'gift_wrapping_available',
            'gift_wrapping_price'
        );
        $skipStockItemAttrs = array('min_qty');

        $this->_getHelper()->checkSimpleAttributesData(
            $product,
            $productData,
            $skipAttributes,
            $skipStockItemAttrs
        );
    }

    /**
     * Data provider for testCreateSimpleAllFieldsValid
     *
     * @magentoDbIsolation enabled
     * @return array
     */
    public function dataProviderTestCreateSimpleAllFieldsValid()
    {
        $productData = require __DIR__ . '/_files/_data/simple_product_all_fields_data.php';
        // Fix for tests, because in current soap version this field has "int" type in WSDL
        // @TODO: fix WSDL in new soap version when implemented
        $productData['stock_data']['notify_stock_qty'] = 2;
        $specialCharsData = require __DIR__ . '/_files/_data/simple_product_special_chars_data.php';

        return array(
            array($specialCharsData),
            array($productData),
        );
    }

    /**
     * Test product resource post using config values in inventory
     *
     * @magentoDbIsolation enabled
     */
    public function testCreateInventoryUseConfigValues()
    {
        $productData = require __DIR__ . '/_files/_data/simple_product_inventory_use_config.php';
        $productId = $this->_createProductWithApi($productData);

        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);
        $this->assertNotNull($product->getId());

        $this->_getHelper()->checkStockItemDataUseDefault($product);
    }

    /**
     * Test product resource post using config values in inventory manage stock field
     *
     * @magentoConfigFixture current_store cataloginventory/item_options/manage_stock 0
     * @magentoDbIsolation enabled
     */
    public function testCreateInventoryManageStockUseConfig()
    {
        $productData = require __DIR__ . '/_files/_data/simple_product_manage_stock_use_config.php';

        $productId = $this->_createProductWithApi($productData);
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);
        $this->assertNotNull($product->getId());

        $stockItem = $product->getStockItem();
        $this->assertNotNull($stockItem);
        $this->assertEquals(0, $stockItem->getManageStock());
    }

    /**
     * Test for set special price for product
     *
     * @magentoDbIsolation enabled
     */
    public function testSetSpecialPrice()
    {
        $productData = require __DIR__ . '/_files/ProductData.php';
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $specialPrice = 1.99;
        $specialFrom = '2011-12-22 00:00:00';
        $specialTo = '2011-12-25 00:00:00';

        $product->setData($productData['create_full_fledged']);
        $product->save();

        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductSetSpecialPrice',
            array(
                'productId' => $product->getSku(),
                'specialPrice' => $specialPrice,
                'fromDate' => $specialFrom,
                'toDate' => $specialTo,
                'store' => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
            )
        );

        $this->assertEquals(true, $result, 'Response is not true casted value');

        // reload product to reflect changes done by API request
        $product->load($product->getId());

        $this->assertEquals($specialPrice, $product->getSpecialPrice(), 'Special price not changed');
        $this->assertEquals($specialFrom, $product->getSpecialFromDate(), 'Special price from not changed');
        $this->assertEquals($specialTo, $product->getSpecialToDate(), 'Special price to not changed');
    }

    /**
     * Test get product info by numeric SKU
     *
     * @magentoDbIsolation enabled
     */
    public function testProductInfoByNumericSku()
    {
        $data = require __DIR__ . '/_files/ProductData.php';

        //generate numeric sku
        $data['create_with_attributes_soapv2']->sku = rand(1000000, 99999999);

        $productId = Magento_Test_Helper_Api::call($this, 'catalogProductCreate', $data['create']);

        $this->assertEquals(
            $productId,
            (int)$productId,
            'Result of a create method is not an integer.'
        );

        //test new product exists in DB
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);
        $this->assertNotNull($product->getId(), 'Tested product not found.');

        $result = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductInfo',
            array(
                'productId' => $data['create']['sku'],
                'store' => 0, //default 0
                'attributes' => '',
                'identifierType' => 'sku',
            )
        );

        $this->assertInternalType('array', $result, 'Response is not an array');
        $this->assertArrayHasKey('product_id', $result, 'Response array does not have "product_id" key');
        $this->assertEquals($productId, $result['product_id'], 'Product cannot be load by SKU which is numeric');
    }

    /**
     * Test product CRUD
     *
     * @magentoDbIsolation enabled
     */
    public function testProductCrud()
    {
        $data = require __DIR__ . '/_files/ProductData.php';

        // create product for test
        $productId = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCreate',
            $data['create_with_attributes_soapv2']
        );

        // test new product id returned
        $this->assertGreaterThan(0, $productId);

        //test new product exists in DB
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);
        $this->assertNotNull($product->getId());

        //update product
        $data['create_with_attributes_soapv2'] = array('productId' => $productId) + $data['update'];

        $isOk = Magento_Test_Helper_Api::call($this, 'catalogProductUpdate', $data['create_with_attributes_soapv2']);

        //test call response is true
        $this->assertTrue($isOk, 'Call returned false');

        //test product exists in DB after update and product data changed
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);
        $this->assertNotNull($product->getId());
        $this->assertEquals($data['update']['productData']->name, $product->getName());

        //delete product
        $isOk = Magento_Test_Helper_Api::call($this, 'catalogProductDelete', array('productId' => $productId));

        //test call response is true
        $this->assertTrue((bool)$isOk, 'Call returned false'); //in SOAP v2 it's integer:1

        //test product not exists in DB after delete
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);
        $this->assertNull($product->getId());
    }

    /**
     * Test product CRUD with custom options
     *
     * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/ProductWithOptionCrud.php
     * @magentoDbIsolation enabled
     */
    public function testProductWithOptionsCrud()
    {
        $this->markTestIncomplete('TODO: Fix test');
        $optionValueApi = Mage::registry('optionValueApi');
        $optionValueInstaller = Mage::registry('optionValueInstaller');
        $data = require __DIR__ . '/_files/ProductData.php';

        $singleData = & $data['create_with_attributes_soapv2']['productData']->additional_attributes->singleData;
        $singleData[1]->value = $optionValueApi;
        $singleData[3]->value = $optionValueInstaller;
        $attributes = $data['create_with_attributes_soapv2']['productData']->additional_attributes;

        // create product for test
        $productId = Magento_Test_Helper_Api::call(
            $this,
            'catalogProductCreate',
            $data['create_with_attributes_soapv2']
        );

        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);

        // test new product id returned
        $this->assertGreaterThan(0, $productId);

        //test new product attributes
        $this->assertEquals($attributes->singleData[0]->value, $product->getData('a_text_api'));
        $this->assertEquals($attributes->singleData[1]->value, $product->getData('a_select_api'));
        $this->assertEquals($attributes->singleData[2]->value, $product->getData('a_text_ins'));
        $this->assertEquals($attributes->singleData[3]->value, $product->getData('a_select_ins'));

    }

    /**
     * Test create product with invalid attribute set
     *
     * @magentoDbIsolation enabled
     */
    public function testProductCreateWithInvalidAttributeSet()
    {
        $productData = require __DIR__ . '/_files/ProductData.php';
        $productData = $productData['create_full']['soap'];
        $productData['set'] = 9999;

        try {
            Magento_Test_Helper_Api::call($this, 'catalogProductCreate', $productData);
        } catch (Exception $e) {
            $this->assertEquals('Product attribute set does not exist.', $e->getMessage(), 'Invalid exception message');
        }

        // find not product (category) attribute set identifier to try other error message
        /** @var $entity Mage_Eav_Model_Entity_Type */
        $entity = Mage::getModel('Mage_Eav_Model_Entity_Type');
        $entityTypeId = $entity->loadByCode('catalog_category')->getId();

        /** @var $attrSet Mage_Eav_Model_Entity_Attribute_Set */
        $attrSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set');

        /** @var $attrSetCollection Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection */
        $attrSetCollection = $attrSet->getCollection();
        $categoryAtrrSets = $attrSetCollection->setEntityTypeFilter($entityTypeId)->toOptionHash();
        $categoryAttrSetId = key($categoryAtrrSets);

        $productData['set'] = $categoryAttrSetId;

        try {
            Magento_Test_Helper_Api::call($this, 'catalogProductCreate', $productData);
        } catch (Exception $e) {
            $this->assertEquals(
                'Product attribute set does not belong to catalog product entity type.',
                $e->getMessage(),
                'Invalid exception message'
            );
        }
    }

    /**
     * Test product attributes update in custom store view
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Mage/Catalog/Model/Product/Api/_files/store_on_new_website.php
     */
    public function testProductUpdateCustomStore()
    {
        /** @var Mage_Core_Model_Store $store */
        $store = Mage::registry('store_on_new_website');

        $data = require __DIR__ . '/_files/ProductData.php';
        // create product for test
        $productId = Magento_Test_Helper_Api::call($this, 'catalogProductCreate', $data['create_full']['soap']);
        $this->assertGreaterThan(0, $productId, 'Product was not created');

        // update product on test store
        $data['update_custom_store'] = array('productId' => $productId) + $data['update_custom_store'];
        $data['update_custom_store']['store'] = $store->getCode();
        $isOk = Magento_Test_Helper_Api::call($this, 'catalogProductUpdate', $data['update_custom_store']);
        $this->assertTrue($isOk, 'Can not update product on test store');

        // Load product in test store
        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->setStoreId($store->getId())->load($productId);
        $this->assertNotNull($product->getId());
        $this->assertEquals(
            $data['update_custom_store']['productData']->name,
            $product->getName(),
            'Product name was not updated'
        );

        // update product attribute in default store
        $data['update_default_store'] = array('productId' => $productId) + $data['update_default_store'];
        $isOk = Magento_Test_Helper_Api::call($this, 'catalogProductUpdate', $data['update_default_store']);
        $this->assertTrue($isOk, 'Can not update product on default store');

        // Load product in default store
        $productDefault = Mage::getModel('Mage_Catalog_Model_Product');
        $productDefault->load($productId);
        $this->assertEquals(
            $data['update_default_store']['productData']->description,
            $productDefault->getDescription(),
            'Description attribute was not updated for default store'
        );
        $this->assertEquals(
            $data['create_full']['soap']['productData']->name,
            $productDefault->getName(),
            'Product name attribute should not have been changed'
        );

        // Load product in test store
        $productTestStore = Mage::getModel('Mage_Catalog_Model_Product');
        $productTestStore->setStoreId($store->getId())->load($productId);
        $this->assertEquals(
            $data['update_default_store']['productData']->description,
            $productTestStore->getDescription(),
            'Description attribute was not updated for test store'
        );
        $this->assertEquals(
            $data['update_custom_store']['productData']->name,
            $productTestStore->getName(),
            'Product name attribute should not have been changed for test store'
        );
    }

    /**
     * Test create product to test default values for media attributes
     *
     * @magentoDbIsolation enabled
     */
    public function testProductCreateForTestMediaAttributesDefaultValue()
    {
        $productData = require __DIR__ . '/_files/ProductData.php';
        $productData = $productData['create'];

        // create product for test
        $productId = Magento_Test_Helper_Api::call($this, 'catalogProductCreate', $productData);

        // test new product id returned
        $this->assertGreaterThan(0, $productId);

        $product = Mage::getModel('Mage_Catalog_Model_Product');
        $product->load($productId);

        $found = false;
        foreach ($product->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $this->assertEquals(
                $product->getData($mediaAttrCode),
                'no_selection',
                'Attribute "' . $mediaAttrCode . '" has no default value'
            );
            $found = true;
        }
        $this->assertTrue($found, 'Media attrributes not found');
    }
}
