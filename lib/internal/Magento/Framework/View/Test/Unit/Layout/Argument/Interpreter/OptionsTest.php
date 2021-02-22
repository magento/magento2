<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use \Magento\Framework\View\Layout\Argument\Interpreter\Options;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_interpreter;

    /**
     * @var Options
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
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
        )->willReturn(
            
                ['value1' => 'label 1', 'value2' => 'label 2', ['value' => 'value3', 'label' => 'label 3']]
            
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $modelClass
        )->willReturn(
            $model
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
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->_objectManager->method('get')
            ->willReturnSelf();
        $this->_model->evaluate($input);
    }

    /**
     * @return array
     */
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
