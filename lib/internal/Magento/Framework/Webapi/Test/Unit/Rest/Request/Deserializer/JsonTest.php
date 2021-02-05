<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest\Request\Deserializer;

class JsonTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $_helperFactoryMock;

    /** @var \Magento\Framework\Webapi\Rest\Request\Deserializer\Json */
    protected $_jsonDeserializer;

    /** @var \Magento\Framework\Json\Decoder */
    protected $decoderMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $_appStateMock;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject */
    private $serializerMock;

    protected function setUp(): void
    {
        /** Prepare mocks for SUT constructor. */
        $this->decoderMock = $this->getMockBuilder(\Magento\Framework\Json\Decoder::class)
            ->disableOriginalConstructor()
            ->setMethods(['decode'])
            ->getMock();
        $this->_appStateMock = $this->createMock(
            \Magento\Framework\App\State::class
        );
        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->getMock();
        /** Initialize SUT. */
        $this->_jsonDeserializer = new \Magento\Framework\Webapi\Rest\Request\Deserializer\Json(
            $this->decoderMock,
            $this->_appStateMock,
            $this->serializerMock
        );
        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->_jsonDeserializer);
        unset($this->decoderMock);
        unset($this->_appStateMock);
        parent::tearDown();
    }

    public function testDeserializerInvalidArgumentException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('"boolean" data type is invalid. String is expected.');
        $this->_jsonDeserializer->deserialize(false);
    }

    public function testDeserialize()
    {
        /** Prepare mocks for SUT constructor. */
        $inputEncodedJson = '{"key1":"test1","key2":"test2","array":{"test01":"some1","test02":"some2"}}';
        $expectedDecodedJson = [
            'key1' => 'test1',
            'key2' => 'test2',
            'array' => ['test01' => 'some1', 'test02' => 'some2'],
        ];
        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($serializedData) {
                    return json_decode($serializedData, true);
                }
            );
        /** Initialize SUT. */
        $this->assertEquals(
            $expectedDecodedJson,
            $this->_jsonDeserializer->deserialize($inputEncodedJson),
            'Deserialization from JSON to array is invalid.'
        );
    }

    public function testDeserializeInvalidEncodedBodyExceptionDeveloperModeOff()
    {
        /** Prepare mocks for SUT constructor. */
        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->will($this->throwException(new \InvalidArgumentException));
        $this->_appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn('production');
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        try {
            $this->_jsonDeserializer->deserialize($inputInvalidJson);
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Framework\Webapi\Exception $e) {
            $this->assertInstanceOf(\Magento\Framework\Webapi\Exception::class, $e, 'Exception type is invalid');
            $this->assertEquals('Decoding error.', $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }

    public function testDeserializeInvalidEncodedBodyExceptionDeveloperModeOn()
    {
        /** Prepare mocks for SUT constructor. */
        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->will(
                $this->throwException(
                    new \InvalidArgumentException('Unable to unserialize value.')
                )
            );
        $this->_appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn('developer');
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        try {
            $this->_jsonDeserializer->deserialize($inputInvalidJson);
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Framework\Webapi\Exception $e) {
            $this->assertInstanceOf(\Magento\Framework\Webapi\Exception::class, $e, 'Exception type is invalid');
            $this->assertStringContainsString('Decoding error:', $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }
}
