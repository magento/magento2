<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Model\AuthorizenetGateway;

use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\ApiClient;
use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\RequestFactory;
use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\Request;
use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\Response;
use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\ResponseFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ApiClientTest extends \PHPUnit\Framework\TestCase
{
    public function testCanCreateAuthenticatedRequest()
    {
        $objectManager = new ObjectManager($this);
        /** @var Request $request */
        $request = $objectManager->getObject(Request::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject $requestFactory */
        $requestFactory = $this->getMockBuilder(RequestFactory::class)->getMock();
        $requestFactory->method('create')->will($this->returnValue($request));
        /** @var \PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMock();

        $config->method('getValue')
            ->will($this->returnCallback(function ($field) {
                $config = [
                    'payment/authorizenet_acceptjs/login' => 'mylogin',
                    'payment/authorizenet_acceptjs/trans_key' => 'mykey'
                ];
                return $config[$field];
            }));

        /**
         * @var $apiClient ApiClient
         */
        $apiClient = $objectManager->getObject(ApiClient::class, [
            'requestFactory' => $requestFactory,
            'scopeConfig' => $config
        ]);
        $request = $apiClient->createAuthenticatedRequest();

        $this->assertSame('mylogin', $request->getData('merchantAuthentication')['login']);
        $this->assertSame('mykey', $request->getData('merchantAuthentication')['transactionKey']);
    }

    public function testCanSendRequest()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->getMockBuilder(\Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));
        /** @var Request $request */
        $request = $objectManager->getObject(Request::class);
        /** @var Response $response */
        $response = $objectManager->getObject(Response::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject $responseFactory */
        $responseFactory = $this->getMockBuilder(ResponseFactory::class)->getMock();
        $responseFactory->method('create')->will($this->returnValue($response));

        $request->setData(Request::REQUEST_TYPE, 'doSomeThing');
        $request->setData('foobar', 'baz');

        $httpClient->expects($this->once())
            ->method('setParameterPost')
            ->with('<doSomeThing><foobar>baz</foobar></doSomeThing>')
            ->willReturn($response);

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn('<foo><bar>baz</bar></foo>');

        /**
         * @var $apiClient ApiClient
         */
        $apiClient = $objectManager->getObject(ApiClient::class, [
            'httpClientFactory' => $httpClientFactory,
            'responseFactory' => $responseFactory
        ]);

        $result = $apiClient->sendRequest($request);

        $this->assertSame('foo', $result->getData(Response::RESPONSE_TYPE));
        $this->assertSame('baz', $result->getData('bar'));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong in the payment gateway.
     */
    public function testExceptionIsThrownWhenEmptyResponseIsReceived()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->getMockBuilder(\Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));
        /** @var Request $request */
        $request = $objectManager->getObject(Request::class);
        /** @var Response $response */
        $response = $objectManager->getObject(Response::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject $responseFactory */
        $responseFactory = $this->getMockBuilder(ResponseFactory::class)->getMock();
        $responseFactory->method('create')->will($this->returnValue($response));

        $request->setData(Request::REQUEST_TYPE, 'doSomeThing');
        $request->setData('foobar', 'baz');

        $httpClient->expects($this->once())
            ->method('setParameterPost')
            ->with('<doSomeThing><foobar>baz</foobar></doSomeThing>')
            ->willReturn($response);

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn('');

        /**
         * @var $apiClient ApiClient
         */
        $apiClient = $objectManager->getObject(ApiClient::class, [
            'httpClientFactory' => $httpClientFactory,
            'responseFactory' => $responseFactory
        ]);

        $apiClient->sendRequest($request);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong in the payment gateway.
     */
    public function testExceptionIsThrownWhenInvalidResponseIsReceived()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->getMockBuilder(\Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));
        /** @var Request $request */
        $request = $objectManager->getObject(Request::class);
        /** @var Response $response */
        $response = $objectManager->getObject(Response::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject $responseFactory */
        $responseFactory = $this->getMockBuilder(ResponseFactory::class)->getMock();
        $responseFactory->method('create')->will($this->returnValue($response));

        $request->setData(Request::REQUEST_TYPE, 'doSomeThing');
        $request->setData('foobar', 'baz');

        $httpClient->expects($this->once())
            ->method('setParameterPost')
            ->with('<doSomeThing><foobar>baz</foobar></doSomeThing>')
            ->willReturn($response);

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn('invalid');

        /**
         * @var $apiClient ApiClient
         */
        $apiClient = $objectManager->getObject(ApiClient::class, [
            'httpClientFactory' => $httpClientFactory,
            'responseFactory' => $responseFactory
        ]);

        $apiClient->sendRequest($request);
    }
}
