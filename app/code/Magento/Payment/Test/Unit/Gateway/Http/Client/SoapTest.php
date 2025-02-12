<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Http\Client;

use Exception;
use Magento\Framework\Webapi\Soap\ClientFactory;
use Magento\Payment\Gateway\Http\Client\Soap;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SoapClient;
use StdClass;

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

    /**
     * @inheritdoc
     */
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
        $this->client = $this->getMockBuilder(SoapClient::class)
            ->onlyMethods(['__setSoapHeaders', '__soapCall', '__getLastRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->gatewayClient = new Soap(
            $this->logger,
            $this->clientFactory,
            $this->converter
        );
    }

    /**
     * @return void
     */
    public function testPlaceRequest(): void
    {
        $expectedResult = [
            'result' => []
        ];
        $soapResult = new StdClass();

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
        $this->logger
            ->method('debug')
            ->willReturnCallback(
                function ($args) use ($expectedResult) {
                    if ($args === ['request' => ['body']] || $args === ['response' => $expectedResult]) {
                        return null;
                    }
                }
            );

        static::assertEquals(
            $expectedResult,
            $this->gatewayClient->placeRequest($transferObject)
        );
    }

    /**
     * @return void
     */
    public function testPlaceRequestSoapException(): void
    {
        $this->expectException(Exception::class);

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
            ->willThrowException(new Exception());
        $this->client->expects(static::once())
            ->method('__getLastRequest')
            ->willReturn('RequestTrace');
        $this->logger
            ->method('debug')
            ->willReturnCallback(
                function ($args) {
                    if ($args === [['request' => ['body']]] || $args === [['trace' => 'RequestTrace']]) {
                        return null;
                    }
                }
            );

        $this->gatewayClient->placeRequest($transferObject);
    }

    /**
     * Returns prepared transfer object
     *
     * @return MockObject
     */
    private function getTransferObject(): MockObject
    {
        $transferObject = $this->getMockBuilder(TransferInterface::class)
            ->onlyMethods(['getBody', 'getClientConfig', 'getMethod'])
            ->addMethods(['__setSoapHeaders'])->getMockForAbstractClass();

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
