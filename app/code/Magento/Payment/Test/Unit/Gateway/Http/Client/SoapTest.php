<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @var MockObject
     */
    private $logger;

    /**
     * @var MockObject
     */
    private $clientFactory;

    /**
     * @var MockObject
     */
    private $converter;

    /**
     * @var MockObject
     */
    private $client;

    /**
     * @var Soap
     */
    private $gatewayClient;

    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(
            Logger::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory = $this->getMockBuilder(
            ClientFactory::class
        )->getMock();
        $this->converter = $this->getMockBuilder(
            ConverterInterface::class
        )->getMockForAbstractClass();
        $this->client = $this->getMockBuilder(\SoapClient::class)
            ->setMethods(['__setSoapHeaders', '__soapCall', '__getLastRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->gatewayClient = new Soap(
            $this->logger,
            $this->clientFactory,
            $this->converter
        );
    }

    public function testPlaceRequest()
    {
        $expectedResult = [
            'result' => []
        ];
        $soapResult = new \StdClass();

        $this->logger->expects(static::at(0))
            ->method('debug')
            ->with(
                ['request' => ['body']]
            );
        $this->clientFactory->expects(static::once())
            ->method('create')
            ->with('path_to_wsdl', ['trace' => true])
            ->willReturn($this->client);
        $transferObject = $this->getTransferObject();
        $transferObject->expects(static::any())
            ->method('__setSoapHeaders')
            ->with(['headers']);
        $this->client->expects(static::once())
            ->method('__soapCall')
            ->with('soapMethod', [['body']])
            ->willReturn($soapResult);
        $this->converter->expects(static::once())
            ->method('convert')
            ->with($soapResult)
            ->willReturn($expectedResult);
        $this->logger->expects(static::at(1))
            ->method('debug')
            ->with(['response' => $expectedResult]);

        static::assertEquals(
            $expectedResult,
            $this->gatewayClient->placeRequest($transferObject)
        );
    }

    public function testPlaceRequestSoapException()
    {
        $this->expectException(\Exception::class);

        $this->logger->expects(static::at(0))
            ->method('debug')
            ->with(
                ['request' => ['body']]
            );
        $this->clientFactory->expects(static::once())
            ->method('create')
            ->with('path_to_wsdl', ['trace' => true])
            ->willReturn($this->client);
        $transferObject = $this->getTransferObject();
        $transferObject->expects(static::any())
            ->method('__setSoapHeaders')
            ->with(['headers']);
        $this->client->expects(static::once())
            ->method('__soapCall')
            ->with('soapMethod', [['body']])
            ->willThrowException(new \Exception());
        $this->client->expects(static::once())
            ->method('__getLastRequest')
            ->willReturn('RequestTrace');
        $this->logger->expects(static::at(1))
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
