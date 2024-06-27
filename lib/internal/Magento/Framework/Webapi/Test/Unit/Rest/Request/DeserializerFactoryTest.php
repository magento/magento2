<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Rest\Request;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request\Deserializer\Xml;
use Magento\Framework\Webapi\Rest\Request\DeserializerFactory;
use PHPUnit\Framework\TestCase;

class DeserializerFactoryTest extends TestCase
{
    public function testGetLogicExceptionEmptyRequestAdapter()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Request deserializer adapter is not set.');
        $interpreterFactory = new DeserializerFactory(
            $this->getMockForAbstractClass(ObjectManagerInterface::class),
            []
        );
        $interpreterFactory->get('contentType');
    }

    public function testGet()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $validInterpreterMock = $this->getMockBuilder(
            Xml::class
        )->disableOriginalConstructor()
        ->getMock();

        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())->method('get')->willReturn($validInterpreterMock);

        $interpreterFactory = new DeserializerFactory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }

    public function testGetMagentoWebapiException()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Server cannot understand Content-Type HTTP header media type text_xml');
        $interpreterFactory = new DeserializerFactory(
            $this->getMockForAbstractClass(ObjectManagerInterface::class),
            $expectedMetadata
        );
        $interpreterFactory->get('text_xml');
    }

    public function testGetLogicExceptionInvalidRequestDeserializer()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $invalidInterpreter = $this->getMockBuilder(
            \Magento\Framework\Webapi\Rest\Response\Renderer\Json::class
        )->disableOriginalConstructor()
        ->getMock();

        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'The deserializer must implement "Magento\Framework\Webapi\Rest\Request\DeserializerInterface".'
        );
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())->method('get')->willReturn($invalidInterpreter);

        $interpreterFactory = new DeserializerFactory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }
}
