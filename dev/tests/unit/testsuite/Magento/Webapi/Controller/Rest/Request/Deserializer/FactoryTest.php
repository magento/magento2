<?php
/**
 * Test Webapi Json Deserializer Request Rest Controller.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Rest\Request\Deserializer;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLogicExceptionEmptyRequestAdapter()
    {
        $this->setExpectedException('LogicException', 'Request deserializer adapter is not set.');
        $interpreterFactory = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory(
            $this->getMock('Magento\Framework\ObjectManagerInterface'),
            []
        );
        $interpreterFactory->get('contentType');
    }

    public function testGet()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $validInterpreterMock = $this->getMockBuilder(
            'Magento\Webapi\Controller\Rest\Request\Deserializer\Xml'
        )->disableOriginalConstructor()->getMock();

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($validInterpreterMock));

        $interpreterFactory = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }

    public function testGetMagentoWebapiException()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $this->setExpectedException(
            'Magento\Webapi\Exception',
            'Server cannot understand Content-Type HTTP header media type text_xml'
        );
        $interpreterFactory = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory(
            $this->getMock('Magento\Framework\ObjectManagerInterface'),
            $expectedMetadata
        );
        $interpreterFactory->get('text_xml');
    }

    public function testGetLogicExceptionInvalidRequestDeserializer()
    {
        $expectedMetadata = ['text_xml' => ['type' => 'text/xml', 'model' => 'Xml']];
        $invalidInterpreter = $this->getMockBuilder(
            'Magento\Webapi\Controller\Response\Rest\Renderer\Json'
        )->disableOriginalConstructor()->getMock();

        $this->setExpectedException(
            'LogicException',
            'The deserializer must implement "Magento\Webapi\Controller\Rest\Request\DeserializerInterface".'
        );
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock->expects($this->once())->method('get')->will($this->returnValue($invalidInterpreter));

        $interpreterFactory = new \Magento\Webapi\Controller\Rest\Request\Deserializer\Factory(
            $objectManagerMock,
            $expectedMetadata
        );
        $interpreterFactory->get('text/xml');
    }
}
