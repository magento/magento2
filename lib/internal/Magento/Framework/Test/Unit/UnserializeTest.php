<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use Magento\Framework\Unserialize;

/**
 * @package Magento\Framework
 */
class UnserializeTest extends \PHPUnit_Framework_TestCase
{

    public function testUnserialize()
    {
        $array = ['foo' => 'bar', 1, 4];
        $this->assertFalse(Unserialize::unserialize('O:7:"Object2":1:{s:12:"Object2var";i:123;}'));
        $this->assertEquals($array, Unserialize::unserialize(serialize($array)));
    }
}
