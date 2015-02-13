<?php
/**
 * Test Rest response controller.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Rest;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Webapi\Rest\Response */
    protected $_responseRest;

    /** @var \Magento\Framework\App\State */
    protected $_appStateMock;

    /** @var \Magento\Framework\Webapi\Rest\Response\Renderer\Xml */
    protected $_rendererMock;

    /** @var \Magento\Framework\Webapi\ErrorProcessor */
    protected $_errorProcessorMock;

    protected function setUp()
    {
        /** Mock all objects required for SUT. */
        $this->_rendererMock = $this->getMockBuilder(
            'Magento\Framework\Webapi\Rest\Response\Renderer\Json'
        )->disableOriginalConstructor()->getMock();
        $rendererFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Webapi\Rest\Response\RendererFactory'
        )->disableOriginalConstructor()->getMock();
        $rendererFactoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_rendererMock));
        $this->_errorProcessorMock = $this->getMockBuilder('Magento\Framework\Webapi\ErrorProcessor')
            ->disableOriginalConstructor()->getMock();
        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);

        /** Init SUP. */
        $this->_responseRest = new \Magento\Framework\Webapi\Rest\Response(
            $rendererFactoryMock,
            $this->_errorProcessorMock,
            $this->_appStateMock
        );
        $this->_responseRest->headersSentThrowsException = false;
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_responseRest);
        unset($this->_appStateMock);
        unset($this->_rendererMock);
        unset($this->_errorProcessorMock);
        parent::tearDown();
    }

    /**
     * Test setException method with \Magento\Framework\Webapi\Exception.
     */
    public function testSetWebapiExceptionException()
    {
        /** Init \Magento\Framework\Webapi\Exception */
        $apiException = new \Magento\Framework\Webapi\Exception(
            'Exception message.',
            0,
            \Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED
        );
        $this->_responseRest->setException($apiException);
        /** Assert that \Magento\Framework\Webapi\Exception was set and presented in the list. */
        $this->assertTrue(
            $this->_responseRest->hasExceptionOfType('Magento\Framework\Webapi\Exception'),
            'Magento\Framework\Webapi\Exception was not set.'
        );
    }

    /**
     * Test sendResponse method with internal error exception during messages rendering.
     */
    public function testSendResponseRenderMessagesException()
    {
        /** Init logic exception. */
        $logicException = new \LogicException();
        /** Mock error processor to throw \LogicException in maskException method. */
        $this->_errorProcessorMock->expects(
            $this->any()
        )->method(
            'maskException'
        )->will(
            $this->throwException($logicException)
        );
        /** Assert that renderException method will be executed once with specified parameters. */
        $this->_errorProcessorMock->expects(
            $this->once()
        )->method(
            'renderException'
        )->with(
            $logicException,
            \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
        );
        /** Set exception to Rest response to get in to the _renderMessages method. */
        $this->_responseRest->setException(new \Magento\Framework\Webapi\Exception('Message.'));
        $this->_responseRest->sendResponse();
    }

    /**
     * Test sendResponse method with HTTP Not Acceptable error exception during messages rendering.
     */
    public function testSendResponseRenderMessagesHttpNotAcceptable()
    {
        $exception = new \Magento\Framework\Webapi\Exception(
            'Message',
            0,
            \Magento\Framework\Webapi\Exception::HTTP_NOT_ACCEPTABLE
        );
        /** Mock error processor to throw \LogicException in maskException method. */
        $this->_errorProcessorMock->expects(
            $this->any()
        )->method(
            'maskException'
        )->will(
            $this->throwException($exception)
        );
        /** Assert that renderException method will be executed once with specified parameters. */
        $this->_errorProcessorMock->expects(
            $this->once()
        )->method(
            'renderException'
        )->with(
            $exception,
            \Magento\Framework\Webapi\Exception::HTTP_NOT_ACCEPTABLE
        );
        /** Set exception to Rest response to get in to the _renderMessages method. */
        $this->_responseRest->setException(
            new \Magento\Framework\Webapi\Exception('Message.', 0, \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST)
        );
        $this->_responseRest->sendResponse();
    }

    /**
     * Test sendResponse method with exception rendering.
     */
    public function testSendResponseWithException()
    {
        /** Mock all required objects. */
        $this->_rendererMock->expects(
            $this->any()
        )->method(
            'getMimeType'
        )->will(
            $this->returnValue('application/json')
        );
        $this->_rendererMock->expects(
            $this->any()
        )->method(
            'render'
        )->will(
            $this->returnCallback([$this, 'callbackForSendResponseTest'], $this->returnArgument(0))
        );
        $exceptionMessage = 'Message';
        $exceptionHttpCode = \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST;
        $exception = new \Magento\Framework\Webapi\Exception($exceptionMessage, 0, $exceptionHttpCode);
        $this->_errorProcessorMock->expects(
            $this->any()
        )->method(
            'maskException'
        )->will(
            $this->returnValue($exception)
        );
        $this->_responseRest->setException($exception);
        /** Start output buffering. */
        ob_start();
        $this->_responseRest->sendResponse();
        /** Clear output buffering. */
        ob_end_clean();
        $actualResponse = $this->_responseRest->getBody();
        $expectedResult = '{"message":"' .
            $exceptionMessage .
            '"}';
        $this->assertStringStartsWith($expectedResult, $actualResponse, 'Response body is invalid');
    }

    /**
     * Callback for testSendResponseRenderMessages method.
     *
     * @param $data
     * @return string
     */
    public function callbackForSendResponseTest($data)
    {
        return json_encode($data);
    }

    /**
     * Test sendResponse method without any exception
     */
    public function testSendResponseSuccessHandling()
    {
        $this->_responseRest->sendResponse();
        $this->assertTrue($this->_responseRest->getHttpResponseCode() == \Magento\Framework\Webapi\Response::HTTP_OK);
    }
}
