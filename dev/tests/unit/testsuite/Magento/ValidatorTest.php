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
 * @package     Magento_Validator
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test case for Magento_Validator
 *
 * @group validator
 */
class Magento_ValidatorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructException()
    {
        /** @var Magento_Validator_Config $configMock */
        $configMock = $this->getMockBuilder('Magento_Validator_Config')->disableOriginalConstructor()->getMock();
        new Magento_Validator(null, null, $configMock);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructEmptyGroupNameException()
    {
        /** @var Magento_Validator_Config $configMock */
        $configMock = $this->getMockBuilder('Magento_Validator_Config')->disableOriginalConstructor()->getMock();
        new Magento_Validator('test_entity', null, $configMock);
    }

    /**
     * @param array $dataToValidate
     * @param PHPUnit_Framework_MockObject_MockObject $zendConstraintMock
     * @param PHPUnit_Framework_MockObject_MockObject $mageConstraintMock
     * @param Magento_Validator_Config $configMock
     * @dataProvider dataProviderForValidator
     */
    public function testIsValid($dataToValidate, $zendConstraintMock, $mageConstraintMock, $configMock)
    {
        $zendConstraintMock->expects($this->once())
            ->method('isValid')
            ->with($dataToValidate['test_field'])
            ->will($this->returnValue(true));

        $mageConstraintMock->expects($this->once())
            ->method('isValidData')
            ->with($dataToValidate, 'test_field_constraint')
            ->will($this->returnValue(true));

        $validator = new Magento_Validator('test_entity', 'test_group_a', $configMock);
        $this->assertTrue($validator->isValid($dataToValidate));
    }

    /**
     * @param array $dataToValidate
     * @param PHPUnit_Framework_MockObject_MockObject $zendConstraintMock
     * @param PHPUnit_Framework_MockObject_MockObject $mageConstraintMock
     * @param Magento_Validator_Config $configMock
     * @dataProvider dataProviderForValidator
     */
    public function testGetErrors($dataToValidate, $zendConstraintMock, $mageConstraintMock, $configMock)
    {
        $expectedZendErrors = array('Test Zend_Validate_Interface constraint error.');
        $zendConstraintMock->expects($this->once())
            ->method('isValid')
            ->with($dataToValidate['test_field'])
            ->will($this->returnValue(false));
        $zendConstraintMock->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue($expectedZendErrors));

        $expectedMageErrors = array(
            'test_field_constraint' => array('Test Magento_Validator_ConstraintInterface constraint error.')
        );
        $mageConstraintMock->expects($this->once())
            ->method('isValidData')
            ->with($dataToValidate, 'test_field_constraint')
            ->will($this->returnValue(false));
        $mageConstraintMock->expects($this->once())
            ->method('getErrors')
            ->will($this->returnValue($expectedMageErrors));

        $validator = new Magento_Validator('test_entity', 'test_group_a', $configMock);
        $this->assertFalse($validator->isValid($dataToValidate));
        $expectedErrors = array_merge(array('test_field' => $expectedZendErrors),
            $expectedMageErrors);
        $actualErrors = $validator->getMessages();
        $this->assertEquals($expectedErrors, $actualErrors);
    }

    public function dataProviderForValidator()
    {
        $dataToValidate = array(
            'test_field' => 'test_value',
            'test_field_constraint' => 'test value constraint',
        );

        $zendConstraintMock = $this->getMock('Zend_Validate_Alnum', array('isValid', 'getMessages'));
        $mageConstraintMock = $this->getMock('Magento_Validator_Constraint', array('isValidData', 'getErrors'));
        $validationRules = array(
            'test_rule' => array(
                array(
                    'constraint' => $zendConstraintMock,
                    'field' => 'test_field'
                ),
                array(
                    'constraint' => $mageConstraintMock,
                    'field' => 'test_field_constraint'
                ),
            ),
        );

        $configMock = $this->getMockBuilder('Magento_Validator_Config')->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())
            ->method('getValidationRules')
            ->with('test_entity', 'test_group_a')
            ->will($this->returnValue($validationRules));

        return array(
            array($dataToValidate, $zendConstraintMock, $mageConstraintMock, $configMock)
        );
    }
}
