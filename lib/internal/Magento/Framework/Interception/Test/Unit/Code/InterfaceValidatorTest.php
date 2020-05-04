<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Code;

use Magento\Framework\Code\Reader\ArgumentsReader;
use Magento\Framework\Interception\Code\InterfaceValidator;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\ExtraParameters;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\IncompatibleInterface;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\IncorrectSubject;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\InvalidProceed;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\ValidPlugin;
use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InterfaceValidatorTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $argumentsReaderMock;

    /**
     * @var InterfaceValidator
     */
    protected $model;

    protected function setUp(): void
    {
        $this->argumentsReaderMock = $this->createMock(ArgumentsReader::class);

        $this->argumentsReaderMock->expects($this->any())->method('isCompatibleType')
            ->willReturnCallback(function ($arg1, $arg2) {
                return ltrim((string)$arg1, '\\') == ltrim((string)$arg2, '\\');
            });

        $this->model = new InterfaceValidator($this->argumentsReaderMock);
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::getMethodParameters
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::getMethodType
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::getOriginMethodName
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::getParametersType
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::__construct
     */
    public function testValidate()
    {
        $this->model->validate(
            ValidPlugin::class,
            ItemWithArguments::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateIncorrectInterface()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('Incorrect interface in');
        $this->model->validate(
            IncompatibleInterface::class,
            Item::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateIncorrectSubjectType()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('$subject type');
        $this->model->validate(
            IncorrectSubject::class,
            Item::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validateMethodsParameters
     */
    public function testValidateIncompatibleMethodArgumentsCount()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('Invalid method signature. Invalid method parameters count');
        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model::class .
            '\InterfaceValidator\ItemPlugin\IncompatibleArgumentsCount',
            Item::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validateMethodsParameters
     */
    public function testValidateIncompatibleMethodArgumentsType()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('Incompatible parameter type');
        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model::class .
            '\InterfaceValidator\ItemPlugin\IncompatibleArgumentsType',
            ItemWithArguments::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateExtraParameters()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('Invalid method signature. Invalid method parameters count');
        $this->model->validate(
            ExtraParameters::class,
            Item::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateInvalidProceed()
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('Invalid [] $name type in');
        $this->model->validate(
            InvalidProceed::class,
            Item::class
        );
    }
}
