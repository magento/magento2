<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter\ArrayType;
use Magento\Framework\Data\Argument\InterpreterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArrayTypeTest extends TestCase
{
    /**
     * @var MockObject|InterpreterInterface
     */
    protected $_itemInterpreter;

    /**
     * @var ArrayType
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_itemInterpreter = $this->getMockForAbstractClass(
            InterpreterInterface::class
        );
        $this->_model = new ArrayType($this->_itemInterpreter);
    }

    /**
     *
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($inputData)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Array items are expected');
        $this->_model->evaluate($inputData);
    }

    /**
     * @return array
     */
    public static function evaluateExceptionDataProvider()
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
            ->willReturnCallback(function ($input) {
                return '-' . $input['value'] . '-';
            });
        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public static function evaluateDataProvider()
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
            'sorted array items' => [
                [
                    'item' => [
                        'key1' => ['value' => 'value 1', 'sortOrder' => 50],
                        'key2' => ['value' => 'value 2'],
                        'key3' => ['value' => 'value 3', 'sortOrder' => 10],
                        'key4' => ['value' => 'value 4'],
                    ],
                ],
                [
                    'key2' => '-value 2-',
                    'key4' => '-value 4-',
                    'key3' => '-value 3-',
                    'key1' => '-value 1-',
                ],
            ],
            'pre-sorted array items' => [
                [
                    'item' => [
                        'key1' => ['value' => 'value 1'],
                        'key4' => ['value' => 'value 4'],
                        'key2' => ['value' => 'value 2', 'sortOrder' => 10],
                        'key3' => ['value' => 'value 3'],
                    ],
                ],
                [
                    'key1' => '-value 1-',
                    'key4' => '-value 4-',
                    'key3' => '-value 3-',
                    'key2' => '-value 2-',
                ],
            ],
            'sort order edge case values' => [
                [
                    'item' => [
                        'key1' => ['value' => 'value 1', 'sortOrder' => 101],
                        'key4' => ['value' => 'value 4'],
                        'key2' => ['value' => 'value 2', 'sortOrder' => -10],
                        'key3' => ['value' => 'value 3'],
                        'key5' => ['value' => 'value 5', 'sortOrder' => 20],
                    ],
                ],
                [
                    'key2' => '-value 2-',
                    'key4' => '-value 4-',
                    'key3' => '-value 3-',
                    'key5' => '-value 5-',
                    'key1' => '-value 1-',
                ],
            ],
        ];
    }
}
