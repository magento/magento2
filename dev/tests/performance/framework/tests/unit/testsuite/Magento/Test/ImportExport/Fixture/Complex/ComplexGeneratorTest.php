<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\ImportExport\Fixture\Complex;

/**
 * Class ComplexGeneratorTest
 *
 */
class ComplexGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Pattern instance
     *
     * @var \Magento\TestFramework\ImportExport\Fixture\Complex\Pattern
     */
    protected $_pattern;

    /**
     * Get pattern instance
     *
     * @return \Magento\TestFramework\ImportExport\Fixture\Complex\Pattern
     */
    protected function getPattern()
    {
        if (!$this->_pattern instanceof \Magento\TestFramework\ImportExport\Fixture\Complex\Pattern) {
            $patternData = [[
                'id' => '%s',
                'name' => 'Static',
                'calculated' => function ($index) {
                    return $index * 10;
                },
            ],['name' => 'xxx %s'], ['name' => 'yyy %s']];
            $this->_pattern = new \Magento\TestFramework\ImportExport\Fixture\Complex\Pattern();
            $this->_pattern->setHeaders(array_keys($patternData[0]));
            $this->_pattern->setRowsSet($patternData);
        }
        return $this->_pattern;
    }

    /**
     * Test complex generator iterator interface
     */
    public function testIteratorInterface()
    {
        $model = new \Magento\TestFramework\ImportExport\Fixture\Complex\Generator($this->getPattern(), 2);
        $rows = [];
        foreach ($model as $row) {
            $rows[] = $row;
        }
        $this->assertEquals(
            [
                ['id' => '1', 'name' => 'Static', 'calculated' => 10],
                ['id' => '', 'name' => 'xxx 1', 'calculated' => ''],
                ['id' => '', 'name' => 'yyy 1', 'calculated' => ''],
                ['id' => '2', 'name' => 'Static', 'calculated' => 20],
                ['id' => '', 'name' => 'xxx 2', 'calculated' => ''],
                ['id' => '', 'name' => 'yyy 2', 'calculated' => ''],
            ],
            $rows
        );
    }

    /**
     * Test generator getIndex
     */
    public function testGetIndex()
    {
        $model = new \Magento\TestFramework\ImportExport\Fixture\Complex\Generator($this->getPattern(), 4);
        for ($i = 0; $i < 32; $i++) {
            $this->assertEquals($model->getIndex($i), floor($i / $this->getPattern()->getRowsCount()) + 1);
        }
    }
}
