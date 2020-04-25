<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter\Decorator;

use PHPUnit\Framework\TestCase;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Layout\Argument\UpdaterInterface;
use \Magento\Framework\View\Layout\Argument\Interpreter\Decorator\Updater;

class UpdaterTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $_objectManager;

    /**
     * @var InterpreterInterface|MockObject
     */
    protected $_interpreter;

    /**
     * @var Updater
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->_interpreter = $this->getMockForAbstractClass(
            InterpreterInterface::class
        );
        $this->_model = new Updater($this->_objectManager, $this->_interpreter);
    }

    public function testEvaluate()
    {
        $input = [
            'value' => 'some text',
            'updater' => [UpdaterInterface::class],
        ];
        $evaluatedValue = 'some text (new)';
        $updatedValue = 'some text (updated)';

        $this->_interpreter->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            ['value' => 'some text']
        )->will(
            $this->returnValue($evaluatedValue)
        );

        $updater = $this->getMockForAbstractClass(UpdaterInterface::class);
        $updater->expects(
            $this->once()
        )->method(
            'update'
        )->with(
            $evaluatedValue
        )->will(
            $this->returnValue($updatedValue)
        );

        $this->_objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            UpdaterInterface::class
        )->will(
            $this->returnValue($updater)
        );

        $actual = $this->_model->evaluate($input);
        $this->assertSame($updatedValue, $actual);
    }

    public function testEvaluateNoUpdaters()
    {
        $input = ['value' => 'some text'];
        $expected = ['value' => 'new text'];

        $this->_interpreter->expects(
            $this->once()
        )->method(
            'evaluate'
        )->with(
            $input
        )->will(
            $this->returnValue($expected)
        );
        $this->_objectManager->expects($this->never())->method('get');

        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    public function testEvaluateWrongUpdaterValue()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Layout argument updaters are expected to be an array of classes');
        $input = ['value' => 'some text', 'updater' => 'non-array'];
        $this->_model->evaluate($input);
    }

    public function testEvaluateWrongUpdaterClass()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Instance of layout argument updater is expected');
        $input = [
            'value' => 'some text',
            'updater' => [
                UpdaterInterface::class,
                ObjectManagerInterface::class,
            ],
        ];
        $self = $this;
        $this->_objectManager->expects($this->exactly(2))->method('get')->will(
            $this->returnCallback(
                function ($className) use ($self) {
                    return $self->getMockForAbstractClass($className);
                }
            )
        );

        $this->_model->evaluate($input);
    }
}
