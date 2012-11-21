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
 * @package     Mage_Tax
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Tax_Model_Resource_Calculation_Rule_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test setClassTypeFilter with correct Class Type
     *
     * @param $classType
     * @param $elementId
     * @param $expected
     *
     * @dataProvider setClassTypeFilterDataProvider
     */
    public function testSetClassTypeFilter($classType, $elementId, $expected)
    {
        $collection = new Mage_Tax_Model_Resource_Calculation_Rule_Collection();
        $collection->setClassTypeFilter($classType, $elementId);
        $this->assertRegExp($expected, (string)$collection->getSelect());
    }

    public function setClassTypeFilterDataProvider()
    {
        return array(
            array(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT, 1, '/cd\.product_tax_class_id = [\S]{0,1}1[\S]{0,1}/'),
            array(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER, 1, '/cd\.customer_tax_class_id = [\S]{0,1}1[\S]{0,1}/')
        );
    }

    /**
     * Test setClassTypeFilter with wrong Class Type
     *
     * @expectedException Mage_Core_Exception
     */
    public function testSetClassTypeFilterWithWrongType()
    {
        $collection = new Mage_Tax_Model_Resource_Calculation_Rule_Collection();
        $collection->setClassTypeFilter('WrongType', 1);
    }
}
