<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Argument\Interpreter;

use Magento\Framework\Phrase\RendererInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * @covers \Magento\Framework\Data\Argument\Interpreter\BaseStringUtils
 */
class BaseStringUtilsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Argument\Interpreter\BaseStringUtils
     */
    private $model;

    /**
     * @var BooleanUtils|\PHPUnit\Framework\MockObject\MockObject
     */
    private $booleanUtils;

    /**
     * Prepare subject for tests.
     */
    protected function setUp(): void
    {
        $this->booleanUtils = $this->createPartialMock(BooleanUtils::class, ['toBoolean']);
        $this->booleanUtils->expects(
            $this->any()
        )->method(
            'toBoolean'
        )->willReturnMap(
            [['true', true], ['false', false]]
        );
        $this->model = new BaseStringUtils($this->booleanUtils);
        /** @var RendererInterface|\PHPUnit\Framework\MockObject\MockObject $translateRenderer */
        $translateRenderer = $this->getMockBuilder(RendererInterface::class)
          ->setMethods(['render'])
          ->getMockForAbstractClass();
        $translateRenderer->expects(self::never())->method('render');
        \Magento\Framework\Phrase::setRenderer($translateRenderer);
    }

    /**
     * Check BaseStringUtils::evaluate() will not translate incoming $input['value'].
     *
     * @param array $input
     * @param bool $expected
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
                'some value',
            ],
            'translation not required' => [['value' => 'some value', 'translate' => 'false'], 'some value'],
        ];
    }

    /**
     * Check BaseStringUtils::evaluate() trows exception in case $input['value'] not a string.
     *
     * @param array $input
     * @dataProvider evaluateExceptionDataProvider
     */
    public function testEvaluateException($input)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('String value is expected');

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
