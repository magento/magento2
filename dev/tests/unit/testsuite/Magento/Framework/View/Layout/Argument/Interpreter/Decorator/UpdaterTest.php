<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Argument\Interpreter\Decorator;

class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_interpreter;

    /**
     * @var Updater
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_interpreter = $this->getMockForAbstractClass('Magento\Framework\Data\Argument\InterpreterInterface');
        $this->_model = new Updater($this->_objectManager, $this->_interpreter);
    }

    public function testEvaluate()
    {
        $input = [
            'value' => 'some text',
            'updater' => ['Magento\Framework\View\Layout\Argument\UpdaterInterface'],
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

        $updater = $this->getMockForAbstractClass('Magento\Framework\View\Layout\Argument\UpdaterInterface');
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
            'Magento\Framework\View\Layout\Argument\UpdaterInterface'
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Layout argument updaters are expected to be an array of classes
     */
    public function testEvaluateWrongUpdaterValue()
    {
        $input = ['value' => 'some text', 'updater' => 'non-array'];
        $this->_model->evaluate($input);
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Instance of layout argument updater is expected
     */
    public function testEvaluateWrongUpdaterClass()
    {
        $input = [
            'value' => 'some text',
            'updater' => [
                'Magento\Framework\View\Layout\Argument\UpdaterInterface',
                'Magento\Framework\ObjectManagerInterface',
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
