<?php
/**
 * Test Webapi Json Deserializer Request Rest Controller.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest\Request\Deserializer;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_helperFactoryMock;

    /** @var \Magento\Framework\Webapi\Rest\Request\Deserializer\Json */
    protected $_jsonDeserializer;

    /** @var \Magento\Framework\Json\Decoder */
    protected $decoderMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_appStateMock;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->decoderMock = $this->getMockBuilder('Magento\Framework\Json\Decoder')
            ->disableOriginalConstructor()
            ->setMethods(['decode'])
            ->getMock();
        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        /** Initialize SUT. */
        $this->_jsonDeserializer = new \Magento\Framework\Webapi\Rest\Request\Deserializer\Json(
            $this->decoderMock,
            $this->_appStateMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_jsonDeserializer);
        unset($this->decoderMock);
        unset($this->_appStateMock);
        parent::tearDown();
    }

    public function testDeserializerInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException', '"boolean" data type is invalid. String is expected.');
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
        $this->decoderMock->expects(
            $this->once()
        )->method(
            'decode'
        )->will(
            $this->returnValue($expectedDecodedJson)
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
        $this->decoderMock->expects($this->once())
            ->method('decode')
            ->will($this->throwException(new \Zend_Json_Exception));
        $this->_appStateMock->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue('production'));
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        try {
            $this->_jsonDeserializer->deserialize($inputInvalidJson);
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Framework\Webapi\Exception $e) {
            $this->assertInstanceOf('Magento\Framework\Webapi\Exception', $e, 'Exception type is invalid');
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
        $this->decoderMock->expects(
            $this->once()
        )->method(
            'decode'
        )->will(
            $this->throwException(
                new \Zend_Json_Exception('Decoding error:' . PHP_EOL . 'Decoding failed: Syntax error')
            )
        );
        $this->_appStateMock->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue('developer'));
        /** Initialize SUT. */
        $inputInvalidJson = '{"key1":"test1"."key2":"test2"}';
        try {
            $this->_jsonDeserializer->deserialize($inputInvalidJson);
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Framework\Webapi\Exception $e) {
            $this->assertInstanceOf('Magento\Framework\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertContains('Decoding error:', $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }
}
