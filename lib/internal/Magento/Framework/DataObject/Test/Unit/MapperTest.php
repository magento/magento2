<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DataObject\Test\Unit;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Mapper
     */
    protected $mapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fromMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $toMock;

    protected function setUp()
    {
        $this->fromMock = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->toMock = $this->getMock('Magento\Framework\DataObject', [], [], '', false);
        $this->mapper = new \Magento\Framework\DataObject\Mapper();
    }

    public function testAccumulateByMapWhenToIsArrayFromIsObject()
    {
        $map['key'] = 'map_value';
        $to['key'] = 'from_value';
        $default['new_key'] = 'default_value';
        $this->fromMock->expects($this->once())->method('hasData')->with('key')->will($this->returnValue(true));
        $this->fromMock->expects($this->once())->method('getData')->with('key')->will($this->returnValue('from_value'));
        $expected['key'] = 'from_value';
        $expected['map_value'] = 'from_value';
        $expected['new_key'] = 'default_value';
        $this->assertEquals($expected, $this->mapper->accumulateByMap($this->fromMock, $to, $map, $default));
    }

    public function testAccumulateByMapWhenToAndFromAreObjects()
    {
        $from = [
            $this->fromMock,
            'getData',
        ];
        $to = [
            $this->toMock,
            'setData',
        ];
        $default = [0];
        $map['key'] = ['value'];
        $this->fromMock->expects($this->once())->method('hasData')->with('key')->will($this->returnValue(false));
        $this->fromMock->expects($this->once())->method('getData')->with('key')->will($this->returnValue(true));
        $this->assertEquals($this->toMock, $this->mapper->accumulateByMap($from, $to, $map, $default));
    }

    public function testAccumulateByMapWhenFromIsArrayToIsObject()
    {
        $map['key'] = 'map_value';
        $from['key'] = 'from_value';
        $default['new_key'] = 'default_value';
        $this->toMock->expects($this->exactly(2))->method('setData');
        $this->assertEquals($this->toMock, $this->mapper->accumulateByMap($from, $this->toMock, $map, $default));
    }

    public function testAccumulateByMapFromAndToAreArrays()
    {
        $from['value'] = 'from_value';
        $map[false] = 'value';
        $to['key'] = 'to_value';
        $default['new_key'] = 'value';
        $expected['key'] = 'to_value';
        $expected['value'] = 'from_value';
        $expected['new_key'] = 'value';
        $this->assertEquals($expected, $this->mapper->accumulateByMap($from, $to, $map, $default));
    }
}
