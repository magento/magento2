<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model\Installer;

use \Magento\Setup\Model\Installer\Progress;

class ProgressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int $total
     * @param int $current
     * @dataProvider constructorExceptionInvalidTotalDataProvider
     * @expectedException \LogicException
     * @expectedExceptionMessage Total number must be more than zero.
     */
    public function testConstructorExceptionInvalidTotal($total, $current)
    {
        new Progress($total, $current);
    }

    /**
     * return array
     */
    public function constructorExceptionInvalidTotalDataProvider()
    {
        return [[0,0], [0, 1], [[], 1]];
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Current cannot exceed total number.
     */
    public function testConstructorExceptionCurrentExceedsTotal()
    {
        new Progress(1, 2);
    }

    public function testSetNext()
    {
        $progress = new Progress(10);
        $progress->setNext();
        $this->assertEquals(1, $progress->getCurrent());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Current cannot exceed total number.
     */
    public function testSetNextException()
    {
        $progress = new Progress(10, 10);
        $progress->setNext();
    }

    public function testFinish()
    {
        $progress = new Progress(10);
        $progress->finish();
        $this->assertEquals(10, $progress->getCurrent());
    }

    public function testGetCurrent()
    {
        $progress = new Progress(10, 5);
        $this->assertEquals(5, $progress->getCurrent());
    }

    public function testGetTotal()
    {
        $progress = new Progress(10);
        $this->assertEquals(10, $progress->getTotal());
    }

    /**
     * @param int $total
     * @param int $current
     * @dataProvider ratioDataProvider
     */
    public function testRatio($total, $current)
    {
        $progress = new Progress($total, $current);
        $this->assertEquals($current / $total, $progress->getRatio());
    }

    /**
     * @return array
     */
    public function ratioDataProvider()
    {
        $data = [];
        for ($i = 10; $i <= 20; $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $data[] = [$i, $j];
            }
        }
        return $data;
    }
}
