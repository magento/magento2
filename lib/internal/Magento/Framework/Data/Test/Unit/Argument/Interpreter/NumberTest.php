<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Argument\Interpreter;

use \Magento\Framework\Data\Argument\Interpreter\Number;

class NumberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Number
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Number();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Numeric value is expected
     *
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($input)
    {
        $this->_model->evaluate($input);
    }

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
