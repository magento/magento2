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
     * @param array $validatorsClosure
     * @param boolean $expectedResult
     * @param array $expectedMessages
     * @param boolean $breakChainOnFailure
     */
    public function testIsValid(
        $value,
        $validatorsClosure,
        $expectedResult,
        $expectedMessages = [],
        $breakChainOnFailure = false
    ) {
        $validators = [];
        foreach ($validatorsClosure as $key => $validator) {
            if (is_callable($validator)) {
                $validators[$key] = $validator($this);
            }
        }
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
    public static function isValidDataProvider()
    {
        $result = [];
        $value = 'test';
        $dataA = ['foo' => ['Foo message 1'], 'bar' => ['Foo message 2']];
        $dataB = ['foo' => ['Bar message 1'], 'bar' => ['Bar message 2']];

        // Case 1. Validators fails without breaking chain
        $validatorA = static fn (self $testCase) => $testCase->getValidatorMock($dataA, $value);
        $validatorB = static fn (self $testCase) => $testCase->getValidatorMock($dataB, $value);

        $result[] = [
            $value,
            [$validatorA, $validatorB],
            false,
            ['foo' => ['Foo message 1', 'Bar message 1'], 'bar' => ['Foo message 2', 'Bar message 2']],
        ];

        // Case 2. Validators fails with breaking chain
        $dataC = ['field' => 'Error message'];
        $validatorA = static fn (self $testCase) => $testCase->getValidatorMock($dataC, $value);
        $validatorB = static fn (self $testCase) => $testCase->getValidatorMockWithExpectsNever();

        $result[] = [$value, [$validatorA, $validatorB], false, ['field' => 'Error message'], true];

        // Case 3. Validators succeed
        $validatorA = static fn (self $testCase) => $testCase->getValidatorMockWithValidatorsSucceed($value);
        $validatorB = static fn (self $testCase) => $testCase->getValidatorMockWithValidatorsSucceed($value);

        $result[] = [$value, [$validatorA, $validatorB], true];

        return $result;
    }

    public function getValidatorMock($data, $value)
    {
        $validatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorMock->expects($this->once())->method('isValid')->with($value)->willReturn(false);
        $validatorMock->expects($this->once())->method('getMessages')->willReturn($data);
        return $validatorMock;
    }

    public function getValidatorMockWithExpectsNever()
    {
        $validatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorMock->expects($this->never())->method('isValid');
        return $validatorMock;
    }

    public function getValidatorMockWithValidatorsSucceed($value)
    {
        $validatorMock = $this->getMockForAbstractClass(ValidatorInterface::class);
        $validatorMock->expects($this->once())->method('isValid')->with($value)->willReturn(true);
        $validatorMock->expects($this->never())->method('getMessages');
        return $validatorMock;
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
