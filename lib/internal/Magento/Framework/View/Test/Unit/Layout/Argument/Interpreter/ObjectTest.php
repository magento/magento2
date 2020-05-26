<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\View\Layout\Argument\Interpreter\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests layout argument interpreter data object.
 */
class ObjectTest extends TestCase
{
    const EXPECTED_CLASS = \Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter\ObjectTest::class;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManager;

    /**
     * @var InterpreterInterface|MockObject
     */
    protected $_interpreter;

    /**
     * @var BooleanUtils
     */
    protected $_booleanUtils;

    /**
     * @var DataObject
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_booleanUtils = $this->createMock(BooleanUtils::class);
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
                ['value' => ObjectManagerInterface::class],
                '\UnexpectedValueException',
                'Instance of ' . self::EXPECTED_CLASS . ' is expected',
            ]
        ];
    }
}
