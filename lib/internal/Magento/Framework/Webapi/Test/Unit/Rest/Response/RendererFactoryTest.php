<?php
/**
 * Test Rest renderer factory class.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Test\Unit\Rest\Response;

class RendererFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Webapi\Rest\Response\RendererFactory */
    protected $_factory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_requestMock = $this->getMockBuilder(
            \Magento\Framework\Webapi\Rest\Request::class
        )->disableOriginalConstructor()->getMock();

        $renders = [
            'default' => ['type' => '*/*', 'model' => \Magento\Framework\Webapi\Rest\Response\Renderer\Json::class],
            'application_json' => [
                'type' => 'application/json',
                'model' => \Magento\Framework\Webapi\Rest\Response\Renderer\Json::class,
            ],
        ];

        $this->_factory = new \Magento\Framework\Webapi\Rest\Response\RendererFactory(
            $this->_objectManagerMock,
            $this->_requestMock,
            $renders
        );
    }

    /**
     * Test GET method.
     */
    public function testGet()
    {
        $acceptTypes = ['application/json'];

        /** Mock request getAcceptTypes method to return specified value. */
        $this->_requestMock->expects($this->once())->method('getAcceptTypes')->will($this->returnValue($acceptTypes));
        /** Mock renderer. */
        $rendererMock = $this->getMockBuilder(
            \Magento\Framework\Webapi\Rest\Response\Renderer\Json::class
        )->disableOriginalConstructor()->getMock();
        /** Mock object to return mocked renderer. */
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            \Magento\Framework\Webapi\Rest\Response\Renderer\Json::class
        )->will(
            $this->returnValue($rendererMock)
        );
        $this->_factory->get();
    }

    /**
     * Test GET method with wrong Accept HTTP Header.
     */
    public function testGetWithWrongAcceptHttpHeader()
    {
        /** Mock request to return empty Accept Types. */
        $this->_requestMock->expects($this->once())->method('getAcceptTypes')->will($this->returnValue(''));
        try {
            $this->_factory->get();
            $this->fail("Exception is expected to be raised");
        } catch (\Magento\Framework\Webapi\Exception $e) {
            $exceptionMessage = 'Server cannot match any of the given Accept HTTP header media type(s) '.
                'from the request: "" with media types from the config of response renderer.';
            $this->assertInstanceOf(\Magento\Framework\Webapi\Exception::class, $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Framework\Webapi\Exception::HTTP_NOT_ACCEPTABLE,
                $e->getHttpCode(),
                'HTTP code is invalid'
            );
        }
    }

    /**
     * Test GET method with wrong Renderer class.
     */
    public function testGetWithWrongRendererClass()
    {
        $acceptTypes = ['application/json'];
        /** Mock request getAcceptTypes method to return specified value. */
        $this->_requestMock->expects($this->once())->method('getAcceptTypes')->will($this->returnValue($acceptTypes));
        /** Mock object to return \Magento\Framework\DataObject */
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            \Magento\Framework\Webapi\Rest\Response\Renderer\Json::class
        )->will(
            $this->returnValue(new \Magento\Framework\DataObject())
        );

        $this->expectException(
            'LogicException',
            'The renderer must implement "Magento\Framework\Webapi\Rest\Response\RendererInterface".'
        );
        $this->_factory->get();
    }
}
