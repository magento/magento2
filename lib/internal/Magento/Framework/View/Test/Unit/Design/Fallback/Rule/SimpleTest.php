<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Design\Fallback\Rule;

use \Magento\Framework\View\Design\Fallback\Rule\Simple;

/**
 * Simple Test
 *
 */
class SimpleTest extends \PHPUnit\Framework\TestCase
{
    /**
     */
    public function testGetPatternDirsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Required parameter \'required_parameter\' was not passed');

        $model = new Simple('<required_parameter> other text');
        $model->getPatternDirs([]);
    }

    /**
     * @dataProvider getPatternDirsDataProvider
     */
    public function testGetPatternDirs($pattern, $optionalParameter = null, $expectedResult = null)
    {
        $params = ['optional_parameter' => $optionalParameter, 'required_parameter' => 'required_parameter'];
        $model = new Simple($pattern, ['optional_parameter']);

        $this->assertEquals($expectedResult, $model->getPatternDirs($params));
    }

    /**
     * @return array
     */
    public function getPatternDirsDataProvider()
    {
        $patternOptional = '<optional_parameter> <required_parameter> other text';
        $patternNoOptional = '<required_parameter> other text';

        return [
            'no optional param passed' => [$patternOptional, null, []],
            'no optional param in pattern' => [
                $patternNoOptional,
                'optional_parameter',
                ['required_parameter other text'],
            ],
            'optional params in pattern and passed' => [
                $patternOptional,
                'optional_parameter',
                ['optional_parameter required_parameter other text'],
            ]
        ];
    }
}
