<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Http\Client;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\Client\Zend;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ZendTest extends TestCase
{
    /** @var Zend */
    protected $model;

    /**
     * @var ConverterInterface|MockObject
     */
    protected $converterMock;

    /**
     * @var ZendClientFactory|MockObject
     */
    protected $zendClientFactoryMock;

    /**
     * @var ZendClient|MockObject
     */
    protected $clientMock;

    /**
     * @var TransferInterface|MockObject
     */
    protected $transferObjectMock;

    /**
     * @var Logger|MockObject
     */
    protected $loggerMock;

    protected function setUp(): void
    {
        $this->converterMock = $this->getMockBuilder(ConverterInterface::class)
            ->getMockForAbstractClass();

        $this->zendClientFactoryMock = $this->getMockBuilder(ZendClientFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientMock = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transferObjectMock = $this->getMockBuilder(TransferInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Zend(
            $this->zendClientFactoryMock,
            $this->loggerMock,
            $this->converterMock
        );
    }

    public function testPlaceRequest()
    {
        $this->setClientTransferObjects();
        $responseBody = 'Response body content';

        $zendHttpResponseMock = $this->getMockBuilder(
            \Zend_Http_Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $zendHttpResponseMock->expects($this->once())->method('getBody')->willReturn($responseBody);

        $this->clientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);
        $this->converterMock->expects($this->once())->method('convert')->with($responseBody);
        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->clientMock);

        $this->model->placeRequest($this->transferObjectMock);
    }

    /**
     * Tests failing client gateway request
     */
    public function testPlaceRequestClientFail()
    {
        $this->expectException(ClientException::class);
        $this->setClientTransferObjects();

        $this->clientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new \Zend_Http_Client_Exception());

        $this->converterMock->expects($this->never())->method('convert');

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->clientMock);

        $this->model->placeRequest($this->transferObjectMock);
    }

    /**
     * Tests failing response converting
     */
    public function testPlaceRequestConvertResponseFail()
    {
        $this->expectException(ConverterException::class);
        $this->setClientTransferObjects();
        $responseBody = 'Response body content';

        $zendHttpResponseMock = $this->getMockBuilder(
            \Zend_Http_Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $zendHttpResponseMock->expects($this->once())->method('getBody')->willReturn($responseBody);

        $this->clientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);
        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($responseBody)
            ->willThrowException(new ConverterException(__()));

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->clientMock);

        $this->model->placeRequest($this->transferObjectMock);
    }

    private function setClientTransferObjects()
    {
        $config = ['key1' => 'value1', 'key2' => 'value2'];
        $method = \Zend_Http_Client::POST;
        $headers = ['key1' => 'value1', 'key2' => 'value2'];
        $body = 'Body content';
        $uri = 'https://example.com/listener';
        $shouldEncode = true;

        $this->transferObjectMock->expects($this->once())->method('getClientConfig')->willReturn($config);
        $this->transferObjectMock->expects($this->atLeastOnce())->method('getMethod')->willReturn($method);
        $this->transferObjectMock->expects($this->once())->method('getHeaders')->willReturn($headers);
        $this->transferObjectMock->expects($this->atLeastOnce())->method('getBody')->willReturn($body);
        $this->transferObjectMock->expects($this->once())->method('shouldEncode')->willReturn($shouldEncode);
        $this->transferObjectMock->expects(static::atLeastOnce())->method('getUri')->willReturn($uri);

        $this->clientMock->expects($this->once())->method('setConfig')->with($config)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setMethod')->with($method)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setParameterPost')->with($body)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setHeaders')->with($headers)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setUrlEncodeBody')->with($shouldEncode)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setUri')->with($uri)->willReturnSelf();
    }
}
