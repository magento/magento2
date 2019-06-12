<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use Magento\Framework\View\Layout\Argument\Interpreter\DataObject;

/**
 * Tests layout argument interpreter data object.
 */
class ObjectTest extends \PHPUnit\Framework\TestCase
{
    const EXPECTED_CLASS = \Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter\ObjectTest::class;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_interpreter;

    /**
     * @var \Magento\Framework\Stdlib\BooleanUtils
     */
    protected $_booleanUtils;

    /**
     * @var \Magento\Framework\View\Layout\Argument\Interpreter\DataObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_booleanUtils = $this->createMock(\Magento\Framework\Stdlib\BooleanUtils::class);
        $this->_model = new DataObject($this->_objectManager, self::EXPECTED_CLASS, $this->_booleanUtils);
    }

    public function testEvaluate()
    {
        $input = ['name' => 'dataSource', 'value' => self::EXPECTED_CLASS];
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with(self::EXPECTED_CLASS)
            ->willReturn($this);

        $actual = $this->_model->evaluate($input);
        $this->assertSame($this, $actual);
    }

    public function textEvaluateShareEnabled()
    {
        $input = ['name' => 'dataSource', 'value' => self::EXPECTED_CLASS, 'shared' => true];
        $this->_booleanUtils->expects($this->once())
            ->method('toBoolean')
            ->willReturn(true);
        $this->_objectManager->expects($this->once())
            ->method('get')
            ->with(self::EXPECTED_CLASS)
            ->willReturn($this);

        $actual = $this->_model->evaluate($input);
        $this->assertSame($this, $actual);
    }

    public function textEvaluateShareDisabled()
    {
        $input = ['name' => 'dataSource', 'value' => self::EXPECTED_CLASS, 'shared' => false];
        $this->_booleanUtils->expects($this->once())
            ->method('toBoolean')
            ->willReturn(false);
        $this->_objectManager->expects($this->once())
            ->method('create')
            ->with(self::EXPECTED_CLASS)
            ->willReturn($this);

        $actual = $this->_model->evaluate($input);
        $this->assertSame($this, $actual);
    }

    /**
     * @dataProvider evaluateWrongClassDataProvider
     */
    public function testEvaluateWrongClass($input, $expectedException, $expectedExceptionMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $self = $this;
        $this->_objectManager->expects($this->any())->method('get')->willReturnCallback(
            function ($className) use ($self) {
                return $self->createMock($className);
            }
        );

        $this->_model->evaluate($input);
    }

    /**
     * @dataProvider evaluateWrongClassDataProvider
     */
    public function testEvaluateShareEnabledWrongClass($input, $expectedException, $expectedExceptionMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $self = $this;
        $this->_booleanUtils->expects($this->any())
            ->method('toBoolean')
            ->willReturn(true);
        $this->_objectManager->expects($this->any())->method('get')->willReturnCallback(
            function ($className) use ($self) {
                return $self->createMock($className);
            }
        );

        $input['shared'] = true;

        $this->_model->evaluate($input);
    }

    /**
     * @dataProvider evaluateWrongClassDataProvider
     */
    public function testEvaluateShareDisabledWrongClass($input, $expectedException, $expectedExceptionMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $self = $this;
        $this->_booleanUtils->expects($this->any())
            ->method('toBoolean')
            ->willReturn(false);
        $this->_objectManager->expects($this->any())->method('create')->willReturnCallback(
            function ($className) use ($self) {
                return $self->createMock($className);
            }
        );

        $input['shared'] = false;

        $this->_model->evaluate($input);
    }

    /**
     * @return array
     */
    public function evaluateWrongClassDataProvider()
    {
        return [
            'no class' => [[], '\InvalidArgumentException', 'Object class name is missing'],
            'unexpected class' => [
                ['value' => \Magento\Framework\ObjectManagerInterface::class],
                '\UnexpectedValueException',
                'Instance of ' . self::EXPECTED_CLASS . ' is expected',
            ]
        ];
    }
}
