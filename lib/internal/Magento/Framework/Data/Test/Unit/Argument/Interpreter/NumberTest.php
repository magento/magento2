<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use Magento\Framework\Data\Argument\Interpreter\Number;
use PHPUnit\Framework\TestCase;

class NumberTest extends TestCase
{
    /**
     * @var Number
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Number();
    }

    /**
     *
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($input)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Numeric value is expected');
        $this->_model->evaluate($input);
    }

    /**
     * @return array
     */
    public function evaluateExceptionDataProvider()
    {
        return ['no value' => [[]], 'non-numeric value' => [['value' => 'non-numeric']]];
    }

    /**
     * @param array $input
     * @param bool $expected
     *
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate($input, $expected)
    {
        $actual = $this->_model->evaluate(['value' => $input]);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function evaluateDataProvider()
    {
        return [
            'integer' => [10, 10],
            'float' => [10.5, 10.5],
            'string numeric (integer)' => ['10', '10'],
            'string numeric (float)' => ['10.5', '10.5']
        ];
    }
}
