<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\Client\Zend;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * Class ZendTest
 */
class ZendTest extends \PHPUnit_Framework_TestCase
{
    /** @var Zend */
    protected $model;

    /**
     * @var ConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    /**
     * @var ZendClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $zendClientFactoryMock;

    /**
     * @var ZendClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferObjectMock;

    protected function setUp()
    {
        $this->converterMock = $this->getMockBuilder('Magento\Payment\Gateway\Http\ConverterInterface')
            ->getMockForAbstractClass();

        $this->zendClientFactoryMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClientFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientMock = $this->getMockBuilder('Magento\Framework\HTTP\ZendClient')
            ->disableOriginalConstructor()
            ->getMock();

        $this->transferObjectMock = $this->getMockBuilder('Magento\Payment\Gateway\Http\TransferInterface')
            ->getMockForAbstractClass();

        $this->model = new Zend($this->zendClientFactoryMock, $this->converterMock);
    }

    public function testPlaceRequest()
    {
        $this->setClientTransferObjects();
        $responseBody = 'Response body content';

        $zendHttpResponseMock = $this->getMockBuilder('Zend_Http_Response')->disableOriginalConstructor()->getMock();
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
     *
     * @expectedException  \Magento\Payment\Gateway\Http\ClientException
     */
    public function testPlaceRequestClientFail()
    {
        $this->setClientTransferObjects();

        $this->clientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new \Zend_Http_Client_Exception);

        $this->converterMock->expects($this->never())->method('convert');

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->clientMock);

        $this->model->placeRequest($this->transferObjectMock);
    }

    /**
     * Tests failing response converting
     *
     * @expectedException  \Magento\Payment\Gateway\Http\ConverterException
     */
    public function testPlaceRequestConvertResponseFail()
    {
        $this->setClientTransferObjects();
        $responseBody = 'Response body content';

        $zendHttpResponseMock = $this->getMockBuilder('Zend_Http_Response')->disableOriginalConstructor()->getMock();
        $zendHttpResponseMock->expects($this->once())->method('getBody')->willReturn($responseBody);

        $this->clientMock->expects($this->once())->method('request')->willReturn($zendHttpResponseMock);
        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($responseBody)
            ->willThrowException(new \Magento\Payment\Gateway\Http\ConverterException(__()));

        $this->zendClientFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->clientMock);

        $this->model->placeRequest($this->transferObjectMock);
    }

    private function setClientTransferObjects()
    {
        $config = ['key1' => 'value1', 'key2' => 'value2'];
        $method = 'methodName';
        $headers = ['key1' => 'value1', 'key2' => 'value2'];
        $body = 'Body content';
        $shouldEncode = true;

        $this->transferObjectMock->expects($this->once())->method('getClientConfig')->willReturn($config);
        $this->transferObjectMock->expects($this->once())->method('getMethod')->willReturn($method);
        $this->transferObjectMock->expects($this->once())->method('getHeaders')->willReturn($headers);
        $this->transferObjectMock->expects($this->once())->method('getBody')->willReturn($body);
        $this->transferObjectMock->expects($this->once())->method('shouldEncode')->willReturn($shouldEncode);

        $this->clientMock->expects($this->once())->method('setConfig')->with($config)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setMethod')->with($method)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setParameterPost')->with($body)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setHeaders')->with($headers)->willReturnSelf();
        $this->clientMock->expects($this->once())->method('setUrlEncodeBody')->with($shouldEncode)->willReturnSelf();
    }
}
