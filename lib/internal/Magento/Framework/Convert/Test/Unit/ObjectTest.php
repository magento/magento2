<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Convert\Test\Unit;

use Magento\Framework\Convert\DataObject;
use PHPUnit\Framework\TestCase;

class ObjectTest extends TestCase
{
    /**
     * @var DataObject
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new DataObject();
    }

    public function testToOptionArray()
    {
        $mockFirst = $this->getMockBuilder(\Magento\Framework\DataObject::class)->addMethods(['getId', 'getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockFirst->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $mockFirst->expects($this->once())
            ->method('getCode')
            ->willReturn('code1');
        $mockSecond = $this->getMockBuilder(\Magento\Framework\DataObject::class)->addMethods(['getId', 'getCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockSecond->expects($this->once())
            ->method('getId')
            ->willReturn(2);
        $mockSecond->expects($this->once())
            ->method('getCode')
            ->willReturn('code2');

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
        $mockFirst = $this->getMockBuilder(\Magento\Framework\DataObject::class)->addMethods(['getSome', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockFirst->expects($this->once())
            ->method('getId')
            ->willReturn(3);
        $mockFirst->expects($this->once())
            ->method('getSome')
            ->willReturn('code3');
        $mockSecond = $this->getMockBuilder(\Magento\Framework\DataObject::class)->addMethods(['getSome', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockSecond->expects($this->once())
            ->method('getId')
            ->willReturn(4);
        $mockSecond->expects($this->once())
            ->method('getSome')
            ->willReturn('code4');

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
        $mockFirst = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getData']);
        $mockSecond = $this->createPartialMock(\Magento\Framework\DataObject::class, ['getData']);

        $mockFirst->expects($this->any())
            ->method('getData')
            ->willReturn([
                'id' => 1,
                'o' => $mockSecond,
            ]);

        $mockSecond->expects($this->any())
            ->method('getData')
            ->willReturn([
                'id' => 2,
                'o' => $mockFirst,
            ]);

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
