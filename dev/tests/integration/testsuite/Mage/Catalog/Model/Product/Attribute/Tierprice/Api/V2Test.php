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
 * Test class for Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2.
 *
 * @group module:Mage_Catalog
 */
class Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2;
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
        $data = $this->_model->prepareTierPrices(
            $product,
            array((object) array('qty' => 3, 'price' => 8, 'website' => 100))
        );
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
            array((object) array('qty' => 3, 'price' => 8))
        );
        $this->assertEquals(
            array(array('website_id' => 0, 'cust_group' => 32000, 'price_qty' => 3, 'price' => 8)),
            $data
        );
    }
}
