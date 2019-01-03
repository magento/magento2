<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Model\AuthorizenetGateway;

use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\ApiClient;
use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\PayloadConverter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ApiClientTest extends \PHPUnit\Framework\TestCase
{
    public function testCanSendRequest()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->getMockBuilder(\Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));

        $httpClient->expects($this->once())
            ->method('setParameterPost')
            ->with('<doSomeThing><foobar>baz</foobar></doSomeThing>')
            ->willReturn([]);

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
            'payloadConverter' => $objectManager->getObject(PayloadConverter::class)
        ]);

        $result = $apiClient->sendRequest([
            PayloadConverter::PAYLOAD_TYPE => 'doSomeThing',
            'foobar' => 'baz'
        ]);

        $this->assertSame('foo', $result[PayloadConverter::PAYLOAD_TYPE]);
        $this->assertSame('baz', $result['bar']);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong in the payment gateway.
     */
    public function testExceptionIsThrownWhenEmptyResponseIsReceived()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->getMockBuilder(\Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));

        $httpClient->expects($this->once())
            ->method('setParameterPost')
            ->with('<doSomeThing><foobar>baz</foobar></doSomeThing>')
            ->willReturn([]);

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
            'payloadConverter' => $objectManager->getObject(PayloadConverter::class)
        ]);

        $apiClient->sendRequest([
            PayloadConverter::PAYLOAD_TYPE => 'doSomeThing',
            'foobar' => 'baz'
        ]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong in the payment gateway.
     */
    public function testExceptionIsThrownWhenInvalidResponseIsReceived()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(\Magento\Framework\HTTP\ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->getMockBuilder(\Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));

        $httpClient->expects($this->once())
            ->method('setParameterPost')
            ->with('<doSomeThing><foobar>baz</foobar></doSomeThing>')
            ->willReturn([]);

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn('bad');

        /**
         * @var $apiClient ApiClient
         */
        $apiClient = $objectManager->getObject(ApiClient::class, [
            'httpClientFactory' => $httpClientFactory,
            'payloadConverter' => $objectManager->getObject(PayloadConverter::class)
        ]);

        $apiClient->sendRequest([
            PayloadConverter::PAYLOAD_TYPE => 'doSomeThing',
            'foobar' => 'baz'
        ]);
    }
}
