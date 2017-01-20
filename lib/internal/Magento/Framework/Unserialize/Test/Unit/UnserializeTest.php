<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    protected function setUp()
    {
        $this->unserialize = new \Magento\Framework\Unserialize\Unserialize();
    }

    public function testUnserializeArray()
    {
        $array = ['foo' => 'bar', 1, 4];
        $this->assertEquals($array, $this->unserialize->unserialize(serialize($array)));
    }

    /**
     * @param string $serialized The string containing serialized object
     *
     * @expectedException \Exception
     * @expectedExceptionMessage String contains serialized object
     * @dataProvider serializedObjectDataProvider
     */
    public function testUnserializeObject($serialized)
    {
        $this->assertFalse($this->unserialize->unserialize($serialized));
    }

    public function serializedObjectDataProvider()
    {
        return [
            // Upper and lower case serialized object indicators, nested in array
            ['a:2:{i:0;s:3:"foo";i:1;O:6:"Object":1:{s:11:"Objectvar";i:123;}}'],
            ['a:2:{i:0;s:3:"foo";i:1;o:6:"Object":1:{s:11:"Objectvar";i:123;}}'],
            ['a:2:{i:0;s:3:"foo";i:1;c:6:"Object":1:{s:11:"Objectvar";i:123;}}'],
            ['a:2:{i:0;s:3:"foo";i:1;C:6:"Object":1:{s:11:"Objectvar";i:123;}}'],

            // Positive, negative signs on object length, non-nested
            ['o:+6:"Object":1:{s:11:"Objectvar";i:123;}'],
            ['o:-6:"Object":1:{s:11:"Objectvar";i:123;}']
        ];
    }
}
