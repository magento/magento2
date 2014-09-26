<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $result = array(0 => array(array(array(
            'id' => '%s',
            'name' => 'Static',
            'calculated' => function ($index) {
                return $index * 10;
            }
        ),
            array('name' => 'xxx %s'),
            array('name' => 'yyy %s')
        ),
            'ecpectedCount' => 3,
            'expectedRowsResult' => array(
                array('id' => '1', 'name' => 'Static', 'calculated' => 10),
                array('id' => '', 'name' => 'xxx 1', 'calculated' => ''),
                array('id' => '', 'name' => 'yyy 1', 'calculated' => '')
            )
        ),
            1 => array(
                array(array('id' => '%s', 'name' => 'Dynamic %s', 'calculated' => 'calc %s')),
                'ecpectedCount' => 1,
                'expectedRowsResult' => array(array('id' => '1', 'name' => 'Dynamic 1', 'calculated' => 'calc 1'))
            )
        );
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
