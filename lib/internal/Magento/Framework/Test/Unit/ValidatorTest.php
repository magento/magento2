<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit;

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
        $expectedMessages = [],
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
        $result = [];
        $value = 'test';

        // Case 1. Validators fails without breaking chain
        $validatorA = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorA->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(false));
        $validatorA->expects(
            $this->once()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue(['foo' => ['Foo message 1'], 'bar' => ['Foo message 2']])
        );

        $validatorB = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorB->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(false));
        $validatorB->expects(
            $this->once()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue(['foo' => ['Bar message 1'], 'bar' => ['Bar message 2']])
        );

        $result[] = [
            $value,
            [$validatorA, $validatorB],
            false,
            ['foo' => ['Foo message 1', 'Bar message 1'], 'bar' => ['Foo message 2', 'Bar message 2']],
        ];

        // Case 2. Validators fails with breaking chain
        $validatorA = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorA->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(false));
        $validatorA->expects(
            $this->once()
        )->method(
            'getMessages'
        )->will(
            $this->returnValue(['field' => 'Error message'])
        );

        $validatorB = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorB->expects($this->never())->method('isValid');

        $result[] = [$value, [$validatorA, $validatorB], false, ['field' => 'Error message'], true];

        // Case 3. Validators succeed
        $validatorA = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorA->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(true));
        $validatorA->expects($this->never())->method('getMessages');

        $validatorB = $this->getMock('Magento\Framework\Validator\ValidatorInterface');
        $validatorB->expects($this->once())->method('isValid')->with($value)->will($this->returnValue(true));
        $validatorB->expects($this->never())->method('getMessages');

        $result[] = [$value, [$validatorA, $validatorB], true];

        return $result;
    }

    /**
     * Test addValidator
     */
    public function testAddValidator()
    {
        $fooValidator = new \Magento\Framework\Validator\Test\Unit\Test\IsTrue();
        $classConstraint = new \Magento\Framework\Validator\Constraint($fooValidator, 'id');
        $propertyValidator = new \Magento\Framework\Validator\Constraint\Property($classConstraint, 'name', 'id');

        /** @var \Magento\Framework\Translate\AbstractAdapter $translator */
        $translator = $this->getMockBuilder('Magento\Framework\Translate\AbstractAdapter')->getMockForAbstractClass();
        \Magento\Framework\Validator\AbstractValidator::setDefaultTranslator($translator);

        $this->_validator->addValidator($classConstraint);
        $this->_validator->addValidator($propertyValidator);
        $expected = [
            ['instance' => $classConstraint, 'breakChainOnFailure' => false],
            ['instance' => $propertyValidator, 'breakChainOnFailure' => false],
        ];
        $this->assertAttributeEquals($expected, '_validators', $this->_validator);
        $this->assertEquals($translator, $fooValidator->getTranslator(), 'Translator was not set');
    }

    /**
     * Check that translator passed into validator in chain
     */
    public function testSetTranslator()
    {
        $fooValidator = new \Magento\Framework\Validator\Test\Unit\Test\IsTrue();
        $this->_validator->addValidator($fooValidator);
        /** @var \Magento\Framework\Translate\AbstractAdapter $translator */
        $translator = $this->getMockBuilder('Magento\Framework\Translate\AbstractAdapter')->getMockForAbstractClass();
        $this->_validator->setTranslator($translator);
        $this->assertEquals($translator, $fooValidator->getTranslator());
        $this->assertEquals($translator, $this->_validator->getTranslator());
    }
}
