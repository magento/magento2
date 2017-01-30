<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\Client\Soap;

class SoapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $clientFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var Soap
     */
    private $gatewayClient;

    protected function setUp()
    {
        $this->logger = $this->getMockBuilder(
            'Magento\Payment\Model\Method\Logger'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory = $this->getMockBuilder(
            'Magento\Framework\Webapi\Soap\ClientFactory'
        )->getMock();
        $this->converter = $this->getMockBuilder(
            'Magento\Payment\Gateway\Http\ConverterInterface'
        )->getMockForAbstractClass();
        $this->client = $this->getMockBuilder('\SoapClient')
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
        $soapResult = new \StdClass;

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
        $this->setExpectedException('Exception');

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
            ->willThrowException(new \Exception);
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getTransferObject()
    {
        $transferObject = $this->getMockBuilder(
            'Magento\Payment\Gateway\Http\TransferInterface'
        )->getMock();

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
