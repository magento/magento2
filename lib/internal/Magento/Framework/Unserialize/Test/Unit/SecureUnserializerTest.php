<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Unserialize\Test\Unit;

/**
 * Tests \Magento\Framework\Unserialize\SecureUnserializer.
 */
class SecureUnserializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Unserialize\SecureUnserializer
     */
    protected $unserializer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->unserializer = new \Magento\Framework\Unserialize\SecureUnserializer();
    }

    /**
     * @return void
     */
    public function testUnserializeArray()
    {
        $array = ['foo' => 'bar', 1, 4];
        $this->assertEquals($array, $this->unserializer->unserialize(serialize($array)));
    }

    /**
     * @param string $serialized The string containing serialized object
     * @return void
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Data contains serialized object and cannot be unserialized
     * @dataProvider serializedObjectDataProvider
     */
    public function testUnserializeObject($serialized)
    {
        $this->assertFalse($this->unserializer->unserialize($serialized));
    }

    /**
     * @return array
     */
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
            ['o:-6:"Object":1:{s:11:"Objectvar";i:123;}'],
        ];
    }
}
