<?php
/**
 * Test Rest renderer factory class.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Rest\Response\Renderer;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Webapi\Controller\Rest\Response\Renderer\Factory */
    protected $_factory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_requestMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_requestMock = $this->getMockBuilder(
            'Magento\Webapi\Controller\Rest\Request'
        )->disableOriginalConstructor()->getMock();

        $renders = [
            'default' => ['type' => '*/*', 'model' => 'Magento\Webapi\Controller\Rest\Response\Renderer\Json'],
            'application_json' => [
                'type' => 'application/json',
                'model' => 'Magento\Webapi\Controller\Rest\Response\Renderer\Json',
            ],
        ];

        $this->_factory = new \Magento\Webapi\Controller\Rest\Response\Renderer\Factory(
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
            'Magento\Webapi\Controller\Rest\Response\Renderer\Json'
        )->disableOriginalConstructor()->getMock();
        /** Mock object to return mocked renderer. */
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Webapi\Controller\Rest\Response\Renderer\Json'
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
        } catch (\Magento\Webapi\Exception $e) {
            $exceptionMessage = 'Server cannot match any of the given Accept HTTP header media type(s) '.
                'from the request: "" with media types from the config of response renderer.';
            $this->assertInstanceOf('Magento\Webapi\Exception', $e, 'Exception type is invalid');
            $this->assertEquals($exceptionMessage, $e->getMessage(), 'Exception message is invalid');
            $this->assertEquals(
                \Magento\Webapi\Exception::HTTP_NOT_ACCEPTABLE,
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
        /** Mock object to return \Magento\Framework\Object */
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Webapi\Controller\Rest\Response\Renderer\Json'
        )->will(
            $this->returnValue(new \Magento\Framework\Object())
        );

        $this->setExpectedException(
            'LogicException',
            'The renderer must implement "Magento\Webapi\Controller\Rest\Response\RendererInterface".'
        );
        $this->_factory->get();
    }
}
