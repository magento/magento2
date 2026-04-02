<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Test\Unit\Model;

use Magento\Framework\App\Cache\Type\Config as Cache;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Usps\Model\UspsAuth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;

class UspsAuthTest extends TestCase
{
    /**
     * @var Cache|MockObject
     */
    private Cache|MockObject $cacheMock;

    /**
     * @var AsyncClientInterface|MockObject
     */
    private AsyncClientInterface|MockObject $asyncHttpClientMock;

    /**
     * @var ErrorFactory|MockObject
     */
    private ErrorFactory|MockObject $errorFactoryMock;

    /**
     * @var UspsAuth
     */
    private UspsAuth $uspsAuth;

    protected function setUp(): void
    {
        $this->cacheMock = $this->createMock(Cache::class);
        $this->asyncHttpClientMock = $this->createMock(AsyncClientInterface::class);
        $this->errorFactoryMock = $this->createMock(ErrorFactory::class);

        $this->uspsAuth = new UspsAuth(
            $this->cacheMock,
            $this->errorFactoryMock,
            $this->asyncHttpClientMock
        );
    }

    /**
     * @param string $clientId,
     * @param string $clientSecret,
     * @param string $clientUrl
     * @return void
     * @throws LocalizedException
     * @throws \Throwable
     */
    #[DataProvider('clientCredentialsDataProvider')]
    public function testGetAccessTokenReturnsCachedToken(
        string $clientId,
        string $clientSecret,
        string $clientUrl
    ): void {
        $expectedCachedToken = 'cached-access-token';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with(UspsAuth::CACHE_KEY_PREFIX)
            ->willReturn($expectedCachedToken);

        $this->asyncHttpClientMock->expects($this->never())->method('request');
        $result = $this->uspsAuth->getAccessToken($clientId, $clientSecret, $clientUrl);
        $this->assertEquals($expectedCachedToken, $result);
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws \Throwable
     */
    #[DataProvider('clientCredentialsDataProvider')]
    public function testGetAccessTokenReturnsNullOnException(
        string $clientId,
        string $clientSecret,
        string $clientUrl
    ): void {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with(UspsAuth::CACHE_KEY_PREFIX)
            ->willReturn(false);

        $this->asyncHttpClientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('Network error'));

        $result = $this->uspsAuth->getAccessToken($clientId, $clientSecret, $clientUrl);
        $this->assertNull($result);
    }

    /**
     * @return void
     * @throws Exception
     * @throws LocalizedException
     * @throws \Throwable
     */
    #[DataProvider('clientCredentialsDataProvider')]
    public function testGetAccessTokenReturnsFalseOnMissingAccessToken(
        string $clientId,
        string $clientSecret,
        string $clientUrl
    ): void {
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with(UspsAuth::CACHE_KEY_PREFIX)
            ->willReturn(false);

        $asyncResponseMock = $this->createMock(HttpResponseDeferredInterface::class);
        $responseResultMock = $this->createMock(Response::class);

        $asyncResponseMock->expects($this->once())
            ->method('get')
            ->willReturn($responseResultMock);

        $responseResultMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getErrorResponse());

        $this->asyncHttpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($asyncResponseMock);

        $result = $this->uspsAuth->getAccessToken($clientId, $clientSecret, $clientUrl);
        $this->assertFalse($result);
    }

    /**
     * @throws \Throwable
     * @throws LocalizedException
     * @throws Exception
     */
    #[DataProvider('clientCredentialsDataProvider')]
    public function testGetAccessTokenFetchesNewToken(
        string $clientId,
        string $clientSecret,
        string $clientUrl
    ): void {
        // Simulate cache miss
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with(UspsAuth::CACHE_KEY_PREFIX)
            ->willReturn(false);

        // Simulate request payload
        $requestPayloadMock = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => 'prices shipments tracking labels payments international-labels',
        ]);

        // Mock async response
        $asyncResponseMock = $this->createMock(HttpResponseDeferredInterface::class);
        $responseResultMock = $this->createMock(Response::class);

        $asyncResponseMock->expects($this->once())
            ->method('get')
            ->willReturn($responseResultMock);

        $responseResultMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getSuccessResponse());

        $this->asyncHttpClientMock->expects($this->once())
            ->method('request')
            ->with($this->callback(function (Request $request) use ($clientUrl, $requestPayloadMock) {
                $this->assertInstanceOf(Request::class, $request, 'Request is not an instance of Request class');
                $this->assertEquals($clientUrl, $request->getUrl(), 'Request URL does not match expected URL');
                $this->assertEquals($requestPayloadMock, $request->getBody(), 'Request body mismatch');
                $this->assertEquals(Request::METHOD_POST, $request->getMethod(), 'Request method mismatch');
                return true;
            }))
            ->willReturn($asyncResponseMock);

        // Assert cache save
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with('new-access-token', UspsAuth::CACHE_KEY_PREFIX, [], 3600);

        $result = $this->uspsAuth->getAccessToken($clientId, $clientSecret, $clientUrl);
        $this->assertEquals('new-access-token', $result);
    }

    /**
     * Data provider for client credentials
     *
     * @return array
     */
    public static function clientCredentialsDataProvider(): array
    {
        return [
            ['clientId', 'clientSecret', 'oauthTokenUrl']
        ];
    }

    /**
     * @return string
     */
    private function getSuccessResponse(): string
    {
        return json_encode(['access_token' => 'new-access-token', 'expires_in' => 3600]);
    }

    /**
     * @return string
     */
    private function getErrorResponse(): string
    {
        return json_encode(['errors' => ['message' => 'Invalid credentials']]);
    }
}
