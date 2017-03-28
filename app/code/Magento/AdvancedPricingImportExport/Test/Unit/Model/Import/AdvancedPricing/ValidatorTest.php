<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing;

use \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator as Validator;
use \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as RowValidatorInterface;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Validator |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    /**
     * @var Validator |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validators;

    /**
     * @var RowValidatorInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorTest;

    protected function setUp()
    {
        $this->validatorTest = $this->getMockForAbstractClass(
            \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::class,
            [],
            '',
            false
        );
        $messages = ['messages'];
        $this->validatorTest->expects($this->any())->method('getMessages')->willReturn($messages);
        $this->validators = [$this->validatorTest];

        $this->validator = $this->getMock(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator::class,
            ['_clearMessages', '_addMessages'],
            [$this->validators]
        );
    }

    /**
     * @dataProvider isValidDataProvider
     *
     * @param array $validatorResult
     * @param bool  $expectedResult
     */
    public function testIsValid($validatorResult, $expectedResult)
    {
        $this->validator->expects($this->once())->method('_clearMessages');
        $value = 'value';
        $this->validatorTest->expects($this->once())->method('isValid')->with($value)->willReturn($validatorResult);

        $result = $this->validator->isValid($value);
        $this->assertEquals($expectedResult, $result);
    }

    public function testIsValidAddMessagesCall()
    {
        $value = 'value';
        $this->validatorTest->expects($this->once())->method('isValid')->willReturn(false);
        $this->validator->expects($this->once())->method('_addMessages');

        $this->validator->isValid($value);
    }

    public function testInit()
    {
        $this->validatorTest->expects($this->once())->method('init');

        $this->validator->init(null);
    }

    public function isValidDataProvider()
    {
        return [
            [
                '$validatorResult' => true,
                '$expectedResult' => true,
            ],
            [
                '$validatorResult' => false,
                '$expectedResult' => false,
            ]
        ];
    }
}
