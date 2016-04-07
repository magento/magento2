<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

class TunnelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRaw;

    protected function setUp()
    {
        $this->_request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->_response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
    }

    protected function tearDown()
    {
        $this->_request = null;
        $this->_response = null;
        $this->_objectManager = null;
    }

    public function testTunnelAction()
    {
        $fixture = uniqid();
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('ga')
            ->will($this->returnValue(urlencode(base64_encode(json_encode([1])))));
        $this->_request->expects($this->at(1))->method('getParam')->with('h')->will($this->returnValue($fixture));
        $tunnelResponse = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $httpClient = $this->getMock(
            'Magento\Framework\HTTP\ZendClient',
            ['setUri', 'setParameterGet', 'setConfig', 'request', 'getHeaders']
        );
        /** @var $helper \Magento\Backend\Helper\Dashboard\Data|\PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock(
            'Magento\Backend\Helper\Dashboard\Data',
            ['getChartDataHash'],
            [],
            '',
            false,
            false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $this->_objectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Backend\Helper\Dashboard\Data')
            ->will($this->returnValue($helper));
        $this->_objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento\Framework\HTTP\ZendClient')
            ->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('setUri')->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('setParameterGet')->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('setConfig')->will($this->returnValue($httpClient));
        $httpClient->expects($this->once())->method('request')->with('GET')->will($this->returnValue($tunnelResponse));
        $tunnelResponse->expects($this->any())->method('getHeaders')
            ->will($this->returnValue(['Content-type' => 'test_header']));
        $tunnelResponse->expects($this->any())->method('getBody')->will($this->returnValue('success_msg'));
        $this->_response->expects($this->any())->method('getBody')->will($this->returnValue('success_msg'));

        $controller = $this->_factory($this->_request, $this->_response);
        $this->resultRaw->expects($this->once())
            ->method('setHeader')
            ->with('Content-type', 'test_header')
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with('success_msg')
            ->willReturnSelf();

        $controller->execute();
        $this->assertEquals('success_msg', $controller->getResponse()->getBody());
    }

    public function testTunnelAction400()
    {
        $controller = $this->_factory($this->_request, $this->_response);

        $this->resultRaw->expects($this->once())
            ->method('setHeader')
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(400)
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with('Service unavailable: invalid request')
            ->willReturnSelf();

        $controller->execute();
    }

    public function testTunnelAction503()
    {
        $fixture = uniqid();
        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('ga')
            ->will($this->returnValue(urlencode(base64_encode(json_encode([1])))));
        $this->_request->expects($this->at(1))->method('getParam')->with('h')->will($this->returnValue($fixture));
        /** @var $helper \Magento\Backend\Helper\Dashboard\Data|\PHPUnit_Framework_MockObject_MockObject */
        $helper = $this->getMock(
            'Magento\Backend\Helper\Dashboard\Data',
            ['getChartDataHash'],
            [],
            '',
            false,
            false
        );
        $helper->expects($this->any())->method('getChartDataHash')->will($this->returnValue($fixture));

        $this->_objectManager->expects($this->at(0))
            ->method('get')
            ->with('Magento\Backend\Helper\Dashboard\Data')
            ->will($this->returnValue($helper));
        $exceptionMock = new \Exception();
        $this->_objectManager->expects($this->at(1))
            ->method('create')
            ->with('Magento\Framework\HTTP\ZendClient')
            ->will($this->throwException($exceptionMock));
        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $loggerMock->expects($this->once())->method('critical')->with($exceptionMock);
        $this->_objectManager->expects($this->at(2))
            ->method('get')
            ->with('Psr\Log\LoggerInterface')
            ->will($this->returnValue($loggerMock));

        $controller = $this->_factory($this->_request, $this->_response);

        $this->resultRaw->expects($this->once())
            ->method('setHeader')
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(503)
            ->willReturnSelf();
        $this->resultRaw->expects($this->once())
            ->method('setContents')
            ->with('Service unavailable: see error log for details')
            ->willReturnSelf();

        $controller->execute();
    }

    /**
     * Create the tested object
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Response\Http|null $response
     * @return \Magento\Backend\Controller\Adminhtml\Dashboard|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _factory($request, $response = null)
    {
        if (!$response) {
            /** @var $response \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
            $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
            $response->headersSentThrowsException = false;
        }
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $varienFront = $helper->getObject('Magento\Framework\App\FrontController');

        $arguments = [
            'request' => $request,
            'response' => $response,
            'objectManager' => $this->_objectManager,
            'frontController' => $varienFront,
        ];
        $this->resultRaw = $this->getMockBuilder('Magento\Framework\Controller\Result\Raw')
            ->disableOriginalConstructor()
            ->getMock();

        $resultRawFactory = $this->getMockBuilder('Magento\Framework\Controller\Result\RawFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRaw);
        $context = $helper->getObject('Magento\Backend\App\Action\Context', $arguments);
        return new \Magento\Backend\Controller\Adminhtml\Dashboard\Tunnel($context, $resultRawFactory);
    }
}
