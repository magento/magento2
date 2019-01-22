<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Http;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Client;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Psr\Log\LoggerInterface;
use Zend_Http_Client;
use Zend_Http_Response;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    public function testCanSendRequest()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit\Framework\MockObject\MockObject $paymentLogger */
        $paymentLogger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpClient */
        $httpClient = $this->getMockBuilder(Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));

        $httpClient->expects($this->once())
            ->method('setRawData')
            ->with(
                '{"doSomeThing":{"foobar":"baz"}}',
                'application/json'
            );

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $request = [
            'payload_type' => 'doSomeThing',
            'foobar' => 'baz'
        ];
        // Authorize.net returns a BOM and refuses to fix it
        $response = pack('CCC', 0xef, 0xbb, 0xbf) . '{"foo":{"bar":"baz"}}';

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn($response);

        $paymentLogger->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => '{"foo":{"bar":"baz"}}']);

        /**
         * @var $apiClient Client
         */
        $apiClient = $objectManager->getObject(Client::class, [
            'httpClientFactory' => $httpClientFactory,
            'paymentLogger' => $paymentLogger
        ]);

        $result = $apiClient->placeRequest($this->getTransferObjectMock($request));

        $this->assertSame('baz', $result['foo']['bar']);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong in the payment gateway.
     */
    public function testExceptionIsThrownWhenEmptyResponseIsReceived()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit\Framework\MockObject\MockObject $paymentLogger */
        $paymentLogger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpClient */
        $httpClient = $this->getMockBuilder(Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));

        $httpClient->expects($this->once())
            ->method('setRawData')
            ->with(
                '{"doSomeThing":{"foobar":"baz"}}',
                'application/json'
            );

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn('');

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->callback(function ($e) {
                return $e instanceof \Exception
                    && $e->getMessage() === 'Invalid JSON was returned by the gateway';
            }));

        $request = [
            'payload_type' => 'doSomeThing',
            'foobar' => 'baz'
        ];

        $paymentLogger->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => '']);

        /**
         * @var $apiClient Client
         */
        $apiClient = $objectManager->getObject(Client::class, [
            'httpClientFactory' => $httpClientFactory,
            'logger' => $logger,
            'paymentLogger' => $paymentLogger
        ]);

        $apiClient->placeRequest($this->getTransferObjectMock($request));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong in the payment gateway.
     */
    public function testExceptionIsThrownWhenInvalidResponseIsReceived()
    {
        $objectManager = new ObjectManager($this);
        /** @var \PHPUnit\Framework\MockObject\MockObject $paymentLogger */
        $paymentLogger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpClientFactory */
        $httpClientFactory = $this->getMockBuilder(ZendClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpClient */
        $httpClient = $this->getMockBuilder(Zend_Http_Client::class)->getMock();
        /** @var \PHPUnit\Framework\MockObject\MockObject $httpResponse */
        $httpResponse = $this->getMockBuilder(Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClientFactory->method('create')->will($this->returnValue($httpClient));

        $httpClient->expects($this->once())
            ->method('setRawData')
            ->with(
                '{"doSomeThing":{"foobar":"baz"}}',
                'application/json'
            );

        $httpClient->expects($this->once())
            ->method('request')
            ->willReturn($httpResponse);

        $httpResponse->expects($this->once())
            ->method('getBody')
            ->willReturn('bad');

        $request = [
            'payload_type' => 'doSomeThing',
            'foobar' => 'baz'
        ];

        $paymentLogger->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => 'bad']);

        $logger->expects($this->once())
            ->method('critical')
            ->with($this->callback(function ($e) {
                return $e instanceof \Exception
                    && $e->getMessage() === 'Invalid JSON was returned by the gateway';
            }));

        /**
         * @var $apiClient Client
         */
        $apiClient = $objectManager->getObject(Client::class, [
            'httpClientFactory' => $httpClientFactory,
            'logger' => $logger,
            'paymentLogger' => $paymentLogger
        ]);

        $apiClient->placeRequest($this->getTransferObjectMock($request));
    }

    /**
     * Creates mock object for TransferInterface.
     *
     * @return TransferInterface|\PHPUnit\Framework\MockObject\MockObject
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
