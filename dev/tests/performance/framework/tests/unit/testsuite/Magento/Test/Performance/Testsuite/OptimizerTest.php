<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Performance\Testsuite;

class OptimizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Performance\Testsuite\Optimizer
     */
    protected $_optimizer;

    protected function setUp()
    {
        $this->_optimizer = new \Magento\TestFramework\Performance\Testsuite\Optimizer();
    }

    protected function tearDown()
    {
        unset($this->_optimizer);
    }

    /**
     * @param array $fixtureSets
     * @param array $expected
     * @dataProvider optimizeFixtureSetsDataProvider
     */
    public function testOptimizeFixtureSets($fixtureSets, $expected)
    {
        $optimized = $this->_optimizer->optimizeFixtureSets($fixtureSets);
        $this->assertEquals($optimized, $expected);
    }

    /**
     * @return array
     */
    public function optimizeFixtureSetsDataProvider()
    {
        return [
            'empty_list' => ['fixtureSets' => [], 'expected' => []],
            'single_scenario' => ['fixtureSets' => ['a' => ['f1', 'f2']], 'expected' => ['a']],
            'empty_fixtures' => [
                'fixtureSets' => ['a' => [], 'b' => []],
                'expected' => ['a', 'b'],
            ],
            'from_smaller_to_bigger' => [
                'fixtureSets' => ['a' => ['f1', 'f2'], 'b' => ['f2'], 'c' => ['f3']],
                'expected' => ['b', 'a', 'c'],
            ],
            'same_together' => [
                'fixtureSets' => ['a' => ['f1', 'f2'], 'b' => ['f1'], 'c' => ['f1']],
                'expected' => ['b', 'c', 'a'],
            ]
        ];
    }
}
