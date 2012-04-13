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
 * Test class for Mage_Catalog_Model_Product_Attribute_Backend_Media.
 *
 * @group module:Mage_Catalog
 * @magentoDataFixture Mage/Catalog/_files/product_simple.php
 */
class Mage_Catalog_Model_Product_Attribute_Backend_MediaTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Attribute_Backend_Media
     */
    protected $_model;

    /**
     * @var string
     */
    protected static $_mediaTmpDir;

    /**
     * @var string
     */
    protected static $_mediaDir;

    public static function setUpBeforeClass()
    {
        self::$_mediaTmpDir = Mage::getSingleton('Mage_Catalog_Model_Product_Media_Config')->getBaseTmpMediaPath();
        $fixtureDir        = realpath(dirname(__FILE__).'/../../../../_files');
        self::$_mediaDir           = Mage::getSingleton('Mage_Catalog_Model_Product_Media_Config')->getBaseMediaPath();

        mkdir(self::$_mediaTmpDir, 0777, true);
        mkdir(self::$_mediaDir, 0777, true);

        copy($fixtureDir . "/magento_image.jpg", self::$_mediaTmpDir . "/magento_image.jpg");
        copy($fixtureDir . "/magento_image.jpg", self::$_mediaDir . "/magento_image.jpg");
        copy($fixtureDir . "/magento_small_image.jpg", self::$_mediaTmpDir . "/magento_small_image.jpg");
    }

    public static function tearDownAfterClass()
    {
        Varien_Io_File::rmdirRecursive(self::$_mediaTmpDir);
        Varien_Io_File::rmdirRecursive(self::$_mediaDir);
    }

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Attribute_Backend_Media();
        $this->_model->setAttribute(
            Mage::getSingleton('Mage_Eav_Model_Config')->getAttribute('catalog_product', 'media_gallery')
        );
    }

    public function testAfterLoad()
    {
        $product = new Mage_Catalog_Model_Product();
        $this->_model->afterLoad($product);
        $data = $product->getData();
        $this->assertArrayHasKey('media_gallery', $data);
        $this->assertArrayHasKey('images', $data['media_gallery']);
        $this->assertArrayHasKey('values', $data['media_gallery']);
    }

    public function testValidate()
    {
        $product = new Mage_Catalog_Model_Product();
        $this->assertTrue($this->_model->validate($product));
        $this->_model->getAttribute()->setIsRequired(true);
        try {
            $this->assertFalse($this->_model->validate($product));
            $this->_model->getAttribute()->setIsRequired(false);
        } catch (Exception $e) {
            $this->_model->getAttribute()->setIsRequired(false);
            throw $e;
        }
    }

    /**
     * @covers Mage_Catalog_Model_Product_Attribute_Backend_Media::beforeSave
     * @covers Mage_Catalog_Model_Product_Attribute_Backend_Media::getRenamedImage
     */
    public function testBeforeSave()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setData('media_gallery', array('images' => array(
            'image'   => array('file' => 'magento_image.jpg'),
        )));

        $this->_model->beforeSave($product);
        $this->assertStringStartsWith('./magento_image', $product->getData('media_gallery/images/image/new_file'));

        $product->setIsDuplicate(true);
        $product->setData('media_gallery', array('images' => array(
            'image'     => array(
                'value_id'  => '100',
                'file'      => 'magento_image.jpg'
            )
        )));
        $this->_model->beforeSave($product);
        $this->assertStringStartsWith('./magento_image', $product->getData('media_gallery/duplicate/100'));

        /* affect of beforeSave */
        $this->assertNotEquals('magento_image.jpg', $this->_model->getRenamedImage('magento_image.jpg'));
        $this->assertEquals('test.jpg', $this->_model->getRenamedImage('test.jpg'));
    }

    public function testAfterSaveAndAfterLoad()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setId(1);
        $product->setData('media_gallery', array('images' => array(
            'image'   => array('file' => 'magento_image.jpg'),
        )));
        $this->_model->afterSave($product);

        $this->assertEmpty($product->getData('media_gallery/images/0/value_id'));
        $this->_model->afterLoad($product);
        $this->assertNotEmpty($product->getData('media_gallery/images/0/value_id'));
    }

    public function testAddImage()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setId(1);
        $file = $this->_model->addImage($product, self::$_mediaTmpDir . '/magento_small_image.jpg');
        $this->assertStringMatchesFormat('/m/a/magento_small_image%sjpg', $file);
    }

    public function testUpdateImage()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setData('media_gallery', array('images' => array(
            'image'   => array('file' => 'magento_image.jpg'),
        )));
        $this->_model->updateImage($product, 'magento_image.jpg', array('label' => 'test label'));
        $this->assertEquals('test label', $product->getData('media_gallery/images/image/label'));
    }

    public function testRemoveImage()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setData('media_gallery', array('images' => array(
            'image'   => array('file' => 'magento_image.jpg'),
        )));
        $this->_model->removeImage($product, 'magento_image.jpg');
        $this->assertEquals('1', $product->getData('media_gallery/images/image/removed'));
    }

    public function testGetImage()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setData('media_gallery', array('images' => array(
            'image'   => array('file' => 'magento_image.jpg'),
        )));

        $this->assertEquals(
            array('file' => 'magento_image.jpg'),
            $this->_model->getImage($product, 'magento_image.jpg')
        );
    }

    public function testClearMediaAttribute()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setData(array(
            'test_media1' => 'test1',
            'test_media2' => 'test2',
            'test_media3' => 'test3',
        ));
        $product->setMediaAttributes(array('test_media1', 'test_media2', 'test_media3'));

        $this->assertNotEmpty($product->getData('test_media1'));
        $this->_model->clearMediaAttribute($product, 'test_media1');
        $this->assertNull($product->getData('test_media1'));

        $this->assertNotEmpty($product->getData('test_media2'));
        $this->assertNotEmpty($product->getData('test_media3'));
        $this->_model->clearMediaAttribute($product, array('test_media2', 'test_media3'));
        $this->assertNull($product->getData('test_media2'));
        $this->assertNull($product->getData('test_media3'));
    }

    public function testSetMediaAttribute()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setMediaAttributes(array('test_media1', 'test_media2', 'test_media3'));
        $this->_model->setMediaAttribute($product, 'test_media1', 'test1');
        $this->assertEquals('test1', $product->getData('test_media1'));

        $this->_model->setMediaAttribute($product, array('test_media2', 'test_media3'), 'test');
        $this->assertEquals('test', $product->getData('test_media2'));
        $this->assertEquals('test', $product->getData('test_media3'));
    }
}
