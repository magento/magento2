<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use \Magento\Framework\View\Layout\Argument\Interpreter\Options;

class OptionsTest extends \PHPUnit_Framework_TestCase
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
     * @var Options
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new Options($this->_objectManager);
    }

    public function testEvaluate()
    {
        $modelClass = \Magento\Framework\Data\OptionSourceInterface::class;
        $model = $this->getMockForAbstractClass($modelClass);
        $model->expects(
            $this->once()
        )->method(
            'toOptionArray'
        )->will(
            $this->returnValue(
                ['value1' => 'label 1', 'value2' => 'label 2', ['value' => 'value3', 'label' => 'label 3']]
            )
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $modelClass
        )->will(
            $this->returnValue($model)
        );
        $input = ['model' => $modelClass];
        $expected = [
            ['value' => 'value1', 'label' => 'label 1'],
            ['value' => 'value2', 'label' => 'label 2'],
            ['value' => 'value3', 'label' => 'label 3'],
        ];
        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider evaluateWrongModelDataProvider
     */
    public function testEvaluateWrongModel($input, $expectedException, $expectedExceptionMessage)
    {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->_model->evaluate($input);
    }

    public function evaluateWrongModelDataProvider()
    {
        return [
            'no model' => [[], '\InvalidArgumentException', 'Options source model class is missing'],
            'wrong model class' => [
                ['model' => \Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter\OptionsTest::class],
                '\UnexpectedValueException',
                'Instance of the options source model is expected',
            ]
        ];
    }
}
