<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Unit\Model\Source\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Inventory\Model\Source\Validator\NameValidator;
use Magento\InventoryApi\Api\Data\SourceInterface;
use PHPUnit\Framework\TestCase;

class NameValidatorTest extends TestCase
{
    /**
     * @var NameValidator
     */
    private $nameValidator;

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
        $this->validationResultFactory = $this->createMock(ValidationResultFactory::class);
        $this->source = $this->getMockBuilder(SourceInterface::class)->getMock();
    }

    public function testValidateNameNotEmpty()
    {
        $emptyValidatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);
        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->with([
                'errors' => [__('"%field" can not be empty.', ['field' => SourceInterface::NAME])]
            ])
            ->willReturn($emptyValidatorResult);
        $this->nameValidator = (new ObjectManager($this))->getObject(NameValidator::class, [
            'validationResultFactory' => $this->validationResultFactory
        ]);
        $this->source->expects($this->once())
            ->method('getName')
            ->willReturn('');
        $this->nameValidator->validate($this->source);
    }

    public function testValidateNameNotWithInvalidCharacters()
    {
        $emptyValidatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);
        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->with([
                'errors' => [
                    __('"%field" can not contain invalid characters.', ['field' => SourceInterface::NAME])
                ]
            ])
            ->willReturn($emptyValidatorResult);
        $this->nameValidator = (new ObjectManager($this))->getObject(NameValidator::class, [
            'validationResultFactory' => $this->validationResultFactory
        ]);
        $this->source->expects($this->once())
            ->method('getName')
            ->willReturn('${}');
        $this->nameValidator->validate($this->source);
    }

    public function testValidateNameSuccessfully()
    {
        $emptyValidatorResult = $this->createMock(\Magento\Framework\Validation\ValidationResult::class);
        $this->validationResultFactory->expects($this->once())
            ->method('create')
            ->willReturn($emptyValidatorResult);
        $this->nameValidator = (new ObjectManager($this))->getObject(NameValidator::class, [
            'validationResultFactory' => $this->validationResultFactory
        ]);
        $this->source->expects($this->once())
            ->method('getName')
            ->willReturn('testname');

        $result = $this->nameValidator->validate($this->source);
        $errors = $result->getErrors();
        $this->assertCount(0, $errors);
    }
}
