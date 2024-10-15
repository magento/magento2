<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Argument\Interpreter;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\View\Layout\Argument\Interpreter\NamedParams;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NamedParamsTest extends TestCase
{
    /**
     * @var InterpreterInterface|MockObject
     */
    protected $_interpreter;

    /**
     * @var NamedParams
     */
    protected $_model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_interpreter = $this->getMockForAbstractClass(
            InterpreterInterface::class
        );
        $this->_model = new NamedParams($this->_interpreter);
    }

    /**
     * @return void
     */
    public function testEvaluate(): void
    {
        $input = [
            'param' => ['param1' => ['value' => 'value 1'], 'param2' => ['value' => 'value 2']],
        ];

        $this->_interpreter
            ->method('evaluate')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == ['value' => 'value 1']) {
                    return 'value 1 (evaluated)';
                } elseif ($arg1 == ['value' => 'value 2']) {
                    return 'value 2 (evaluated)';
                }
            });
        $expected = ['param1' => 'value 1 (evaluated)', 'param2' => 'value 2 (evaluated)'];

        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return void
     *
     * @dataProvider evaluateWrongParamDataProvider
     */
    public function testEvaluateWrongParam($input, $expectedExceptionMessage): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->_model->evaluate($input);
    }

    /**
     * @return array
     */
    public static function evaluateWrongParamDataProvider(): array
    {
        return [
            'root param is non-array' => [
                ['param' => 'non-array'],
                'Layout argument parameters are expected to be an array'
            ],
            'individual param is non-array' => [
                ['param' => ['sub-param' => 'non-array']],
                'Parameter data of layout argument is expected to be an array'
            ]
        ];
    }
}
