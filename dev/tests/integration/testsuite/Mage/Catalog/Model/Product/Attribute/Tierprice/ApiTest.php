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
 * Test class for Mage_Catalog_Model_Product_Attribute_Tierprice_Api
 *
 * @magentoDataFixture Mage/Catalog/_files/product_simple.php
 */
class Mage_Catalog_Model_Product_Attribute_Tierprice_ApiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Attribute_Tierprice_Api
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Attribute_Tierprice_Api;
    }

    public function testInfo()
    {
        $info = $this->_model->info(1);
        $this->assertInternalType('array', $info);
        $this->assertEquals(2, count($info));
        $element = current($info);
        $this->assertArrayHasKey('customer_group_id', $element);
        $this->assertArrayHasKey('website', $element);
        $this->assertArrayHasKey('qty', $element);
        $this->assertArrayHasKey('price', $element);
    }

    public function testUpdate()
    {
        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        $this->_model->update(1, array(array('qty' => 3, 'price' => 8)));
        $info = $this->_model->info(1);
        $this->assertEquals(1, count($info));
    }

    /**
     * @expectedException Mage_Api_Exception
     */
    public function testPrepareTierPricesInvalidData()
    {
        $product = new Mage_Catalog_Model_Product();
        $this->_model->prepareTierPrices($product, array(1));
    }

    public function testPrepareTierPricesInvalidWebsite()
    {
        $product = new Mage_Catalog_Model_Product();
        $data = $this->_model->prepareTierPrices($product, array(array('qty' => 3, 'price' => 8, 'website' => 100)));
        $this->assertEquals(
            array(array('website_id' => 0, 'cust_group' => 32000, 'price_qty' => 3, 'price' => 8)),
            $data
        );
    }

    public function testPrepareTierPrices()
    {
        $product = new Mage_Catalog_Model_Product();

        $this->assertNull($this->_model->prepareTierPrices($product));

        $data = $this->_model->prepareTierPrices($product,
            array(array('qty' => 3, 'price' => 8))
        );
        $this->assertEquals(
            array(array('website_id' => 0, 'cust_group' => 32000, 'price_qty' => 3, 'price' => 8)),
            $data
        );
    }
}
