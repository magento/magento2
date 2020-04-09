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
    private $validatorMock;

    /**
     * @var Validator|MockObject
     */
    private $validatorsMock;

    /**
     * @var RowValidatorInterface|MockObject
     */
    private $validatorTestMock;

    protected function setUp(): void
    {
        $this->validatorTestMock = $this->getMockForAbstractClass(RowValidatorInterface::class, [], '', false);
        $messages = ['messages'];
        $this->validatorTestMock->expects($this->any())->method('getMessages')->willReturn($messages);
        $this->validatorsMock = [$this->validatorTestMock];

        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->setMethods(['_clearMessages', '_addMessages'])
            ->setConstructorArgs([$this->validatorsMock])
            ->getMock();
    }

    /**
     * @dataProvider isValidDataProvider
     *
     * @param array $validatorResult
     * @param bool $expectedResult
     * @throws \Zend_Validate_Exception
     */
    public function testIsValid($validatorResult, $expectedResult)
    {
        $this->validatorMock->expects($this->once())->method('_clearMessages');
        $value = 'value';
        $this->validatorTestMock
            ->expects($this->once())->method('isValid')
            ->with($value)
            ->willReturn($validatorResult);

        $result = $this->validatorMock->isValid($value);
        $this->assertEquals($expectedResult, $result);
    }

    public function testIsValidAddMessagesCall()
    {
        $value = 'value';
        $this->validatorTestMock->expects($this->once())->method('isValid')->willReturn(false);
        $this->validatorMock->expects($this->once())->method('_addMessages');

        $this->validatorMock->isValid($value);
    }

    public function testInit()
    {
        $this->validatorTestMock->expects($this->once())->method('init');

        $this->validatorMock->init(null);
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
