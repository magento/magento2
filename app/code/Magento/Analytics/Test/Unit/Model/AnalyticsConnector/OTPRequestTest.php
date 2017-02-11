<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\AnalyticsConnector;

use Magento\Analytics\Model\AnalyticsConnector\OTPRequest;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OTPRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ZendClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientFactoryMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Zend_Http_Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpResponseMock;

    /**
     * @var ZendClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var OTPRequest
     */
    private $otpRequestModel;

    /**
     * @var string
     */
    private $otpUrlConfigPath = 'path/url/otp';

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->analyticsTokenMock = $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClientFactoryMock = $this->getMockBuilder(ZendClientFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpResponseMock = $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClientMock = $this->getMockBuilder(ZendClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->otpRequestModel = $this->objectManagerHelper->getObject(
            OTPRequest::class,
            [
                'config' => $this->configMock,
                'analyticsToken' => $this->analyticsTokenMock,
                'clientFactory' => $this->httpClientFactoryMock,
                'logger' => $this->loggerMock,
                'otpUrlConfigPath' => $this->otpUrlConfigPath,
            ]
        );
    }

    /**
     * @param string|null $token If null token is not exist.
     * @param int $responseCode
     * @param string|null $otp If null OTP was not received.
     *
     * @dataProvider callDataProvider
     */
    public function testCallSuccess($token, $responseCode, $otp)
    {
        $otpUrl = 'https://example.com/otp';
        $baseUrl = 'https://base.com';
        $requestBody = json_encode(["token" => $token, "url" => $baseUrl]);
        $responseBody = json_encode(["otp" => $otp]);

        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->with()
            ->willReturn((bool)$token);
        if ($token) {
            $this->httpClientFactoryMock
                ->expects($this->once())
                ->method('create')
                ->with()
                ->willReturn($this->httpClientMock);
            $this->configMock
                ->expects($this->exactly(2))
                ->method('getValue')
                ->withConsecutive(
                    [$this->otpUrlConfigPath],
                    [Store::XML_PATH_SECURE_BASE_URL]
                )
                ->willReturnOnConsecutiveCalls($otpUrl, $baseUrl);
            $this->httpClientMock
                ->expects($this->once())
                ->method('setUri')
                ->with($otpUrl)
                ->willReturnSelf();
            $this->analyticsTokenMock
                ->expects($this->once())
                ->method('getToken')
                ->with()
                ->willReturn($token);
            $this->httpClientMock
                ->expects($this->once())
                ->method('setRawData')
                ->with($requestBody)
                ->willReturnSelf();
            $this->httpClientMock
                ->expects($this->once())
                ->method('setMethod')
                ->with(ZendClient::POST)
                ->willReturnSelf();
            $this->httpClientMock
                ->expects($this->once())
                ->method('request')
                ->with()
                ->willReturn($this->httpResponseMock);
            $this->httpResponseMock
                ->expects($this->once())
                ->method('getStatus')
                ->with()
                ->willReturn($responseCode);
            $this->httpResponseMock
                ->expects(($responseCode === 200) ? $this->once() : $this->never())
                ->method('getBody')
                ->with()
                ->willReturn($responseBody);
            $this->loggerMock
                ->expects($otp ? $this->never() : $this->once())
                ->method('critical')
                ->with('The request for a OTP is unsuccessful.')
                ->willReturn(null);
        }
        $this->assertSame($otp ?: false, $this->otpRequestModel->call());
    }

    /**
     * @return void
     */
    public function testCallWithException()
    {
        $exception = new \Exception('Error');
        $this->analyticsTokenMock
            ->expects($this->once())
            ->method('isTokenExist')
            ->with()
            ->willThrowException($exception);
        $this->loggerMock
            ->expects($this->once())
            ->method('critical')
            ->with($exception->getMessage())
            ->willReturn(null);
        $this->assertFalse($this->otpRequestModel->call());
    }

    /**
     * @return array
     */
    public function callDataProvider()
    {
        return [
            'TokenDoesNotExist' => [null, 0, null],
            'TokenExistAndResponseWithRedirect' => ['9f17967951d23e550695', 303, null],
            'TokenExistAndRequestIsSuccessfulWithOtpEmpty' => ['9f17967951d23e550695', 200, null],
            'TokenExistAndRequestIsSuccessfulWithOtpValid' => ['9f17967951d23e550695', 200, '249e6b658877bde2a77bc4ab'],
        ];
    }
}
