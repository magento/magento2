<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Interception\Test\Unit\Code;

use \Magento\Framework\Interception\Code\InterfaceValidator;

class InterfaceValidatorTest extends \PHPUnit_Framework_TestCase
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
        $this->argumentsReaderMock = $this->getMock(
            \Magento\Framework\Code\Reader\ArgumentsReader::class,
            [],
            [],
            '',
            false
        );

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
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\ValidPlugin::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments::class
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage Incorrect interface in
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateIncorrectInterface()
    {
        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\IncompatibleInterface::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage Invalid [\Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item] $subject type
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateIncorrectSubjectType()
    {
        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\IncorrectSubject::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage Invalid method signature. Invalid method parameters count
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validateMethodsParameters
     */
    public function testValidateIncompatibleMethodArgumentsCount()
    {
        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model::class
                . '\InterfaceValidator\ItemPlugin\IncompatibleArgumentsCount',
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage Incompatible parameter type
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validateMethodsParameters
     */
    public function testValidateIncompatibleMethodArgumentsType()
    {
        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model::class
                . '\InterfaceValidator\ItemPlugin\IncompatibleArgumentsType',
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments::class
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage Invalid method signature. Invalid method parameters count
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateExtraParameters()
    {
        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\ExtraParameters::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage Invalid [] $name type in
     * @covers \Magento\Framework\Interception\Code\InterfaceValidator::validate
     */
    public function testValidateInvalidProceed()
    {
        $this->model->validate(
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin\InvalidProceed::class,
            \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item::class
        );
    }
}
