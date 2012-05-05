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

class Mage_Catalog_Model_Product_Type_Configurable_AttributeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Catalog_Model_Product_Type_Configurable_Attribute
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Catalog_Model_Product_Type_Configurable_Attribute;
    }

    public function testAddPrice()
    {
        $this->assertEmpty($this->_model->getPrices());
        $this->_model->addPrice(100);
        $this->assertEquals(array(100), $this->_model->getPrices());
    }

    public function testGetLabel()
    {
        $this->assertEmpty($this->_model->getLabel());
        $this->_model->setProductAttribute(new Varien_Object(array('store_label' => 'Store Label')));
        $this->assertEquals('Store Label', $this->_model->getLabel());

        $this->_model->setUseDefault(1)->setProductAttribute(new Varien_Object(array('store_label' => 'Other Label')));
        $this->assertEquals('Other Label', $this->_model->getLabel());
    }
}
