<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service;

use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Math\Random;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use Zend_Http_Client_Exception;
use Zend_Http_Response;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GatewayTest extends TestCase
{
    /**
     * @var Gateway|MockObject
     */
    private $object;

    /**
     * @var ZendClientFactory|MockObject
     */
    private $httpClientFactoryMock;

    /**
     * @var Random|MockObject
     */
    private $mathRandomMock;

    /**
     * @var Logger|MockObject
     */
    private $loggerMock;

    /**
     * @var ZendClient|MockObject
     */
    private $zendClientMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->httpClientFactoryMock = $this->getMockBuilder(ZendClientFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->zendClientMock = $this->getMockBuilder(ZendClient::class)
            ->setMethods(['request', 'setUri'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClientFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->zendClientMock);
        $this->mathRandomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(LoggerInterface::class)])
            ->setMethods(['debug'])
            ->getMock();

        $this->object = new Gateway(
            $this->httpClientFactoryMock,
            $this->mathRandomMock,
            $this->loggerMock
        );
    }

    /**
     * @param string $nvpResponse
     * @param array $expectedResult
     * @dataProvider postRequestOkDataProvider
     */
    public function testPostRequestOk(string $nvpResponse, array $expectedResult): void
    {
        $configMap = [
            ['getDebugReplacePrivateDataKeys', null, ['masked']],
            ['debug', null, true]
        ];

        /** @var ConfigInterface|MockObject $configInterfaceMock */
        $configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $zendResponseMock = $this->getMockBuilder(Zend_Http_Response::class)
            ->setMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $zendResponseMock->expects(static::once())
            ->method('getBody')
            ->willReturn($nvpResponse);
        $this->zendClientMock->expects(static::once())
            ->method('request')
            ->willReturn($zendResponseMock);

        $configInterfaceMock->expects(static::any())
            ->method('getValue')
            ->willReturnMap($configMap);
        $this->loggerMock->expects(static::once())
            ->method('debug');

        $object = new DataObject();

        $result = $this->object->postRequest($object, $configInterfaceMock);

        static::assertEquals($expectedResult, $result->toArray());
    }

    /**
     * @return array[]
     */
    public function postRequestOkDataProvider(): array
    {
        return [
            [
                'RESULT=0&RESPMSG=Approved&SECURETOKEN=9tl4MmP46NUadl9pwCKFgfQjA'
                . '&SECURETOKENID=vVWBMSNb9j0SLlYw4AbqBnKmuogtzNNC',
                [
                    'result' => '0',
                    'securetoken' => '9tl4MmP46NUadl9pwCKFgfQjA',
                    'securetokenid' => 'vVWBMSNb9j0SLlYw4AbqBnKmuogtzNNC',
                    'respmsg' => 'Approved',
                    'result_code' => '0',
                ]
            ],
            [
                'RESULT=0&PNREF=A30A3A958244&RESPMSG=Approved&AUTHCODE=028PNI&AVSADDR=N&AVSZIP=N&HOSTCODE=A'
                . '&PROCAVS=N&VISACARDLEVEL=12&TRANSTIME=2020-12-16 14:43:57&FIRSTNAME[4]=Joé'
                . '&LASTNAME=O\'Reilly&COMPANYNAME[14]=Ruff & Johnson&COMMENT1[7]=Level=5'
                . '&AMT=30.00&ACCT=1111&EXPDATE=1224&CARDTYPE=0&IAVS=N',
                [
                    'result' => '0',
                    'pnref' => 'A30A3A958244',
                    'respmsg' => 'Approved',
                    'authcode' => '028PNI',
                    'avsaddr' => 'N',
                    'avszip' => 'N',
                    'hostcode' => 'A',
                    'procavs' => 'N',
                    'visacardlevel' => '12',
                    'transtime' => '2020-12-16 14:43:57',
                    'firstname' => 'Joé',
                    'lastname' => 'O\'Reilly',
                    'companyname' => 'Ruff & Johnson',
                    'comment1' => 'Level=5',
                    'amt' => '30.00',
                    'acct' => '1111',
                    'expdate' => '1224',
                    'cardtype' => '0',
                    'iavs' => 'N',
                    'result_code' => '0',
                ]
            ],
        ];
    }

    /**
     * @param array $requestData
     * @param string $requestBody
     * @dataProvider requestBodyDataProvider
     */
    public function testRequestBody(array $requestData, string $requestBody): void
    {
        $configMap = [
            ['getDebugReplacePrivateDataKeys', null, ['masked']],
            ['debug', null, true]
        ];

        /** @var ConfigInterface|MockObject $configInterfaceMock */
        $configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $zendResponseMock = $this->getMockBuilder(Zend_Http_Response::class)
            ->setMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $zendResponseMock->expects(static::once())
            ->method('getBody')
            ->willReturn('RESULT=0&RESPMSG=Approved');
        $this->zendClientMock->expects(static::once())
            ->method('request')
            ->willReturn($zendResponseMock);

        $configInterfaceMock->expects(static::any())
            ->method('getValue')
            ->willReturnMap($configMap);
        $this->loggerMock->expects(static::once())
            ->method('debug');

        $request = new DataObject($requestData);
        $this->object->postRequest($request, $configInterfaceMock);
        $method = new ReflectionMethod($this->zendClientMock, '_prepareBody');
        $method->setAccessible(true);
        $this->assertEquals($requestBody, $method->invoke($this->zendClientMock));
    }

    /**
     * @return array[]
     */
    public function requestBodyDataProvider(): array
    {
        return [
            [
                [
                    'companyname' => 'Ruff & Johnson',
                    'comment1' => 'Level=5',
                    'shiptofirstname' => 'Joé',
                    'shiptolastname' => 'O\'Reilly',
                    'shiptostreet' => '4659 Rainbow Road',
                    'shiptocity' => 'Los Angeles',
                    'shiptostate' => 'CA',
                    'shiptozip' => '90017',
                    'shiptocountry' => 'US',
                ],
                'companyname[14]=Ruff & Johnson&comment1[7]=Level=5&shiptofirstname=Joé&shiptolastname=O\'Reilly'
                . '&shiptostreet=4659 Rainbow Road&shiptocity=Los Angeles&shiptostate=CA&shiptozip=90017'
                . '&shiptocountry=US'
            ]
        ];
    }

    public function testPostRequestFail()
    {
        $this->expectException('Zend_Http_Client_Exception');
        /** @var ConfigInterface|MockObject $configInterfaceMock */
        $configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $zendResponseMock = $this->getMockBuilder(Zend_Http_Response::class)
            ->setMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $zendResponseMock->expects(static::never())
            ->method('getBody');
        $this->zendClientMock->expects(static::once())
            ->method('request')
            ->willThrowException(new Zend_Http_Client_Exception());

        $object = new DataObject();
        $this->object->postRequest($object, $configInterfaceMock);
    }
}
