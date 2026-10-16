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
    private const OAUTH_SCOPE = 'prices shipments tracking labels payments international-labels';

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
        $expectedCacheKey = $this->getExpectedCacheKey($clientId, $clientUrl);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($expectedCacheKey)
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
    public function testGetAccessTokenUsesClientSpecificCacheKeys(): void
    {
        $firstClientId = 'first-client-id';
        $secondClientId = 'second-client-id';
        $clientUrl = 'https://apis.usps.com/oauth2/v3/token';
        $firstCacheKey = $this->getExpectedCacheKey($firstClientId, $clientUrl);
        $secondCacheKey = $this->getExpectedCacheKey($secondClientId, $clientUrl);

        $this->assertNotSame($firstCacheKey, $secondCacheKey);

        $this->cacheMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnMap([
                [$firstCacheKey, 'first-client-token'],
                [$secondCacheKey, 'second-client-token'],
            ]);

        $this->asyncHttpClientMock->expects($this->never())->method('request');

        $this->assertSame(
            'first-client-token',
            $this->uspsAuth->getAccessToken($firstClientId, 'first-client-secret', $clientUrl)
        );
        $this->assertSame(
            'second-client-token',
            $this->uspsAuth->getAccessToken($secondClientId, 'second-client-secret', $clientUrl)
        );
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
            ->with($this->getExpectedCacheKey($clientId, $clientUrl))
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
            ->with($this->getExpectedCacheKey($clientId, $clientUrl))
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
            ->with($this->getExpectedCacheKey($clientId, $clientUrl))
            ->willReturn(false);

        // Simulate request payload
        $requestPayloadMock = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => self::OAUTH_SCOPE,
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
            ->with('new-access-token', $this->getExpectedCacheKey($clientId, $clientUrl), [], 3600);

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

    /**
     * @param string $clientId
     * @param string $clientUrl
     * @return string
     */
    private function getExpectedCacheKey(string $clientId, string $clientUrl): string
    {
        return UspsAuth::CACHE_KEY_PREFIX . hash('sha256', implode('|', [
            $clientUrl,
            $clientId,
            self::OAUTH_SCOPE
        ]));
    }
}
