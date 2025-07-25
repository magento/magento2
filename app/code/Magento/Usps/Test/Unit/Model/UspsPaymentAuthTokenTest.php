<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\HttpException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Usps\Model\UspsPaymentAuthToken;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

class UspsPaymentAuthTokenTest extends TestCase
{
    private const ERROR_LOG_MESSAGE = '---Exception from auth api---';

    /**
     * @var UspsPaymentAuthToken
     */
    private $uspsPaymentAuthToken;

    /**
     * @var AsyncClientInterface|MockObject
     */
    private $asyncHttpClientMock;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->asyncHttpClientMock = $this->createMock(AsyncClientInterface::class);
        $this->uspsPaymentAuthToken = new UspsPaymentAuthToken(
            $this->asyncHttpClientMock,
        );
    }

    /**
     * @dataProvider getPaymentAuthTokenDataProvider
     * @param $token
     * @param $clientUrl
     * @param $accountInfo
     * @param $expectedResult
     * @return void
     * @throws Throwable
     */
    public function testGetPaymentAuthTokenSuccess($token, $clientUrl, $accountInfo): void
    {
        $requestPayload = json_encode([
            'roles' => [
                [
                    'roleName' => 'PAYER',
                    'CRID' => $accountInfo['CRID'],
                    'accountNumber' => $accountInfo['accountNumber'],
                    'accountType' => $accountInfo['accountType'],
                ],
                [
                    'roleName' => 'LABEL_OWNER',
                    'CRID' => $accountInfo['CRID'],
                    'MID' => $accountInfo['MID'],
                    'manifestMID' => $accountInfo['manifestMID']
                ]
            ]
        ]);
        $expectedResult = 'payment-authorization-token';
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $asyncResponseMock = $this->getMockBuilder(HttpResponseDeferredInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseResult = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $asyncResponseMock->expects($this->once())
            ->method('get')
            ->willReturn($responseResult);
        $responseResult->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getSuccessResponse());

        $this->asyncHttpClientMock->expects($this->once())
            ->method('request')
            ->with($this->callback(function ($request) use ($clientUrl, $requestPayload, $headers) {
                $this->assertInstanceOf(Request::class, $request, 'Request is not an instance of Request class');
                $this->assertEquals($clientUrl, $request->getUrl(), 'Request URL does not match expected URL');
                $this->assertEquals($headers, $request->getHeaders(), 'Request headers do not match expected headers');
                $this->assertEquals(Request::METHOD_POST, $request->getMethod(), 'Request method is not POST');
                $this->assertEquals($requestPayload, $request->getBody(), 'Request body mismatch');
                return true;
            }))->willReturn($asyncResponseMock);

        $result = $this->uspsPaymentAuthToken->getPaymentAuthToken($token, $clientUrl, $accountInfo);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getPaymentAuthTokenDataProvider
     * @param string $token,
     * @param string $clientUrl,
     * @param array $accountInfo
     * @return void
     * @throws Throwable
     */
    public function testGetPaymentAuthTokenMissingToken(string $token, string $clientUrl, array $accountInfo): void
    {
        $asyncResponseMock = $this->getMockBuilder(HttpResponseDeferredInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseResult = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $asyncResponseMock->expects($this->once())
            ->method('get')
            ->willReturn($responseResult);
        $responseResult->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode(['error' => ['message' => 'Token not found']]));

        $this->asyncHttpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($asyncResponseMock);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Token not found');
        try {
            $this->uspsPaymentAuthToken->getPaymentAuthToken($token, $clientUrl, $accountInfo);
        } catch (LocalizedException $e) {
            $this->assertInstanceOf(LocalizedException::class, $e);
            throw $e; // Re-throw to ensure the test fails if the exception is not caught
        }
    }

    /**
     * @dataProvider getPaymentAuthTokenDataProvider
     * @param string $token
     * @param string $clientUrl
     * @param array $accountInfo
     * @return void
     * @throws Throwable
     */
    public function testGetPaymentAuthTokenHttpException(string $token, string $clientUrl, array $accountInfo): void
    {
        $httpExceptionMessage = 'HTTP error occurred';

        $this->asyncHttpClientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new HttpException($httpExceptionMessage));

        $uspsPaymentAuthTokenMock = $this->getMockBuilder(UspsPaymentAuthToken::class)
            ->setConstructorArgs(['asyncHttpClient' => $this->asyncHttpClientMock])
            ->onlyMethods(['_debug'])
            ->getMock();

        $uspsPaymentAuthTokenMock->expects($this->once())
            ->method('_debug')
            ->with(self::ERROR_LOG_MESSAGE . $httpExceptionMessage);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('HTTP Exception: ' . $httpExceptionMessage);
        try {
            $uspsPaymentAuthTokenMock->getPaymentAuthToken($token, $clientUrl, $accountInfo);
        } catch (LocalizedException $e) {
            $this->assertInstanceOf(LocalizedException::class, $e);
            $this->assertEquals('HTTP Exception: ' . $httpExceptionMessage, $e->getMessage());
            throw $e; // Re-throw to ensure the test fails if the exception is not caught
        }
    }

    /**
     * @return array
     */
    public static function getPaymentAuthTokenDataProvider(): array
    {
        return [
            [
                'token' => 'test_token',
                'clientUrl' => 'test_payment_auth_token_url',
                'accountInfo' => [
                    'CRID' => '123456',
                    'MID' => '789012',
                    'manifestMID' => '345678',
                    'accountNumber' => '901234',
                    'accountType' => 'EPS'
                ]
            ],
            [
                'token' => 'test_token',
                'clientUrl' => 'test_payment_auth_token_url',
                'accountInfo' => [
                    'CRID' => '123456',
                    'MID' => '429012',
                    'manifestMID' => '345678',
                    'accountNumber' => '901234',
                    'accountType' => 'PERMIT'
                ]
            ]
        ];
    }

    /**
     * @return string
     */
    private function getSuccessResponse(): string
    {
        return json_encode(['paymentAuthorizationToken' => 'payment-authorization-token']);
    }
}
