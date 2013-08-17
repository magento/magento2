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
 * @package     Magento_Tax
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * @magentoDataFixture Mage/Tax/_files/tax_classes.php
 */
class Mage_Tax_Model_Calculation_RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that first value in multiselect applied as default if there is no default value in config
     *
     * @magentoConfigFixture default_store tax/classes/default_customer_tax_class 0
     */
    public function testGetCustomerTaxClassWithDefaultFirstValue()
    {
        $model = new Mage_Tax_Model_Calculation_Rule(
            Mage::getModel('Mage_Core_Model_Context'),
            Mage::helper('Mage_Tax_Helper_Data'),
            $this->_getTaxClassMock(
                'getCustomerClasses',
                Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER
            ),
            null,
            null,
            array()
        );
        $this->assertEquals(1, $model->getCustomerTaxClassWithDefault());
    }

    /**
     * Test that default value for multiselect is retrieve from config
     *
     * @magentoConfigFixture default_store tax/classes/default_customer_tax_class 2
     */
    public function testGetCustomerTaxClassWithDefaultFromConfig()
    {
        $model = new Mage_Tax_Model_Calculation_Rule(
            Mage::getModel('Mage_Core_Model_Context'),
            Mage::helper('Mage_Tax_Helper_Data'),
            $this->_getTaxClassMock(
                'getCustomerClasses',
                Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER
            ),
            null,
            null,
            array()
        );
        $this->assertEquals(2, $model->getCustomerTaxClassWithDefault());
    }

    /**
     * Test that first value in multiselect applied as default if there is no default value in config
     *
     * @magentoConfigFixture default_store tax/classes/default_product_tax_class 0
     */
    public function testGetProductTaxClassWithDefaultFirstValue()
    {
        $model = new Mage_Tax_Model_Calculation_Rule(
            Mage::getModel('Mage_Core_Model_Context'),
            Mage::helper('Mage_Tax_Helper_Data'),
            $this->_getTaxClassMock(
                'getProductClasses',
                 Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT
            ),
            null,
            null,
            array()
        );
        $this->assertEquals(1, $model->getProductTaxClassWithDefault());
    }

    /**
     * Test that default value for multiselect is retrieve from config
     *
     * @magentoConfigFixture default_store tax/classes/default_product_tax_class 2
     */
    public function testGetProductTaxClassWithDefaultFromConfig()
    {
        $model = new Mage_Tax_Model_Calculation_Rule(
            Mage::getModel('Mage_Core_Model_Context'),
            Mage::helper('Mage_Tax_Helper_Data'),
            $this->_getTaxClassMock(
                'getProductClasses',
                 Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT
            ),
            null,
            null,
            array()
        );
        $this->assertEquals(2, $model->getProductTaxClassWithDefault());
    }

    /**
     * Test get all options
     *
     * @dataProvider getAllOptionsProvider
     */
    public function testGetAllOptions($classFilter, $expected)
    {
        $model = new Mage_Tax_Model_Calculation_Rule(
            Mage::getModel('Mage_Core_Model_Context'),
            Mage::helper('Mage_Tax_Helper_Data'),
            Mage::getModel('Mage_Tax_Model_Class'),
            null,
            null,
            array()
        );
        $classes = $model->getAllOptionsForClass($classFilter);
        $this->assertCount(count($expected), $classes);
        $count = 0;
        foreach ($classes as $class) {
            $this->assertEquals($expected[$count], $class['label']);
            $count++;
        }
    }

    /**
     * Data provider for testGetAllOptions() method
     */
    public function getAllOptionsProvider()
    {
        return array(
            array(
                Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER,
                array('Retail Customer', 'CustomerTaxClass1', 'CustomerTaxClass2')
            ),
            array(
                Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT,
                array('Taxable Goods', 'ProductTaxClass1', 'ProductTaxClass2')
            ),
        );
    }

    /**
     * Get Product|Customer tax class mock
     *
     * @param string $callback
     * @param string $filter
     * @return Mage_Tax_Model_Class
     */
    protected function _getTaxClassMock($callback, $filter)
    {
        $collection = $this->getMock(
            'Mage_Tax_Model_Resource_Class_Collection',
            array('setClassTypeFilter', 'toOptionArray'),
            array(), '', false
        );
        $collection->expects($this->any())
            ->method('setClassTypeFilter')
            ->with($filter)
            ->will($this->returnValue($collection));

        $collection->expects($this->any())
            ->method('toOptionArray')
            ->will($this->returnCallback(array($this, $callback)));

        $mock = $this->getMock(
            'Mage_Tax_Model_Class',
            array('getCollection'),
            array(
                Mage::getModel('Mage_Core_Model_Context'),
                Mage::getModel('Mage_Tax_Model_Class_Factory'),
                Mage::helper('Mage_Tax_Helper_Data')
            ),
            '',
            true
        );
        $mock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($collection));

        return $mock;
    }

    /**
     * Prepare Customer Tax Classes
     * @return array
     */
    public function getCustomerClasses()
    {
        return array(
            array(
                'value' => '1',
                'name' => 'Retail Customer'
            ),
            array(
                'value' => '2',
                'name' => 'Guest'
            )
        );
    }

    /**
     * Prepare Product Tax classes
     * @return array
     */
    public function getProductClasses()
    {
        return array(
            array(
                'value' => '1',
                'name' => 'Taxable Goods'
            ),
            array(
                'value' => '2',
                'name' => 'Shipping'
            )
        );
    }
}
