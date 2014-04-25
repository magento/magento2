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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework;

/**
 * Test case for \Magento\Framework\Validator
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Validator
     */
    protected $_validator;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->_validator = new \Magento\Framework\Validator();
    }

    /**
     * Cleanup validator instance to unset default translator if any
     */
    protected function tearDown()
    {
        unset($this->_validator);
    }

    /**
     * Test isValid method
     *
     * @dataProvider isValidDataProvider
     *
     * @param mixed $value
     * @param \Magento\Framework\Validator\ValidatorInterface[] $validators
     * @param boolean $expectedResult
     * @param array $expectedMessages
     * @param boolean $breakChainOnFailure
     */
    public function testIsValid(
        $value,
        $validators,
        $expectedResult,
        $expectedMessages = array(),
        $breakChainOnFailure = false
    ) {
        foreach ($validators as $validator) {
            $this->_validator->addValidator($validator, $breakChainOnFailure);
        }

        $this->assertEquals($expectedResult, $this->_validator->isValid($value));
        $this->assertEquals($expectedMessages, $this->_validator->getMessages($value));
    }

    /**
     * Data provider for testIsValid
     *
     * @return array
     */
    public function isValidDataProvider()
    {
        $result = array();
        $value = 'test';

        // Case 1. Validators fails without breaking chain
        $validatorA = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorA->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(false));
        $validatorA->expects(
            $this->once()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue(array('foo' => array('Foo message 1'), 'bar' => array('Foo message 2')))
        );

        $validatorB = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorB->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(false));
        $validatorB->expects(
            $this->once()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue(array('foo' => array('Bar message 1'), 'bar' => array('Bar message 2')))
        );

        $result[] = array(
            $value,
            array($validatorA, $validatorB),
            false,
            array('foo' => array('Foo message 1', 'Bar message 1'), 'bar' => array('Foo message 2', 'Bar message 2'))
        );

        // Case 2. Validators fails with breaking chain
        $validatorA = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorA->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(false));
        $validatorA->expects(
            $this->once()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue(array('field' => 'Error message'))
        );

        $validatorB = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorB->expects($this->never())->method('isValid');

        $result[] = array($value, array($validatorA, $validatorB), false, array('field' => 'Error message'), true);

        // Case 3. Validators succeed
        $validatorA = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorA->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(true));
        $validatorA->expects($this->never())->method('getMessages');

        $validatorB = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorB->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(true));
        $validatorB->expects($this->never())->method('getMessages');

        $result[] = array($value, array($validatorA, $validatorB), true);

        return $result;
    }

    /**
     * Test addValidator
     */
    public function testAddValidator()
    {
        $fooValidator = new \Magento\Framework\Validator\Test\True();
        $classConstraint = new \Magento\Framework\Validator\Constraint($fooValidator, 'id');
        $propertyValidator = new \Magento\Framework\Validator\Constraint\Property($classConstraint, 'name', 'id');

        /** @var \Magento\Framework\Translate\AbstractAdapter $translator */
        $translator = $this->getMockBuilder('Magento\Framework\Translate\AbstractAdapter')->getMockForAbstractClass();
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($translator);

        $this->_validator->addValidator($classConstraint);
        $this->_validator->addValidator($propertyValidator);
        $expected = array(
            array('instance' => $classConstraint, 'breakChainOnFailure' => false),
            array('instance' => $propertyValidator, 'breakChainOnFailure' => false)
        );
        $this->assertAttributeEquals($expected, '_validators', $this->_validator);
        $this->assertEquals($translator, $fooValidator->getTranslator(), 'Translator was not set');
    }

    /**
     * Check that translator passed into validator in chain
     */
    public function testSetTranslator()
    {
        $fooValidator = new \Magento\Framework\Validator\Test\True();
        $this->_validator->addValidator($fooValidator);
        /** @var \Magento\Framework\Translate\AbstractAdapter $translator */
        $translator = $this->getMockBuilder('Magento\Framework\Translate\AbstractAdapter')->getMockForAbstractClass();
        $this->_validator->setTranslator($translator);
        $this->assertEquals($translator, $fooValidator->getTranslator());
        $this->assertEquals($translator, $this->_validator->getTranslator());
    }
}
