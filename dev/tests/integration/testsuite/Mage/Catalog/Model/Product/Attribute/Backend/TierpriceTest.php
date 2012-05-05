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
 * Test class for Mage_Catalog_Model_Product_Attribute_Backend_Tierprice.
 *
 * @magentoDataFixture Mage/Catalog/_files/product_simple.php
 */
class Mage_Catalog_Model_Product_Attribute_Backend_TierpriceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Attribute_Backend_Tierprice
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Attribute_Backend_Tierprice;
        $this->_model->setAttribute(
            Mage::getSingleton('Mage_Eav_Model_Config')->getAttribute('catalog_product', 'tier_price')
        );
    }


    public function testValidate()
    {
        $product = new Varien_Object();
        $product->setTierPrice(
            array(
                array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8,),
                array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5,),
            )
        );
        $this->assertTrue($this->_model->validate($product));
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidateDuplicate()
    {
        $product = new Varien_Object();
        $product->setTierPrice(
            array(
                array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8,),
                array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8,),
            )
        );

        $this->_model->validate($product);
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testValidateDuplicateWebsite()
    {
        $product = new Varien_Object();
        $product->setTierPrice(
            array(
                array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8,),
                array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5,),
                array('website_id' => 1, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5,),
            )
        );

        $this->_model->validate($product);
    }

    public function testPreparePriceData()
    {
        $data = array(
            array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 2, 'price' => 8,),
            array('website_id' => 0, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5,),
            array('website_id' => 1, 'cust_group' => 1, 'price_qty' => 5, 'price' => 5,),
        );

        $newData = $this->_model->preparePriceData($data, Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, 1);
        $this->assertEquals(2, count($newData));
        $this->assertArrayHasKey('1-2', $newData);
        $this->assertArrayHasKey('1-5', $newData);
    }

    public function testAfterLoad()
    {
        $product = new Mage_Catalog_Model_Product();
        $product->setId(1);
        $this->_model->afterLoad($product);
        $price = $product->getTierPrice();
        $this->assertNotEmpty($price);
        $this->assertEquals(2, count($price));
    }

    public function testAfterSave()
    {
        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        $product = new Mage_Catalog_Model_Product();
        $product->load(1);
        $product->setOrigData();
        $product->setTierPrice(
            array(
                array('website_id' => 0, 'cust_group' => 32000, 'price_qty' => 2, 'price' => 7, 'delete' => true),
                array('website_id' => 0, 'cust_group' => 32000, 'price_qty' => 5, 'price' => 4,),
                array('website_id' => 0, 'cust_group' => 32000, 'price_qty' => 10,'price' => 3,),
                array('website_id' => 0, 'cust_group' => 32000, 'price_qty' => 20,'price' => 2,),
            )
        );

        $this->_model->afterSave($product);

        $product = new Mage_Catalog_Model_Product();
        $product->setId(1);
        $this->_model->afterLoad($product);
        $this->assertEquals(3, count($product->getTierPrice()));
    }

    /**
     * @depends testAfterSave
     */
    public function testAfterSaveEmpty()
    {
        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        $product = new Mage_Catalog_Model_Product();
        $product->load(1);
        $product->setOrigData();
        $product->setTierPrice(array());
        $this->_model->afterSave($product);

        $product = new Mage_Catalog_Model_Product();
        $product->setId(1);
        $this->_model->afterLoad($product);
        $this->assertEmpty($product->getTierPrice());
    }

}
