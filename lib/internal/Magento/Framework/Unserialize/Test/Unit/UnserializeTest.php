<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Unserialize\Test\Unit;

/**
 * @package Magento\Framework
 */
class UnserializeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Unserialize\Unserialize */
    protected $unserialize;

    public function setUp()
    {
        $this->unserialize = new \Magento\Framework\Unserialize\Unserialize();
    }

    public function testUnserializeArray()
    {
        $array = ['foo' => 'bar', 1, 4];
        $this->assertEquals($array, $this->unserialize->unserialize(serialize($array)));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage String contains serialized object
     */
    public function testUnserializeObject()
    {
        $serialized = 'a:2:{i:0;s:3:"foo";i:1;O:6:"Object":1:{s:11:"Objectvar";i:123;}}';
        $this->assertFalse($this->unserialize->unserialize($serialized));
    }
}
