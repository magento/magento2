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
    private $model;

    /**
     * @var BooleanUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $booleanUtils;

    /**
     * Prepare subject for test.
     */
    protected function setUp()
    {
        $this->booleanUtils = $this->getMock(BooleanUtils::class);
        $this->booleanUtils->expects(
            $this->any()
        )->method(
            'toBoolean'
        )->will(
            $this->returnValueMap([['true', true], ['false', false]])
        );

        $baseStringUtils = new BaseStringUtils($this->booleanUtils);
        $this->model = new StringUtils($this->booleanUtils, $baseStringUtils);
        /** @var RendererInterface|\PHPUnit_Framework_MockObject_MockObject $translateRenderer */
        $translateRenderer = $this->getMockForAbstractClass(RendererInterface::class);
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
     * Check StringUtils::evaluate can translate incoming $input['value'].
     *
     * @param array $input
     * @param string $expected
     *
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate($input, $expected)
    {
        $actual = $this->model->evaluate($input);
        $this->assertSame($expected, (string)$actual);
    }

    /**
     * Provide test data and expected results for testEvaluate().
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
                'some value (translated)',
            ],
            'translation not required' => [['value' => 'some value', 'translate' => 'false'], 'some value'],
        ];
    }

    /**
     * Check StringUtils::evaluate() throws exception in case $input['value'] is not a string.
     *
     * @param array $input
     *
     * @dataProvider evaluateExceptionDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage String value is expected
     */
    public function testEvaluateException($input)
    {
        $this->model->evaluate($input);
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
