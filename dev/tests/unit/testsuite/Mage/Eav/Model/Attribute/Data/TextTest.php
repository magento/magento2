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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Eav_Model_Attribute_Data_TextTest extends PHPUnit_Framework_TestCase
{

    /**
     * Text Model to be tested
     * @var Mage_Eav_Model_Attribute_Data_Text|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;
    /**
     * @var $_attribute Mage_Eav_Model_Entity_Attribute_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attribute;
    protected function setUp()
    {
        $this->_model = $this->getMock('Mage_Eav_Model_Attribute_Data_Text', array('getAttribute'), array(), '', false);
        $attributeData = array(
            'store_label' => 'Test',
            'attribute_code' => 'test',
            'is_required' => 1,
            'validate_rules' => array(
                'min_text_length' => 0,
                'max_text_length' => 0,
                'input_validation' => 0
            )
        );

        /** @var $model Mage_Core_Model_Abstract */
        $attribute = $this->getMock('Mage_Core_Model_Abstract', null, array($attributeData));
        //$this->_model->_attribute = $attribute;
        $this->_attribute = $attribute;
        $this->_model->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($this->_attribute));
        $helper = $this->getMockBuilder('Mage_Core_Helper_String')
            ->setMethods(array('__'))
            ->disableOriginalConstructor()
            ->getMock();
        $helper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));
        Mage::register('_helper/Mage_Eav_Helper_Data', $helper);
        Mage::register('_helper/Mage_Core_Helper_String', $helper);


    }

    protected function tearDown()
    {
        $this->_model = null;
        Mage::unregister('_helper/Mage_Eav_Helper_Data');
        Mage::unregister('_helper/Mage_Core_Helper_String');
    }

    /**
     * This test is to check the change made to validateValue.
     * A bug was found where a text attribute that has is_required==1
     * would not accept the string value of "0" (zero) as an input.
     * That bug was fixed. 
     * @covers Mage_Eav_Model_Attribute_Data_Text::validateValue
     * @param string|int|float|array $value
     * @param string|int|float|array $expectedResult
     * @dataProvider dataGetValuesAndResults
     */
    public function testValidateValue($value, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_model->validateValue($value));
    }

    public static function dataGetValuesAndResults()
    {
        return array(
            array("0",true), //The string value of zero should be a valid input
            array(0, array('"%s" is a required value.')) //Integer value of zero remains invalid
        );
    }
}