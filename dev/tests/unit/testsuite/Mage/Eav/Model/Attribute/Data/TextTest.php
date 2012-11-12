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

class Mage_Eav_Model_Attribute_Data_TextTest extends Magento_Test_TestCase_ObjectManagerAbstract
{
    /**
     * @var Mage_Eav_Model_Attribute_Data_Text
     */
    protected $_model;

    protected function setUp()
    {
        $helper = $this->getMock('Mage_Core_Helper_String', array('__'));
        $helper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0))
        ;
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

        $attributeClass = 'Mage_Eav_Model_Entity_Attribute_Abstract';
        $arguments = $this->_getConstructArguments(
            self::MODEL_ENTITY, $attributeClass, array('data' => $attributeData)
        );

        /** @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract|PHPUnit_Framework_MockObject_MockObject */
        $attribute = $this->getMock($attributeClass, array('_init'), $arguments);
        $this->_model = new Mage_Eav_Model_Attribute_Data_Text(array(
            'translationHelper' => $helper,
            'stringHelper' => $helper,
        ));
        $this->_model->setAttribute($attribute);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @dataProvider validateValueDataProvider
     * @param mixed $inputValue
     * @param mixed $expectedResult
     */
    public function testValidateValue($inputValue, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_model->validateValue($inputValue));
    }

    public static function validateValueDataProvider()
    {
        return array(
            'zero string'  => array('0', true),
            'zero integer' => array(0, array('"%s" is a required value.'))
        );
    }
}
