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
            $patternData = array(array(
                'id' => '%s',
                'name' => 'Static',
                'calculated' => function ($index) {
                    return $index * 10;
                }
            ),array('name' => 'xxx %s'), array('name' => 'yyy %s'));
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
        $rows = array();
        foreach ($model as $row) {
            $rows[] = $row;
        }
        $this->assertEquals(
            array(
                array('id' => '1', 'name' => 'Static', 'calculated' => 10),
                array('id' => '', 'name' => 'xxx 1', 'calculated' => ''),
                array('id' => '', 'name' => 'yyy 1', 'calculated' => ''),
                array('id' => '2', 'name' => 'Static', 'calculated' => 20),
                array('id' => '', 'name' => 'xxx 2', 'calculated' => ''),
                array('id' => '', 'name' => 'yyy 2', 'calculated' => '')
            ),
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
