<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Test\Unit\DataConverter;

use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SerializedToJsonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Serialize|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializeMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

    /**
     * @var SerializedToJson
     */
    private $serializedToJson;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->serializeMock = $this->createMock(Serialize::class);
        $this->jsonMock = $this->createMock(Json::class);
        $this->serializedToJson = $objectManager->getObject(
            SerializedToJson::class,
            [
                'serialize' => $this->serializeMock,
                'json' => $this->jsonMock
            ]
        );
    }

    public function testConvert()
    {
        $serializedData = 'serialized data';
        $jsonData = 'json data';
        $unserializedData = 'unserialized data';
        $this->serializeMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($unserializedData);
        $this->jsonMock->expects($this->once())
            ->method('serialize')
            ->with($unserializedData)
            ->willReturn($jsonData);
        $this->assertEquals($jsonData, $this->serializedToJson->convert($serializedData));
    }
}
