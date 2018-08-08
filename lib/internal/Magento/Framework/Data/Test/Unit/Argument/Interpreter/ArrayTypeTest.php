<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use \Magento\Framework\Data\Argument\Interpreter\ArrayType;

class ArrayTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Data\Argument\InterpreterInterface
     */
    protected $_itemInterpreter;

    /**
     * @var ArrayType
     */
    protected $_model;

    protected function setUp()
    {
        $this->_itemInterpreter = $this->getMockForAbstractClass(
            'Magento\Framework\Data\Argument\InterpreterInterface'
        );
        $this->_model = new ArrayType($this->_itemInterpreter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Array items are expected
     *
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($inputData)
    {
        $this->_model->evaluate($inputData);
    }

    /**
     * @return array
     */
    public function evaluateExceptionDataProvider()
    {
        return [
            'non-array item' => [['item' => 'non-array']],
        ];
    }

    /**
     * @param array $input
     * @param array $expected
     *
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate(array $input, array $expected)
    {
        $this->_itemInterpreter->expects($this->any())
            ->method('evaluate')
            ->will($this->returnCallback(function ($input) {
                return '-' . $input['value'] . '-';
            }));
        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function evaluateDataProvider()
    {
        return [
            'empty array items' => [
                ['item' => []],
                [],
            ],
            'absent array items' => [
                [],
                [],
            ],
            'present array items' => [
                [
                    'item' => [
                        'key1' => ['value' => 'value 1'],
                        'key2' => ['value' => 'value 2'],
                        'key3' => ['value' => 'value 3'],
                    ],
                ],
                [
                    'key1' => '-value 1-',
                    'key2' => '-value 2-',
                    'key3' => '-value 3-',
                ],
            ],
        ];
    }
}
