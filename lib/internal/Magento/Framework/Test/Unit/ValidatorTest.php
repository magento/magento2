<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\Translate\AbstractAdapter;
use Magento\Framework\Validator;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\Constraint;
use Magento\Framework\Validator\Constraint\Property;
use Magento\Framework\Validator\Test\Unit\Test\IsTrue;
use Magento\Framework\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test case for \Magento\Framework\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    protected $_validator;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->_validator = new Validator();
    }

    /**
     * Cleanup validator instance to unset default translator if any
     */
    protected function tearDown(): void
    {
        unset($this->_validator);
    }

    /**
     * Test isValid method
     *
     * @dataProvider isValidDataProvider
     *
     * @param mixed $value
     * @param ValidatorInterface[] $validators
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
        $validatorA = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorA->expects($this->once())->method('isValid')->with($value)->willReturn(false);
        $validatorA->expects(
            $this->once()
        )->method(
            'getMessages'
        )->willReturn(
            ['foo' => ['Foo message 1'], 'bar' => ['Foo message 2']]
        );

        $validatorB = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorB->expects($this->once())->method('isValid')->with($value)->willReturn(false);
        $validatorB->expects(
            $this->once()
        )->method(
            'getMessages'
        )->willReturn(
            ['foo' => ['Bar message 1'], 'bar' => ['Bar message 2']]
        );

        $result[] = [
            $value,
            [$validatorA, $validatorB],
            false,
            ['foo' => ['Foo message 1', 'Bar message 1'], 'bar' => ['Foo message 2', 'Bar message 2']],
        ];

        // Case 2. Validators fails with breaking chain
        $validatorA = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorA->expects($this->once())->method('isValid')->with($value)->willReturn(false);
        $validatorA->expects(
            $this->once()
        )->method(
            'getMessages'
        )->willReturn(
            ['field' => 'Error message']
        );

        $validatorB = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorB->expects($this->never())->method('isValid');

        $result[] = [$value, [$validatorA, $validatorB], false, ['field' => 'Error message'], true];

        // Case 3. Validators succeed
        $validatorA = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorA->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $validatorA->expects($this->never())->method('getMessages');

        $validatorB = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorB->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $validatorB->expects($this->never())->method('getMessages');

        $result[] = [$value, [$validatorA, $validatorB], true];

        return $result;
    }

    /**
     * Test addValidator
     */
    public function testAddValidator()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');
        $fooValidator = new IsTrue();
        $classConstraint = new Constraint($fooValidator, 'id');
        $propertyValidator = new Property($classConstraint, 'name', 'id');

        /** @var AbstractAdapter $translator */
        $translator = $this->getMockBuilder(
            AbstractAdapter::class
        )->getMockForAbstractClass();
        AbstractValidator::setDefaultTranslator($translator);

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
        $fooValidator = new IsTrue();
        $this->_validator->addValidator($fooValidator);
        /** @var AbstractAdapter $translator */
        $translator = $this->getMockBuilder(
            AbstractAdapter::class
        )->getMockForAbstractClass();
        $this->_validator->setTranslator($translator);
        $this->assertEquals($translator, $fooValidator->getTranslator());
        $this->assertEquals($translator, $this->_validator->getTranslator());
    }
}
