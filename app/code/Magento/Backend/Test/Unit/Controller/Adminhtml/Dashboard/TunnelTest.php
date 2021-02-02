<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Controller\Adminhtml\Dashboard;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TunnelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_response;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRaw;

    protected function setUp(): void
    {
        $this->_request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->_response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
    }

    protected function tearDown(): void
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
            ->willReturn(urlencode(base64_encode(json_encode([1]))));
        $this->_request->expects($this->at(1))->method('getParam')->with('h')->willReturn($fixture);
        $tunnelResponse = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $httpClient = $this->createPartialMock(
            \Magento\Framework\HTTP\ZendClient::class,
            ['setUri', 'setParameterGet', 'setConfig', 'request', 'getHeaders']
        );
        /** @var $helper \Magento\Backend\Helper\Dashboard\Data|\PHPUnit\Framework\MockObject\MockObject */
        $helper = $this->createPartialMock(\Magento\Backend\Helper\Dashboard\Data::class, ['getChartDataHash']);
        $helper->expects($this->any())->method('getChartDataHash')->willReturn($fixture);

        $this->_objectManager->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Backend\Helper\Dashboard\Data::class)
            ->willReturn($helper);
        $this->_objectManager->expects($this->at(1))
            ->method('create')
            ->with(\Magento\Framework\HTTP\ZendClient::class)
            ->willReturn($httpClient);
        $httpClient->expects($this->once())->method('setUri')->willReturn($httpClient);
        $httpClient->expects($this->once())->method('setParameterGet')->willReturn($httpClient);
        $httpClient->expects($this->once())->method('setConfig')->willReturn($httpClient);
        $httpClient->expects($this->once())->method('request')->with('GET')->willReturn($tunnelResponse);
        $tunnelResponse->expects($this->any())->method('getHeaders')
            ->willReturn(['Content-type' => 'test_header']);
        $tunnelResponse->expects($this->any())->method('getBody')->willReturn('success_msg');
        $this->_response->expects($this->any())->method('getBody')->willReturn('success_msg');

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
            ->willReturn(urlencode(base64_encode(json_encode([1]))));
        $this->_request->expects($this->at(1))->method('getParam')->with('h')->willReturn($fixture);
        /** @var $helper \Magento\Backend\Helper\Dashboard\Data|\PHPUnit\Framework\MockObject\MockObject */
        $helper = $this->createPartialMock(\Magento\Backend\Helper\Dashboard\Data::class, ['getChartDataHash']);
        $helper->expects($this->any())->method('getChartDataHash')->willReturn($fixture);

        $this->_objectManager->expects($this->at(0))
            ->method('get')
            ->with(\Magento\Backend\Helper\Dashboard\Data::class)
            ->willReturn($helper);
        $exceptionMock = new \Exception();
        $this->_objectManager->expects($this->at(1))
            ->method('create')
            ->with(\Magento\Framework\HTTP\ZendClient::class)
            ->will($this->throwException($exceptionMock));
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $loggerMock->expects($this->once())->method('critical')->with($exceptionMock);
        $this->_objectManager->expects($this->at(2))
            ->method('get')
            ->with(\Psr\Log\LoggerInterface::class)
            ->willReturn($loggerMock);

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
     * @return \Magento\Backend\Controller\Adminhtml\Dashboard|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function _factory($request, $response = null)
    {
        if (!$response) {
            /** @var $response \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
            $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
            $response->headersSentThrowsException = false;
        }
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $varienFront = $helper->getObject(\Magento\Framework\App\FrontController::class);

        $arguments = [
            'request' => $request,
            'response' => $response,
            'objectManager' => $this->_objectManager,
            'frontController' => $varienFront,
        ];
        $this->resultRaw = $this->getMockBuilder(\Magento\Framework\Controller\Result\Raw::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultRawFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RawFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRawFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->resultRaw);
        $context = $helper->getObject(\Magento\Backend\App\Action\Context::class, $arguments);
        return new \Magento\Backend\Controller\Adminhtml\Dashboard\Tunnel($context, $resultRawFactory);
    }
}
