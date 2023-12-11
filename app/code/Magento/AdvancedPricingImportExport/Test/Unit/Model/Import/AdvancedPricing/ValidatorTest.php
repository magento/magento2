<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Import\AdvancedPricing;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator as Validator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as RowValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @var Validator|MockObject
     */
    protected $validator;

    /**
     * @var Validator|MockObject
     */
    protected $validators;

    /**
     * @var RowValidatorInterface|MockObject
     */
    protected $validatorTest;

    protected function setUp(): void
    {
        $this->validatorTest = $this->getMockForAbstractClass(
            \Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface::class,
            [],
            '',
            false
        );
        $messages = ['messages'];
        $this->validatorTest->method('getMessages')->willReturn($messages);
        $this->validators = [$this->validatorTest];

        $this->validator = $this->getMockBuilder(
            \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator::class
        )
            ->setMethods(['_clearMessages', '_addMessages'])
            ->setConstructorArgs([$this->validators])
            ->getMock();
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

    /**
     * @return array
     */
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
