<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Http;

use Magento\Framework\App\Http\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Context
     */
    protected $object;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $this->object = $this->objectManager->getObject(
            Context::class,
            [
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testGetValue()
    {
        $this->assertNull($this->object->getValue('key'));
    }

    public function testSetGetValue()
    {
        $this->object->setValue('key', 'value', 'default');
        $this->assertEquals('value', $this->object->getValue('key'));
    }

    public function testSetUnsetGetValue()
    {
        $this->object->setValue('key', 'value', 'default');
        $this->object->unsValue('key');
        $this->assertEquals('default', $this->object->getValue('key'));
    }

    public function testGetData()
    {
        $this->object->setValue('key1', 'value1', 'default1');
        $this->object->setValue('key2', 'value2', 'default2');
        $this->object->setValue('key3', 'value3', 'value3');
        $this->object->unsValue('key1');
        $this->assertEquals(['key2' => 'value2'], $this->object->getData());
    }

    public function testGetVaryString()
    {
        $this->object->setValue('key2', 'value2', 'default2');
        $this->object->setValue('key1', 'value1', 'default1');
        $data = [
            'key2' => 'value2',
            'key1' => 'value1'
        ];
        ksort($data);
        $this->assertEquals(sha1(json_encode($data)), $this->object->getVaryString());
    }

    public function testToArray()
    {
        $newObject = new Context(['key' => 'value'], [], $this->serializerMock);

        $newObject->setValue('key1', 'value1', 'default1');
        $newObject->setValue('key2', 'value2', 'default2');
        $this->assertEquals(
            [
                'data' => ['key' => 'value', 'key1' => 'value1', 'key2' => 'value2'],
                'default' => ['key1' => 'default1', 'key2' => 'default2']
            ],
            $newObject->toArray()
        );
    }
}
