<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Http\Client;

use Laminas\Http\Exception\RuntimeException;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
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
     * @var LaminasClientFactory|MockObject
     */
    protected $clientFactoryMock;

    /**
     * @var LaminasClient|MockObject
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

        $this->clientFactoryMock = $this->getMockBuilder(LaminasClientFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientMock = $this->getMockBuilder(LaminasClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transferObjectMock = $this->getMockBuilder(TransferInterface::class)
            ->getMockForAbstractClass();

        $this->model = new Zend(
            $this->clientFactoryMock,
            $this->loggerMock,
            $this->converterMock
        );
    }

    public function testPlaceRequest()
    {
        $this->setClientTransferObjects();
        $responseBody = 'Response body content';

        $httpResponseMock = $this->getMockBuilder(
            Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $httpResponseMock->expects($this->once())->method('getBody')->willReturn($responseBody);

        $this->clientMock->expects($this->once())->method('send')->willReturn($httpResponseMock);
        $this->converterMock->expects($this->once())->method('convert')->with($responseBody);
        $this->clientFactoryMock->expects($this->once())
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
            ->method('send')
            ->willThrowException(new RuntimeException());

        $this->converterMock->expects($this->never())->method('convert');

        $this->clientFactoryMock->expects($this->once())
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

        $httpResponseMock = $this->getMockBuilder(
            Response::class
        )->disableOriginalConstructor()
            ->getMock();
        $httpResponseMock->expects($this->once())->method('getBody')->willReturn($responseBody);

        $this->clientMock->expects($this->once())->method('send')->willReturn($httpResponseMock);
        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($responseBody)
            ->willThrowException(new ConverterException(__()));

        $this->clientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->clientMock);

        $this->model->placeRequest($this->transferObjectMock);
    }

    private function setClientTransferObjects()
    {
        $config = ['key1' => 'value1', 'key2' => 'value2'];
        $method = Request::METHOD_POST;
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

        $this->clientMock->expects($this->once())->method('setOptions')->with($config)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setMethod')->with($method)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setParameterPost')->with([$body])->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setHeaders')->with($headers)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setUrlEncodeBody')->with($shouldEncode)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setUri')->with($uri)->willReturnSelf();
    }
}
