<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Http;

use Magento\AuthorizenetAcceptjs\Gateway\Http\Client;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Zend_Http_Client;
use Zend_Http_Response;

/**
 * Test for Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Http\Client
 */
class ClientTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Logger
     */
    private $paymentLogger;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var Zend_Http_Client
     */
    private $httpClient;

    /**
     * @var Zend_Http_Response
     */
    private $httpResponse;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Client
     */
    private $apiClient;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->paymentLogger = $this->createMock(Logger::class);
        $this->httpClientFactory = $this->createMock(ZendClientFactory::class);
        $this->httpClient = $this->createMock(Zend_Http_Client::class);
        $this->httpResponse = $this->createMock(Zend_Http_Response::class);
        $this->httpClientFactory->method('create')->will($this->returnValue($this->httpClient));
        $this->httpClient->method('request')
            ->willReturn($this->httpResponse);
        /** @var MockObject $logger */
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->httpClient->expects($this->once())
            ->method('setRawData')
            ->with(
                '{"doSomeThing":{"foobar":"baz"}}',
                'application/json'
            );
        $this->apiClient = $this->objectManager->getObject(Client::class, [
            'httpClientFactory' => $this->httpClientFactory,
            'paymentLogger' => $this->paymentLogger,
            'logger' => $this->logger,
            'json' => new Json(),
        ]);
    }

    /**
     * @return void
     */
    public function testCanSendRequest()
    {
        // Assert the raw data was set on the client
        $request = [
            'payload_type' => 'doSomeThing',
            'foobar' => 'baz'
        ];
        // Authorize.net returns a BOM and refuses to fix it
        $response = pack('CCC', 0xef, 0xbb, 0xbf) . '{"foo":{"bar":"baz"}}';

        $this->httpResponse->method('getBody')
            ->willReturn($response);

        // Assert the logger was given the data
        $this->paymentLogger->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => '{"foo":{"bar":"baz"}}']);
        $result = $this->apiClient->placeRequest($this->getTransferObjectMock($request));
        $this->assertSame('baz', $result['foo']['bar']);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong in the payment gateway.
     *
     * @return void
     */
    public function testExceptionIsThrownWhenEmptyResponseIsReceived()
    {
        // Assert the client has the raw data set
        $this->httpResponse->method('getBody')
            ->willReturn('');

        // Assert the exception is given to the logger
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->callback(function ($e) {
                return $e instanceof \Exception
                    && $e->getMessage() === 'Invalid JSON was returned by the gateway';
            }));

        $request = [
            'payload_type' => 'doSomeThing',
            'foobar' => 'baz',
        ];

        // Assert the logger was given the data
        $this->paymentLogger->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => '']);

        $this->apiClient->placeRequest($this->getTransferObjectMock($request));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Something went wrong in the payment gateway.
     *
     * @return void
     */
    public function testExceptionIsThrownWhenInvalidResponseIsReceived()
    {
        // Assert the client was given the raw data
        $this->httpResponse->method('getBody')
            ->willReturn('bad');

        $request = [
            'payload_type' => 'doSomeThing',
            'foobar' => 'baz',
        ];

        // Assert the logger was given the data
        $this->paymentLogger->expects($this->once())
            ->method('debug')
            ->with(['request' => $request, 'response' => 'bad']);

        // Assert the exception was given to the logger
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->callback(function ($e) {
                return $e instanceof \Exception
                    && $e->getMessage() === 'Invalid JSON was returned by the gateway';
            }));

        $this->apiClient->placeRequest($this->getTransferObjectMock($request));
    }

    /**
     * Creates mock object for TransferInterface.
     * @param array $data
     * @return TransferInterface|MockObject
     */
    private function getTransferObjectMock(array $data)
    {
        $transferObjectMock = $this->createMock(TransferInterface::class);
        $transferObjectMock->method('getBody')
            ->willReturn($data);

        return $transferObjectMock;
    }
}
