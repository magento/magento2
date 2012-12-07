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
 * @package     Mage_Eav
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Eav_Model_Entity_Attribute_Backend_ArrayTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Eav_Model_Entity_Attribute_Backend_Array
     */
    protected $_model;

    /**
     * @var Mage_Eav_Model_Entity_Attribute
     */
    protected $_attribute;

    protected function setUp()
    {
        $this->_attribute = $this->getMock(
            'Mage_Eav_Model_Entity_Attribute', array('getAttributeCode'), array(), '', false
        );
        $this->_model = new Mage_Eav_Model_Entity_Attribute_Backend_Array();
        $this->_model->setAttribute($this->_attribute);
    }

    /**
     * @dataProvider attributeValueDataProvider
     */
    public function testValidate($data)
    {
        $this->_attribute->expects($this->atLeastOnce())->method('getAttributeCode')->will($this->returnValue('code'));
        $product = new Varien_Object(array('code' => $data));
        $this->_model->validate($product);
        $this->assertEquals('1,2,3', $product->getCode());
    }

    public static function attributeValueDataProvider()
    {
        return array(
            array(
                array(1, 2, 3)
            ),
            array(
                '1,2,3'
            )
        );
    }
}
