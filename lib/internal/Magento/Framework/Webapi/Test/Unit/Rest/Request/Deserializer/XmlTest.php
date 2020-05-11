<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Rest\Request\Deserializer;

use Magento\Framework\App\State;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request\Deserializer\Xml;
use Magento\Framework\Xml\Parser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /** @var MockObject */
    protected $_xmlParserMock;

    /** @var Xml */
    protected $_xmlDeserializer;

    /** @var MockObject */
    protected $_appStateMock;

    protected function setUp(): void
    {
        /** Prepare mocks for SUT constructor. */
        $this->_xmlParserMock = $this->createPartialMock(
            Parser::class,
            ['xmlToArray', 'loadXML']
        );
        $this->_appStateMock = $this->createMock(State::class);
        /** Initialize SUT. */
        $this->_xmlDeserializer = new Xml(
            $this->_xmlParserMock,
            $this->_appStateMock
        );
        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->_xmlDeserializer);
        unset($this->_xmlParserMock);
        unset($this->_appStateMock);
        parent::tearDown();
    }

    public function testDeserializeInvalidArgumentException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('"boolean" data type is invalid. String is expected.');
        $this->_xmlDeserializer->deserialize(false);
    }

    public function testDeserialize()
    {
        /** Prepare mocks for SUT constructor. */
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $validInputXml = '<?xml version="1.0"?><xml><key1>test1</key1><key2>test2</key2></xml>';
        $returnArray = ['xml' => ['key1' => 'test1', 'key2' => 'test2']];
        $this->_xmlParserMock->expects($this->once())->method('xmlToArray')->willReturn($returnArray);
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
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');

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
            ->willReturn('developer');
        $errorMessage = 'End tag for "key1" was omitted.';
        $this->_xmlDeserializer->handleErrors(null, $errorMessage, null, null);
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $invalidXml = '<?xml version="1.0"?><xml><key1>test1</xml>';
        /** Initialize SUT. */
        try {
            $this->_xmlDeserializer->deserialize($invalidXml);
            $this->fail("Exception is expected to be raised");
        } catch (Exception $e) {
            $exceptionMessage = 'Decoding Error: End tag for "key1" was omitted.';
            $this->assertInstanceOf(Exception::class, $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                Exception::HTTP_BAD_REQUEST,
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
            ->willReturn('production');
        $errorMessage = 'End tag for "key1" was omitted.';
        $this->_xmlDeserializer->handleErrors(null, $errorMessage, null, null);
        $this->_xmlParserMock->expects($this->once())->method('loadXML');
        $invalidXml = '<?xml version="1.0"?><xml><key1>test1</xml>';
        /** Initialize SUT. */
        try {
            $this->_xmlDeserializer->deserialize($invalidXml);
            $this->fail("Exception is expected to be raised");
        } catch (Exception $e) {
            $this->assertInstanceOf(Exception::class, $e, 'Exception type is invalid');
            $this->assertEquals('Decoding error.', $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                Exception::HTTP_BAD_REQUEST,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }
}
