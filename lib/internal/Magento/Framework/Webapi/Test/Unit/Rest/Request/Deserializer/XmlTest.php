<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest\Request\Deserializer;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_xmlParserMock;

    /** @var \Magento\Framework\Webapi\Rest\Request\Deserializer\Xml */
    protected $_xmlDeserializer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_appStateMock;

    protected function setUp()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_xmlParserMock = $this->getMock('Magento\Framework\Xml\Parser', ['xmlToArray', 'loadXML']);
        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        /** Initialize SUT. */
        $this->_xmlDeserializer = new \Magento\Framework\Webapi\Rest\Request\Deserializer\Xml(
            $this->_xmlParserMock,
            $this->_appStateMock
        );
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_xmlDeserializer);
        unset($this->_xmlParserMock);
        unset($this->_appStateMock);
        parent::tearDown();
    }

    public function testDeserializeInvalidArgumentException()
    {
        $this->setExpectedException('InvalidArgumentException', '"boolean" data type is invalid. String is expected.');
        $this->_xmlDeserializer->deserialize(false);
    }

    public function testDeserialize()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $validInputXml = '<?xml version="1.0"?><xml><key1>test1</key1><key2>test2</key2></xml>';
        $returnArray = ['xml' => ['key1' => 'test1', 'key2' => 'test2']];
        $this->_xmlParserMock->expects($this->once())->method('xmlToArray')->will($this->returnValue($returnArray));
        $expectedArray = ['key1' => 'test1', 'key2' => 'test2'];
        /** Initialize SUT. */
        $this->assertEquals(
            $expectedArray,
            $this->_xmlDeserializer->deserialize($validInputXml),
            'Request XML body was parsed incorrectly into array of params.'
        );
    }

    public function testHandleErrors()
    {
        /** Add error message */
        $firstErrorMessage = "No document type declaration. ";
        $this->_xmlDeserializer->handleErrors(null, $firstErrorMessage, null, null);
        /** Assert that first error message was added */
        $this->assertAttributeEquals(
            $firstErrorMessage,
            '_errorMessage',
            $this->_xmlDeserializer,
            'Error message was not set to xml deserializer.'
        );
        /** Add error message */
        $secondErrorMessage = "Strings should be wrapped in double quotes.";
        $expectedMessages = $firstErrorMessage . $secondErrorMessage;
        $this->_xmlDeserializer->handleErrors(null, $secondErrorMessage, null, null);
        /** Assert that both error messages were added */
        $this->assertAttributeEquals(
            $expectedMessages,
            '_errorMessage',
            $this->_xmlDeserializer,
            'Error messages were not set to xml deserializer.'
        );
    }

    public function testDeserializeMagentoWebapiExceptionDeveloperModeOn()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_appStateMock->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue('developer'));
        $errorMessage = 'End tag for "key1" was omitted.';
        $this->_xmlDeserializer->handleErrors(null, $errorMessage, null, null);
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $invalidXml = '<?xml version="1.0"?><xml><key1>test1</xml>';
        /** Initialize SUT. */
        try {
            $this->_xmlDeserializer->deserialize($invalidXml);
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Framework\Webapi\Exception $e) {
            $exceptionMessage = 'Decoding Error: End tag for "key1" was omitted.';
            $this->assertInstanceOf('Magento\Framework\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }

    public function testDeserializeMagentoWebapiExceptionDeveloperModeOff()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_appStateMock->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue('production'));
        $errorMessage = 'End tag for "key1" was omitted.';
        $this->_xmlDeserializer->handleErrors(null, $errorMessage, null, null);
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $invalidXml = '<?xml version="1.0"?><xml><key1>test1</xml>';
        /** Initialize SUT. */
        try {
            $this->_xmlDeserializer->deserialize($invalidXml);
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
}
