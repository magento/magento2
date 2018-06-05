<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Argument\Interpreter;

class StringUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Argument\Interpreter\StringUtils
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_booleanUtils;

    protected function setUp()
    {
        $this->_booleanUtils = $this->getMock('\Magento\Framework\Stdlib\BooleanUtils');
        $this->_booleanUtils->expects(
            $this->any()
        )->method(
            'toBoolean'
        )->will(
            $this->returnValueMap([['true', true], ['false', false]])
        );
        $this->_model = new StringUtils($this->_booleanUtils);
        $translateRenderer = $this->getMockForAbstractClass('Magento\Framework\Phrase\RendererInterface');
        $translateRenderer->expects($this->any())->method('render')->will(
            $this->returnCallback(
                function ($input) {
                    return end($input) . ' (translated)';
                }
            )
        );
        \Magento\Framework\Phrase::setRenderer($translateRenderer);
    }

    /**
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

    public function evaluateDataProvider()
    {
        return [
            'no value' => [[], ''],
            'with value' => [['value' => 'some value'], 'some value'],
            'translation required' => [
                ['value' => 'some value', 'translate' => 'true'],
                'some value (translated)',
            ],
            'translation not required' => [['value' => 'some value', 'translate' => 'false'], 'some value']
        ];
    }

    /**
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

    public function evaluateExceptionDataProvider()
    {
        return ['not a string' => [['value' => 123]]];
    }
}
