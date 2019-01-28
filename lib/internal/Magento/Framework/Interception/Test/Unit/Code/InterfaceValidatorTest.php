<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Interception\Test\Unit\Code;

use \Magento\Framework\Interception\Code\InterfaceValidator;
use \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\ValidPlugin;
use \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\IncompatibleInterface;
use \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\IncorrectSubject;
use \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\ExtraParameters;
use \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\InvalidProceed;

class InterfaceValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentsReaderMock;

    /**
     * @var \Magento\Framework\Interception\Code\InterfaceValidator
     */
    protected $model;

    protected function setUp()
    {
        $this->argumentsReaderMock = $this->createMock(\Magento\Framework\Code\Reader\ArgumentsReader::class);

        $this->argumentsReaderMock->expects($this->any())->method('isCompatibleType')
            ->will($this->returnCallback(function ($arg1, $arg2) {
                return ltrim($arg1, '\\') == ltrim($arg2, '\\');
            }));

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
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateIncorrectInterface()
    {
        $this->setExpectedException(\Magento\Framework\Exception\ValidatorException::class, 'Incorrect interface in');

        $this->model->validate(
            IncompatibleInterface::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateIncorrectSubjectType()
    {
        $this->setExpectedException(\Magento\Framework\Exception\ValidatorException::class, '$subject type');

        $this->model->validate(
            IncorrectSubject::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validateMethodsParameters
     */
    public function testValidateIncompatibleMethodArgumentsCount()
    {
        $this->setExpectedException(\Magento\Framework\Exception\ValidatorException::class, 'Invalid method signature. Invalid method parameters count');

        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model::class .
            '\InterfaceValidator\ItemPlugin\IncompatibleArgumentsCount',
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validateMethodsParameters
     */
    public function testValidateIncompatibleMethodArgumentsType()
    {
        $this->setExpectedException(\Magento\Framework\Exception\ValidatorException::class, 'Incompatible parameter type');

        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model::class .
            '\InterfaceValidator\ItemPlugin\IncompatibleArgumentsType',
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateExtraParameters()
    {
        $this->setExpectedException(\Magento\Framework\Exception\ValidatorException::class, 'Invalid method signature. Invalid method parameters count');

        $this->model->validate(
            ExtraParameters::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }

    /**
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateInvalidProceed()
    {
        $this->setExpectedException(\Magento\Framework\Exception\ValidatorException::class, 'Invalid [] $name type in');

        $this->model->validate(
            InvalidProceed::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }
}
