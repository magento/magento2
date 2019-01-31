<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Unserialize\Test\Unit;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Unserialize\Unserialize;

class UnserializeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Serialize|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var Unserialize
     */
    private $unserialize;

    protected function setUp()
    {
        $this->serializerMock = $this->getMockBuilder(Serialize::class)
            ->setMethods(
                ['serialize', 'unserialize']
            )
            ->getMock();
        $this->unserialize = new Unserialize($this->serializerMock);
    }

    public function testUnserializeArray()
    {
        $data = ['foo' => 'bar', 1, 4];
        $serializedData = 'serialzied data';
        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);
        $this->assertEquals(
            $data,
            $this->unserialize->unserialize($serializedData)
        );
    }

    /**
     * @param string $serialized The string containing serialized object
     * @expectedException \Exception
     * @expectedExceptionMessage String contains serialized object
     * @dataProvider unserializeObjectDataProvider
     */
    public function testUnserializeObject($serialized)
    {
        $this->assertFalse($this->unserialize->unserialize($serialized));
    }

    /**
     * @return array
     */
    public function unserializeObjectDataProvider()
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
