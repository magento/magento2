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
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests product model:
 * - general behaviour is tested (external interaction and pricing is not tested there)
 *
 * @group module:Mage_Catalog
 * @see Mage_Catalog_Model_ProductExternalTest
 * @see Mage_Catalog_Model_ProductPriceTest
 * @magentoDataFixture Mage/Catalog/_files/categories.php
 */
class Mage_Catalog_Model_ProductGettersTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product;
    }

    public function testGetResourceCollection()
    {
        $collection = $this->_model->getResourceCollection();
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Product_Collection', $collection);
        $this->assertEquals($this->_model->getStoreId(), $collection->getStoreId());
    }

    public function testGetUrlModel()
    {
        $model = $this->_model->getUrlModel();
        $this->assertInstanceOf('Mage_Catalog_Model_Product_Url', $model);
        $this->assertSame($model, $this->_model->getUrlModel());
    }

    public function testGetName()
    {
        $this->assertEmpty($this->_model->getName());
        $this->_model->setName('test');
        $this->assertEquals('test', $this->_model->getName());
    }

    public function testGetTypeId()
    {
        $this->assertEmpty($this->_model->getTypeId());
        $this->_model->setTypeId('simple');
        $this->assertEquals('simple', $this->_model->getTypeId());
    }

    public function testGetStatus()
    {
        $this->assertEquals(Mage_Catalog_Model_Product_Status::STATUS_ENABLED, $this->_model->getStatus());
        $this->_model->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
        $this->assertEquals(Mage_Catalog_Model_Product_Status::STATUS_DISABLED, $this->_model->getStatus());
    }

    public function testGetSetTypeInstance()
    {
        // model getter
        $typeInstance = $this->_model->getTypeInstance();
        $this->assertInstanceOf('Mage_Catalog_Model_Product_Type_Abstract', $typeInstance);
        $this->assertSame($typeInstance, $this->_model->getTypeInstance());

        // singleton
        $otherProduct = new Mage_Catalog_Model_Product;
        $this->assertSame($typeInstance, $otherProduct->getTypeInstance());

        // model setter
        $simpleTypeInstance = new Mage_Catalog_Model_Product_Type_Simple;
        $this->_model->setTypeInstance($simpleTypeInstance);
        $this->assertSame($simpleTypeInstance, $this->_model->getTypeInstance());
    }

    public function testGetIdBySku()
    {
        $this->assertEquals(1, $this->_model->getIdBySku('simple')); // fixture
    }

    public function testGetAttributes()
    {
        // fixture required
        $this->_model->load(1);
        $attributes = $this->_model->getAttributes();
        $this->assertArrayHasKey('name', $attributes);
        $this->assertArrayHasKey('sku', $attributes);
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Eav_Attribute', $attributes['sku']);
    }

    /**
     * @covers Mage_Catalog_Model_Product::getCalculatedFinalPrice
     * @covers Mage_Catalog_Model_Product::getMinimalPrice
     * @covers Mage_Catalog_Model_Product::getSpecialPrice
     * @covers Mage_Catalog_Model_Product::getSpecialFromDate
     * @covers Mage_Catalog_Model_Product::getSpecialToDate
     * @covers Mage_Catalog_Model_Product::getRequestPath
     * @covers Mage_Catalog_Model_Product::getGiftMessageAvailable
     * @covers Mage_Catalog_Model_Product::getRatingSummary
     * @dataProvider getObsoleteGettersDataProvider
     * @param string $key
     * @param string $method
     */
    public function testGetObsoleteGetters($key, $method)
    {
        $value = uniqid();
        $this->assertEmpty($this->_model->$method());
        $this->_model->setData($key, $value);
        $this->assertEquals($value, $this->_model->$method());
    }

    public function getObsoleteGettersDataProvider()
    {
        return array(
            array('calculated_final_price', 'getCalculatedFinalPrice'),
            array('minimal_price', 'getMinimalPrice'),
            array('special_price', 'getSpecialPrice'),
            array('special_from_date', 'getSpecialFromDate'),
            array('special_to_date', 'getSpecialToDate'),
            array('request_path', 'getRequestPath'),
            array('gift_message_available', 'getGiftMessageAvailable'),
            array('rating_summary', 'getRatingSummary'),
        );
    }

    public function testGetMediaAttributes()
    {
        $model = new Mage_Catalog_Model_Product(array('media_attributes' => 'test'));
        $this->assertEquals('test', $model->getMediaAttributes());

        $attributes = $this->_model->getMediaAttributes();
        $this->assertArrayHasKey('image', $attributes);
        $this->assertArrayHasKey('small_image', $attributes);
        $this->assertArrayHasKey('thumbnail', $attributes);
        $this->assertInstanceOf('Mage_Catalog_Model_Resource_Eav_Attribute', $attributes['image']);
    }

    public function testGetMediaGalleryImages()
    {
        $model = new Mage_Catalog_Model_Product;
        $this->assertEmpty($model->getMediaGalleryImages());

        $this->_model->setMediaGallery(array('images' => array(array('file' => 'magento_image.jpg'))));
        $images = $this->_model->getMediaGalleryImages();
        $this->assertInstanceOf('Varien_Data_Collection', $images);
        foreach ($images as $image) {
            $this->assertInstanceOf('Varien_Object', $image);
            $image = $image->getData();
            $this->assertArrayHasKey('file', $image);
            $this->assertArrayHasKey('url', $image);
            $this->assertArrayHasKey('id', $image);
            $this->assertArrayHasKey('path', $image);
            $this->assertStringEndsWith('magento_image.jpg', $image['file']);
            $this->assertStringEndsWith('magento_image.jpg', $image['url']);
            $this->assertStringEndsWith('magento_image.jpg', $image['path']);
        }
    }

    public function testGetMediaConfig()
    {
        $model = $this->_model->getMediaConfig();
        $this->assertInstanceOf('Mage_Catalog_Model_Product_Media_Config', $model);
        $this->assertSame($model, $this->_model->getMediaConfig());
    }

    public function testGetAttributeText()
    {
        $this->assertNull($this->_model->getAttributeText('status'));
        $this->_model->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->assertEquals('Enabled', $this->_model->getAttributeText('status'));
    }

    public function testGetCustomDesignDate()
    {
        $this->assertEquals(array('from' => null, 'to' => null), $this->_model->getCustomDesignDate());
        $this->_model->setCustomDesignFrom(1)->setCustomDesignTo(2);
        $this->assertEquals(array('from' => 1, 'to' => 2), $this->_model->getCustomDesignDate());
    }

    /**
     * @see Mage_Catalog_Model_Product_Type_SimpleTest
     */
    public function testGetSku()
    {
        $this->assertEmpty($this->_model->getSku());
        $this->_model->setSku('sku');
        $this->assertEquals('sku', $this->_model->getSku());
    }

    public function testGetWeight()
    {
        $this->assertEmpty($this->_model->getWeight());
        $this->_model->setWeight(10.22);
        $this->assertEquals(10.22, $this->_model->getWeight());
    }

    public function testGetOptionInstance()
    {
        $model = $this->_model->getOptionInstance();
        $this->assertInstanceOf('Mage_Catalog_Model_Product_Option', $model);
        $this->assertSame($model, $this->_model->getOptionInstance());
    }

    public function testGetProductOptionsCollection()
    {
        $this->assertInstanceOf(
            'Mage_Catalog_Model_Resource_Product_Option_Collection', $this->_model->getProductOptionsCollection()
        );
    }

    public function testGetDefaultAttributeSetId()
    {
        $setId = $this->_model->getDefaultAttributeSetId();
        $this->assertNotEmpty($setId);
        $this->assertRegExp('/^[0-9]+$/', $setId);
    }

    public function testGetReservedAttributes()
    {
        $result = $this->_model->getReservedAttributes();
        $this->assertInternalType('array', $result);
        $this->assertContains('position', $result);
        $this->assertContains('reserved_attributes', $result);
        $this->assertContains('is_virtual', $result);
        // and 84 more...

        $this->assertNotContains('type_id', $result);
        $this->assertNotContains('calculated_final_price', $result);
        $this->assertNotContains('request_path', $result);
        $this->assertNotContains('rating_summary', $result);
    }

    public function testGetCacheIdTags()
    {
        $this->assertFalse($this->_model->getCacheIdTags());

        $this->_model->load(1); // fixture
        $this->assertEquals(array('catalog_product_1'), $this->_model->getCacheIdTags());
    }

    public function testGetPreconfiguredValues()
    {
        $this->assertInstanceOf('Varien_Object', $this->_model->getPreconfiguredValues());
        $this->_model->setPreconfiguredValues('test');
        $this->assertEquals('test', $this->_model->getPreconfiguredValues());
    }

    public static function tearDownAfterClass()
    {
        $mediaDir = Mage::getSingleton('Mage_Catalog_Model_Product_Media_Config')->getBaseMediaPath();
        Varien_Io_File::rmdirRecursive($mediaDir);
    }
}
