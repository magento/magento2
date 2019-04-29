<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryImportExport\Model\Import\Validator\ValidatorChain;
use Magento\InventoryImportExport\Model\Import\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

class ValidatorChainTest extends TestCase
{
    /**
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $qtyValidator;

    /**
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $skuValidator;

    /**
     * @var ValidationResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResultFactory;

    /**
     * @var ValidatorChain
     */
    private $validatorChain;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->validationResultFactory = $this->createMock(ValidationResultFactory::class);
        $this->qtyValidator = $this->getMockBuilder(ValidatorInterface::class)->getMock();
        $this->skuValidator = $this->getMockBuilder(ValidatorInterface::class)->getMock();
    }

    public function testValidateWithOutValidators()
    {
        $emptyValidatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);
        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->with(['errors' =>[]])
            ->willReturn($emptyValidatorResult);

        $this->validatorChain = (new ObjectManager($this))->getObject(
            ValidatorChain::class,
            [
                'validationResultFactory' => $this->validationResultFactory,
                'validators' => []
            ]
        );

        $result = $this->validatorChain->validate([], 1);
        $this->assertEquals($emptyValidatorResult, $result);
    }

    public function testValidateWithOutErros()
    {
        $emptyValidatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);
        $emptyValidatorResult->expects($this->once())->method('isValid')
            ->willReturn(true);

        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->with(['errors' => []])
            ->willReturn($emptyValidatorResult);

        $this->qtyValidator->method('validate')
            ->willReturn($emptyValidatorResult);

        $this->validatorChain = (new ObjectManager($this))->getObject(
            ValidatorChain::class,
            [
                'validationResultFactory' => $this->validationResultFactory,
                'validators' => [$this->qtyValidator]
            ]
        );

        $result = $this->validatorChain->validate([], 1);
        $this->assertEquals($emptyValidatorResult, $result);
    }

    public function testValidateWithErros()
    {
        $validatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);

        $validatorResult->expects($this->once())->method('isValid')
            ->willReturn(false);

        $validatorResult->expects($this->once())
            ->method('getErrors')
            ->willReturn(['Qty can not negative', 'Additional error']);

        $this->qtyValidator->expects($this->once())->method('validate')
            ->willReturn($validatorResult);

        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->with(['errors' => ['Qty can not negative', 'Additional error']])
            ->willReturn($validatorResult);

        $this->validatorChain = (new ObjectManager($this))->getObject(
            ValidatorChain::class,
            [
                'validationResultFactory' => $this->validationResultFactory,
                'validators' => [$this->qtyValidator]
            ]
        );

        $result = $this->validatorChain->validate([-1], 1);
        $this->assertEquals($validatorResult, $result);
    }
}
