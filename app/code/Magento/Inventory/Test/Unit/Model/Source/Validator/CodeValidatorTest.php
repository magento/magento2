<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Unit\Model\Source\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Inventory\Model\Source\Validator\CodeValidator;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
use PHPUnit\Framework\TestCase;

class CodeValidatorTest extends TestCase
{

    /**
     * @var CodeValidator
     */
    private $codeValidator;

    /**
     * @var SourceInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var ValidationResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResultFactory;

    protected function setUp()
    {
        $this->validationResultFactory = $this->getMockBuilder(ValidationResultFactory::class)->getMock();
        $this->source = $this->getMockBuilder(SourceInterface::class)->getMock();
    }

    public function testValidateCodeNotEmpty()
    {
        $emptyValidatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);
        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->with(['errors' => [__('"%field" can not be empty.', ['field' => SourceInterface::SOURCE_CODE])]])
            ->willReturn($emptyValidatorResult);
        $this->codeValidator = (new ObjectManager($this))->getObject(CodeValidator::class, [
            'validationResultFactory' => $this->validationResultFactory
        ]);

        $this->source->expects($this->once())
            ->method('getSourceCode')
            ->willReturn('  ');
        $this->codeValidator->validate($this->source);
    }

    public function testValidateCodeNotWithWhiteSpaces()
    {
        $emptyValidatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);
        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->with([
                'errors' => [__('"%field" can not contain whitespaces.', ['field' => SourceInterface::SOURCE_CODE])]
            ])
            ->willReturn($emptyValidatorResult);
        $this->codeValidator = (new ObjectManager($this))->getObject(CodeValidator::class, [
            'validationResultFactory' => $this->validationResultFactory
        ]);
        $this->source->expects($this->once())
            ->method('getSourceCode')
            ->willReturn(' source code ');
        $this->codeValidator->validate($this->source);
    }

    public function testValidateCodeSuccessfully()
    {
        $emptyValidatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);
        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($emptyValidatorResult);
        $this->codeValidator = (new ObjectManager($this))->getObject(CodeValidator::class, [
            'validationResultFactory' => $this->validationResultFactory
        ]);
        $this->source->expects($this->once())
            ->method('getSourceCode')
            ->willReturn(' source_code ');

        $result = $this->codeValidator->validate($this->source);
        $errors = $result->getErrors();
        $this->assertCount(0, $errors);
    }
}
