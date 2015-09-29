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
        $serialized = 'a:2:{i:0;s:3:"foo";i:1;O:6:"Object":1:{s:11:"Objectvar";i:123;}}';
        $this->assertFalse(Unserialize::unserialize($serialized));
        $this->assertEquals($array, Unserialize::unserialize(serialize($array)));
    }
}
