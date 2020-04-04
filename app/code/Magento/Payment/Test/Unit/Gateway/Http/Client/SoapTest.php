<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Gateway\Http\Client;

use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Payment\Gateway\Http\Client\Soap;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SoapTest extends TestCase
{
    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var ClientFactory|MockObject
     */
    private $clientFactoryMock;

    /**
     * @var ConverterInterface|MockObject
     */
    private $converterMock;

    /**
     * @var \SoapClient|MockObject
     */
    private $clientMock;

    /**
     * @var Soap
     */
    private $gatewayClient;

    protected function setUp()
    {
        $this->loggerMock = $this->getMockBuilder(
            Logger::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactoryMock = $this->getMockBuilder(
            ClientFactory::class
        )->getMock();
        $this->converterMock = $this->getMockBuilder(
            ConverterInterface::class
        )->getMockForAbstractClass();
        $this->clientMock = $this->getMockBuilder(\SoapClient::class)
            ->setMethods(['__setSoapHeaders', '__soapCall', '__getLastRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->gatewayClient = new Soap(
            $this->loggerMock,
            $this->clientFactoryMock,
            $this->converterMock
        );
    }

    public function testPlaceRequest()
    {
        $expectedResult = [
            'result' => []
        ];
        $soapResult = new \StdClass();

        $this->loggerMock->expects(static::at(0))
            ->method('debug')
            ->with(
                ['request' => ['body']]
            );
        $this->clientFactoryMock->expects(static::once())
            ->method('create')
            ->with('path_to_wsdl', ['trace' => true])
            ->willReturn($this->clientMock);
        $transferObject = $this->getTransferObject();
        $transferObject->expects(static::any())
            ->method('__setSoapHeaders')
            ->with(['headers']);
        $this->clientMock->expects(static::once())
            ->method('__soapCall')
            ->with('soapMethod', [['body']])
            ->willReturn($soapResult);
        $this->converterMock->expects(static::once())
            ->method('convert')
            ->with($soapResult)
            ->willReturn($expectedResult);
        $this->loggerMock->expects(static::at(1))
            ->method('debug')
            ->with(['response' => $expectedResult]);

        static::assertEquals(
            $expectedResult,
            $this->gatewayClient->placeRequest($transferObject)
        );
    }

    public function testPlaceRequestSoapException()
    {
        $this->expectException('Exception');

        $this->loggerMock->expects(static::at(0))
            ->method('debug')
            ->with(
                ['request' => ['body']]
            );
        $this->clientFactoryMock->expects(static::once())
            ->method('create')
            ->with('path_to_wsdl', ['trace' => true])
            ->willReturn($this->clientMock);
        $transferObject = $this->getTransferObject();
        $transferObject->expects(static::any())
            ->method('__setSoapHeaders')
            ->with(['headers']);
        $this->clientMock->expects(static::once())
            ->method('__soapCall')
            ->with('soapMethod', [['body']])
            ->willThrowException(new \Exception());
        $this->clientMock->expects(static::once())
            ->method('__getLastRequest')
            ->willReturn('RequestTrace');
        $this->loggerMock->expects(static::at(1))
            ->method('debug')
            ->with(
                ['trace' => 'RequestTrace']
            );

        $this->gatewayClient->placeRequest($transferObject);
    }

    /**
     * Returns prepared transfer object
     *
     * @return MockObject
     */
    private function getTransferObject()
    {
        $transferObject = $this->getMockBuilder(
            TransferInterface::class
        )->setMethods(['__setSoapHeaders', 'getBody', 'getClientConfig', 'getMethod'])->getMockForAbstractClass();

        $transferObject->expects(static::any())
            ->method('getBody')
            ->willReturn(['body']);
        $transferObject->expects(static::any())
            ->method('getClientConfig')
            ->willReturn(['wsdl' => 'path_to_wsdl']);
        $transferObject->expects(static::any())
            ->method('getMethod')
            ->willReturn('soapMethod');

        return $transferObject;
    }
}
