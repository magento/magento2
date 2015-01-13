<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\ImportExport\Fixture\Complex;

/**
 * Class PatternTest
 *
 */
class PatternTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get pattern object
     *
     * @param $patternData
     *
     * @return \Magento\TestFramework\ImportExport\Fixture\Complex\Pattern
     */
    protected function getPattern($patternData)
    {
        $pattern = new \Magento\TestFramework\ImportExport\Fixture\Complex\Pattern();
        $pattern->setHeaders(array_keys($patternData[0]));
        $pattern->setRowsSet($patternData);
        return $pattern;
    }

    /**
     * Data source for pattern
     *
     * @return array
     */
    public function patternDataPrivider()
    {
        $result = [0 => [[[
            'id' => '%s',
            'name' => 'Static',
            'calculated' => function ($index) {
                return $index * 10;
            },
        ],
            ['name' => 'xxx %s'],
            ['name' => 'yyy %s'],
        ],
            'ecpectedCount' => 3,
            'expectedRowsResult' => [
                ['id' => '1', 'name' => 'Static', 'calculated' => 10],
                ['id' => '', 'name' => 'xxx 1', 'calculated' => ''],
                ['id' => '', 'name' => 'yyy 1', 'calculated' => ''],
            ],
        ],
            1 => [
                [['id' => '%s', 'name' => 'Dynamic %s', 'calculated' => 'calc %s']],
                'ecpectedCount' => 1,
                'expectedRowsResult' => [['id' => '1', 'name' => 'Dynamic 1', 'calculated' => 'calc 1']],
            ],
        ];
        return $result;
    }

    /**
     * Test pattern object
     *
     * @param array $patternData
     * @param int $expectedRowsCount
     * @param array $expectedRowsResult
     *
     * @dataProvider patternDataPrivider
     * @test
     */
    public function testPattern($patternData, $expectedRowsCount, $expectedRowsResult)
    {
        $pattern = $this->getPattern($patternData);
        $this->assertEquals($pattern->getRowsCount(), $expectedRowsCount);
        foreach ($expectedRowsResult as $key => $expectedRow) {
            $this->assertEquals($expectedRow, $pattern->getRow(floor($key / $pattern->getRowsCount()) + 1, $key));
        }
    }
}
