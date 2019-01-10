<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Http;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Client;
use Magento\AuthorizenetAcceptjs\Gateway\Http\Payload\Converter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    public function testCanSendRequest()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->method('setRawData')
            ->with(
                '<doSomeThing xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><foobar>baz</foobar></doSomeThing>',
                'text/xml'
            )
            ->willReturn([]);

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $request = [
            Converter::PAYLOAD_TYPE => 'doSomeThing',
            'foobar' => 'baz'
        ];
        $response = '<foo><bar>baz</bar></foo>';

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn($response);

        $loggerMock->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => $response]);

        /**
         * @var $apiClient Client
         */
        $apiClient = $objectManager->getObject(Client::class, [
            'httpClientFactory' => $httpClientFactory,
            'payloadConverter' => $objectManager->getObject(Converter::class),
            'logger' => $loggerMock
        ]);

        $result = $apiClient->placeRequest($this->getTransferObjectMock($request));

        $this->assertSame('foo', $result[Converter::PAYLOAD_TYPE]);
        $this->assertSame('baz', $result['bar']);
    }

    public function testExceptionIsThrownWhenEmptyResponseIsReceived()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(__('Something went wrong in the payment gateway.')->__toString());

        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->method('setRawData')
            ->with(
                '<doSomeThing xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><foobar>baz</foobar></doSomeThing>',
                'text/xml'
            )
            ->willReturn([]);

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn('');

        $request = [
            Converter::PAYLOAD_TYPE => 'doSomeThing',
            'foobar' => 'baz'
        ];

        $loggerMock->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => '']);

        /**
         * @var $apiClient Client
         */
        $apiClient = $objectManager->getObject(Client::class, [
            'httpClientFactory' => $httpClientFactory,
            'payloadConverter' => $objectManager->getObject(Converter::class),
            'logger' => $loggerMock
        ]);

        $apiClient->placeRequest($this->getTransferObjectMock($request));
    }

    public function testExceptionIsThrownWhenInvalidResponseIsReceived()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage(__('Something went wrong in the payment gateway.')->__toString());

        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->method('setRawData')
            ->with(
                '<doSomeThing xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"><foobar>baz</foobar></doSomeThing>',
                'text/xml'
            )
            ->willReturn([]);

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn('bad');

        $request = [
            Converter::PAYLOAD_TYPE => 'doSomeThing',
            'foobar' => 'baz'
        ];

        $loggerMock->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => 'bad']);

        /**
         * @var $apiClient Client
         */
        $apiClient = $objectManager->getObject(Client::class, [
            'httpClientFactory' => $httpClientFactory,
            'payloadConverter' => $objectManager->getObject(Converter::class),
            'logger' => $loggerMock
        ]);

        $apiClient->placeRequest($this->getTransferObjectMock($request));
    }

    /**
     * Creates mock object for TransferInterface.
     *
     * @return TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTransferObjectMock(array $data)
    {
        $transferObjectMock = $this->createMock(TransferInterface::class);
        $transferObjectMock->expects($this->once())
            ->method('getBody')
            ->willReturn($data);

        return $transferObjectMock;
    }
}
