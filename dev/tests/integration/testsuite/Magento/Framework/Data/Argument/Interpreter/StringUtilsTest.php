<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * @covers \Magento\Framework\Data\Argument\Interpreter\StringUtils
 */
class StringUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Argument\Interpreter\StringUtils
     */
    protected $_model;

    /**
     * @var BooleanUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_booleanUtils;

    protected function setUp()
    {
        $this->_booleanUtils = $this->getMock(BooleanUtils::class);
        $this->_booleanUtils->expects(
            $this->any()
        )->method(
            'toBoolean'
        )->will(
            $this->returnValueMap([['true', true], ['false', false]])
        );
        $this->_model = new StringUtils($this->_booleanUtils);
        /** @var RendererInterface|\PHPUnit_Framework_MockObject_MockObject $translateRenderer */
        $translateRenderer = $this->getMockForAbstractClass(RendererInterface::class);
        $translateRenderer->expects(self::never())->method('render');
        \Magento\Framework\Phrase::setRenderer($translateRenderer);
    }

    /**
     * Check StringUtils::evaluate() won't translate incoming $input['value'].
     *
     * @param array $input
     * @param bool $expected
     *
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate($input, $expected)
    {
        $actual = $this->_model->evaluate($input);
        $this->assertSame($expected, (string)$actual);
    }

    /**
     * Provide test data and expected results for testEavaluate().
     *
     * @return array
     */
    public function evaluateDataProvider()
    {
        return [
            'no value' => [[], ''],
            'with value' => [['value' => 'some value'], 'some value'],
            'translation required' => [
                ['value' => 'some value', 'translate' => 'true'],
                'some value',
            ],
            'translation not required' => [['value' => 'some value', 'translate' => 'false'], 'some value']
        ];
    }

    /**
     * Check StringUtils::evaluate() trows exception in case $input['value'] not a string.
     *
     * @param array $input
     *
     * @dataProvider evaluateExceptionDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage String value is expected
     */
    public function testEvaluateException($input)
    {
        $this->_model->evaluate($input);
    }

    /**
     * Provide test data for testEvaluateException.
     *
     * @return array
     */
    public function evaluateExceptionDataProvider()
    {
        return ['not a string' => [['value' => 123]]];
    }
}
