<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Convert\Test\Unit;

use \Magento\Framework\Convert\DataObject;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new DataObject();
    }

    public function testToOptionArray()
    {
        $mockFirst = $this->getMock('Magento\Framework\DataObject', ['getId', 'getCode'], []);
        $mockFirst->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $mockFirst->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('code1'));
        $mockSecond = $this->getMock('Magento\Framework\DataObject', ['getId', 'getCode'], []);
        $mockSecond->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(2));
        $mockSecond->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue('code2'));

        $callable = function ($item) {
            return $item->getCode();
        };

        $items = [
            $mockFirst,
            $mockSecond,
        ];
        $result = [
            ['value' => 1, 'label' => 'code1'],
            ['value' => 2, 'label' => 'code2'],
        ];
        $this->assertEquals($result, $this->model->toOptionArray($items, 'id', $callable));
    }

    public function testToOptionHash()
    {
        $mockFirst = $this->getMock('Magento\Framework\DataObject', ['getSome', 'getId'], []);
        $mockFirst->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(3));
        $mockFirst->expects($this->once())
            ->method('getSome')
            ->will($this->returnValue('code3'));
        $mockSecond = $this->getMock('Magento\Framework\DataObject', ['getSome', 'getId'], []);
        $mockSecond->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(4));
        $mockSecond->expects($this->once())
            ->method('getSome')
            ->will($this->returnValue('code4'));

        $callable = function ($item) {
            return $item->getId();
        };
        $items = [
            $mockFirst,
            $mockSecond,
        ];
        $result = [
            3 => 'code3',
            4 => 'code4',
        ];

        $this->assertEquals($result, $this->model->toOptionHash($items, $callable, 'some'));
    }

    public function testConvertDataToArray()
    {
        $object = new \stdClass();
        $object->a = [[1]];
        $mockFirst = $this->getMock('Magento\Framework\DataObject', ['getData']);
        $mockSecond = $this->getMock('Magento\Framework\DataObject', ['getData']);

        $mockFirst->expects($this->any())
            ->method('getData')
            ->will($this->returnValue([
                'id' => 1,
                'o' => $mockSecond,
            ]));

        $mockSecond->expects($this->any())
            ->method('getData')
            ->will($this->returnValue([
                'id' => 2,
                'o' => $mockFirst,
            ]));

        $data = [
            'object' => $mockFirst,
            'stdClass' => $object,
            'test' => 'test',
        ];
        $result = [
            'object' => [
                'id' => 1,
                'o' => [
                    'id' => 2,
                    'o' => '*** CYCLE DETECTED ***',
                ],
            ],
            'stdClass' => [
                'a' => [
                    [1],
                ],
            ],
            'test' => 'test',
        ];
        $this->assertEquals($result, $this->model->convertDataToArray($data));
    }
}
