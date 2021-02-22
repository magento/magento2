<?php
/**
 * Test Webapi Json Deserializer Request Rest Controller.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest\Request;

class DeserializerFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetLogicExceptionEmptyRequestAdapter()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Request deserializer adapter is not set.');
        $interpreterFactory = new \Magento\Framework\Webapi\Rest\Request\DeserializerFactory(
            $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
            []
        );
        $interpreterFactory->get('contentType');
    }

    public function testGet()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $validInterpreterMock = $this->getMockBuilder(
            \Magento\Framework\Webapi\Rest\Request\Deserializer\Xml::class
        )->disableOriginalConstructor()->getMock();

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())->method('get')->willReturn($validInterpreterMock);

        $interpreterFactory = new \Magento\Framework\Webapi\Rest\Request\DeserializerFactory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }

    public function testGetMagentoWebapiException()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $this->expectException(\Magento\Framework\Webapi\Exception::class);
        $this->expectExceptionMessage('Server cannot understand Content-Type HTTP header media type text_xml');
        $interpreterFactory = new \Magento\Framework\Webapi\Rest\Request\DeserializerFactory(
            $this->createMock(\Magento\Framework\ObjectManagerInterface::class),
            $expectedMetadata
        );
        $interpreterFactory->get('text_xml');
    }

    public function testGetLogicExceptionInvalidRequestDeserializer()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $invalidInterpreter = $this->getMockBuilder(
            \Magento\Framework\Webapi\Response\Rest\Renderer\Json::class
        )->disableOriginalConstructor()->getMock();

        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'The deserializer must implement "Magento\Framework\Webapi\Rest\Request\DeserializerInterface".'
        );
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())->method('get')->willReturn($invalidInterpreter);

        $interpreterFactory = new \Magento\Framework\Webapi\Rest\Request\DeserializerFactory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }
}
