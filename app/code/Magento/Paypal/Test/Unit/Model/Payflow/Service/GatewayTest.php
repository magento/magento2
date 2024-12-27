<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow\Service;

use Laminas\Http\Exception\RuntimeException;
use Laminas\Http\Response;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Math\Random;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

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
     * @var LaminasClientFactory|MockObject
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
     * @var LaminasClient|MockObject
     */
    private $httpClientMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->httpClientFactoryMock = $this->getMockBuilder(LaminasClientFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClientMock = $this->getMockBuilder(LaminasClient::class)
            ->onlyMethods(['send', 'setUri'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClientFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->httpClientMock);
        $this->mathRandomMock = $this->getMockBuilder(Random::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs([$this->getMockForAbstractClass(LoggerInterface::class)])
            ->onlyMethods(['debug'])
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
        $responseMock = $this->getMockBuilder(Response::class)
            ->onlyMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock->expects(static::once())
            ->method('getBody')
            ->willReturn($nvpResponse);
        $this->httpClientMock->expects(static::once())
            ->method('send')
            ->willReturn($responseMock);

        $configInterfaceMock->expects(static::any())
            ->method('getValue')
            ->willReturnMap($configMap);
        $this->loggerMock->expects(static::once())
            ->method('debug');
        $this->mathRandomMock->expects(static::once())
            ->method('getUniqueHash')
            ->willReturn('UniqueHash');

        $object = new DataObject();

        $result = $this->object->postRequest($object, $configInterfaceMock);

        static::assertEquals($expectedResult, $result->toArray());
    }

    /**
     * @return array[]
     */
    public static function postRequestOkDataProvider(): array
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
        $responseMock = $this->getMockBuilder(Response::class)
            ->onlyMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock->expects(static::once())
            ->method('getBody')
            ->willReturn('RESULT=0&RESPMSG=Approved');
        $this->httpClientMock->expects(static::once())
            ->method('send')
            ->willReturn($responseMock);

        $configInterfaceMock->expects(static::any())
            ->method('getValue')
            ->willReturnMap($configMap);
        $this->loggerMock->expects(static::once())
            ->method('debug');
        $this->mathRandomMock->expects(static::once())
            ->method('getUniqueHash')
            ->willReturn('UniqueHash');

        $request = new DataObject($requestData);
        $this->object->postRequest($request, $configInterfaceMock);
        $method = new ReflectionMethod($this->httpClientMock, 'prepareBody');
        $method->setAccessible(true);
        $this->assertEquals($requestBody, urldecode($method->invoke($this->httpClientMock)));
    }

    /**
     * @return array[]
     */
    public static function requestBodyDataProvider(): array
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
        $this->expectException(RuntimeException::class);
        /** @var ConfigInterface|MockObject $configInterfaceMock */
        $configInterfaceMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $responseMock = $this->getMockBuilder(Response::class)
            ->onlyMethods(['getBody'])
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock->expects(static::never())
            ->method('getBody');
        $this->httpClientMock->expects(static::once())
            ->method('send')
            ->willThrowException(new RuntimeException());
        $this->mathRandomMock->expects(static::once())
            ->method('getUniqueHash')
            ->willReturn('UniqueHash');

        $object = new DataObject();
        $this->object->postRequest($object, $configInterfaceMock);
    }
}
