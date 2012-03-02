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
 * Test class for Mage_Catalog_Model_Product_Attribute_Media_Api.
 *
 * @group module:Mage_Catalog
 * @magentoDataFixture Mage/Catalog/_files/product_simple.php
 * @magentoDataFixture productMediaFixture
 */
class Mage_Catalog_Model_Product_Attribute_Media_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Attribute_Media_Api
     */
    protected $_model;

    /**
     * @var string
     */
    protected static $_fixtureDir;

    /**
     * @var string
     */
    protected static $_mediaTmpDir;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Attribute_Media_Api;
    }

    public static function setUpBeforeClass()
    {
        self::$_fixtureDir = realpath(__DIR__ . '/../../../../_files');
        self::$_mediaTmpDir = Mage::getSingleton('Mage_Catalog_Model_Product_Media_Config')->getBaseTmpMediaPath();
        mkdir(self::$_mediaTmpDir . "/m/a", 0777, true);
        copy(self::$_fixtureDir . '/magento_image.jpg', self::$_mediaTmpDir . '/m/a/magento_image.jpg');
    }

    public static function tearDownAfterClass()
    {
        Varien_Io_File::rmdirRecursive(self::$_mediaTmpDir);
        $config = Mage::getSingleton('Mage_Catalog_Model_Product_Media_Config');
        Varien_Io_File::rmdirRecursive($config->getBaseMediaPath());
    }

    public static function productMediaFixture()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->load(1);
        $product->setTierPrice(array());
        $product->setData('media_gallery', array('images' => array(array('file' => '/m/a/magento_image.jpg',),)));
        $product->save();
    }

    /**
     * @covers Mage_Catalog_Model_Product_Attribute_Media_Api::items
     * @covers Mage_Catalog_Model_Product_Attribute_Media_Api::info
     */
    public function testItemsAndInfo()
    {
        $items = $this->_model->items(1);
        $this->assertNotEmpty($items);
        $this->assertEquals(1, count($items));
        $item = current($items);
        $this->assertArrayHasKey('file', $item);
        $this->assertArrayHasKey('label', $item);;
        $this->assertArrayHasKey('url', $item);

        $info = $this->_model->info(1, $item['file']);
        $this->assertArrayHasKey('file', $info);
        $this->assertArrayHasKey('label', $info);;
        $this->assertArrayHasKey('url', $info);
        return $item['file'];
    }

    /**
     * @depends testItemsAndInfo
     */
    public function testCreate()
    {
        $data = array(
            'file' => array(
                'mime'      => 'image/jpeg',
                'content'   => base64_encode(file_get_contents(self::$_fixtureDir.'/magento_small_image.jpg'))
            )
        );
        $this->_model->create(1, $data);
        $items = $this->_model->items(1);
        $this->assertEquals(2, count($items));
    }

    public function createFaultDataProvider()
    {
        return array(
            array('floor' => 'ceiling'),
            array('file' => array('mime' => 'test')),
            array('file' => array('mime' => 'image/jpeg', 'content' => 'not valid'))
        );
    }

    /**
     * @dataProvider createFaultDataProvider
     * @expectedException Mage_Api_Exception
     */
    public function testCreateFault($data)
    {
        $this->_model->create(1, $data);
    }

    /**
     * @depends testItemsAndInfo
     */
    public function testUpdate($file)
    {
        $data = array(
            'file' => array(
                'mime'      => 'image/jpeg',
                'content'   => base64_encode(file_get_contents(self::$_fixtureDir.'/magento_small_image.jpg'))
            )
        );
        $this->assertTrue($this->_model->update(1, $file, $data));
    }

    /**
     * @depends testItemsAndInfo
     * @expectedException Mage_Api_Exception
     */
    public function testRemove($file)
    {
        $this->assertTrue($this->_model->remove(1, $file));
        $this->_model->info(1, $file);
    }

    public function testTypes()
    {
        $types = $this->_model->types(4);
        $this->assertNotEmpty($types);
        $this->assertInternalType('array', $types);
        $type = current($types);
        $this->assertArrayHasKey('code', $type);
        $this->assertArrayHasKey('scope', $type);
    }
}
