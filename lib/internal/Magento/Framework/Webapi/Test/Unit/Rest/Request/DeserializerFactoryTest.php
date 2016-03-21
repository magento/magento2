<?php
/**
 * Test Webapi Json Deserializer Request Rest Controller.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest\Request;

class DeserializerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLogicExceptionEmptyRequestAdapter()
    {
        $this->setExpectedException('LogicException', 'Request deserializer adapter is not set.');
        $interpreterFactory = new \Magento\Framework\Webapi\Rest\Request\DeserializerFactory(
            $this->getMock('Magento\Framework\ObjectManagerInterface'),
            []
        );
        $interpreterFactory->get('contentType');
    }

    public function testGet()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $validInterpreterMock = $this->getMockBuilder(
            'Magento\Framework\Webapi\Rest\Request\Deserializer\Xml'
        )->disableOriginalConstructor()->getMock();

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($validInterpreterMock));

        $interpreterFactory = new \Magento\Framework\Webapi\Rest\Request\DeserializerFactory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }

    public function testGetMagentoWebapiException()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $this->setExpectedException(
            'Magento\Framework\Webapi\Exception',
            'Server cannot understand Content-Type HTTP header media type text_xml'
        );
        $interpreterFactory = new \Magento\Framework\Webapi\Rest\Request\DeserializerFactory(
            $this->getMock('Magento\Framework\ObjectManagerInterface'),
            $expectedMetadata
        );
        $interpreterFactory->get('text_xml');
    }

    public function testGetLogicExceptionInvalidRequestDeserializer()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $invalidInterpreter = $this->getMockBuilder(
            'Magento\Framework\Webapi\Response\Rest\Renderer\Json'
        )->disableOriginalConstructor()->getMock();

        $this->setExpectedException(
            'LogicException',
            'The deserializer must implement "Magento\Framework\Webapi\Rest\Request\DeserializerInterface".'
        );
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($invalidInterpreter));

        $interpreterFactory = new \Magento\Framework\Webapi\Rest\Request\DeserializerFactory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }
}
