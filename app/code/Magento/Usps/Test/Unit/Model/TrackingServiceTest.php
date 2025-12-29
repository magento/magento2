<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\AsyncClient\HttpResponseDeferredInterface;
use Magento\Framework\HTTP\AsyncClient\Request;
use Magento\Framework\HTTP\AsyncClient\Response;
use Magento\Framework\HTTP\AsyncClientInterface;
use Magento\Shipping\Model\Tracking\Result;
use Magento\Shipping\Model\Tracking\Result\Error;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Usps\Model\Carrier;
use Magento\Usps\Model\TrackingService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TrackingServiceTest class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TrackingServiceTest extends TestCase
{
    /**
     * @var TrackingService
     */
    private TrackingService $trackingService;

    /**
     * @var ResultFactory|MockObject
     */
    private $trackFactoryMock;

    /**
     * @var ErrorFactory|MockObject
     */
    private $trackErrorFactoryMock;

    /**
     * @var StatusFactory|MockObject
     */
    private $trackStatusFactoryMock;

    /**
     * @var AsyncClientInterface|MockObject
     */
    private $httpClientMock;

    /**
     * @var Carrier|MockObject
     */
    private $carrierMock;

    /**
     * @var AsyncClientInterface|MockObject
     */
    private $asyncHttpClientMock;

    protected function setUp(): void
    {
        $this->trackFactoryMock = $this->createMock(ResultFactory::class);
        $this->trackErrorFactoryMock = $this->createMock(ErrorFactory::class);
        $this->trackStatusFactoryMock = $this->createMock(StatusFactory::class);
        $this->httpClientMock = $this->createMock(AsyncClientInterface::class);
        $this->carrierMock = $this->createMock(Carrier::class);

        $this->trackingService = new TrackingService(
            $this->trackFactoryMock,
            $this->trackErrorFactoryMock,
            $this->trackStatusFactoryMock,
            $this->httpClientMock
        );
        $this->trackingService->setCarrierModel($this->carrierMock);
        $this->asyncHttpClientMock = $this->createMock(AsyncClientInterface::class);
    }

    /**
     * @param string $tackingData
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws \Throwable
     */
    #[DataProvider('getTrackingNumbersDataProviderSuccess')]
    public function testGetRestTrackingSuccess(string $tackingData): void
    {
        $apiUrl = 'tracking_url_api';
        $accessToken = 'test_token';
        $queryParams = ['expand' => 'DETAIL'];
        $expectedResult = $this->getSuccessResponse($tackingData);
        // Mock carrier configuration
        $this->carrierMock->expects($this->once())
            ->method('getUrl')
            ->with(TrackingService::TRACK_REQUEST_END_POINT)
            ->willReturn($apiUrl);

        $this->carrierMock->expects($this->once())
            ->method('getOauthAccessRequest')
            ->willReturn($accessToken);

        // Mock HTTP response
        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($expectedResult);

        $deferredResponseMock = $this->createMock(HttpResponseDeferredInterface::class);
        $deferredResponseMock->expects($this->once())
            ->method('get')
            ->willReturn($responseMock);

        // Mock HTTP client
        $this->httpClientMock->expects($this->any())
            ->method('request')
            ->with($this->callback(
                function (
                    Request $request
                ) use (
                    $apiUrl,
                    $tackingData,
                    $queryParams,
                    $accessToken
                ) {
                    $encodedTracking = urlencode($tackingData);
                    $queryString = http_build_query($queryParams);
                    $expectedUrl = $apiUrl . '/' . $encodedTracking . '?' . $queryString;
                    $expectedHeaders = [
                        'Content-Type' => TrackingService::CONTENT_TYPE_JSON,
                        'Authorization' => TrackingService::AUTHORIZATION_BEARER . $accessToken,
                    ];

                    // Assertions for request properties
                    $this->assertEquals(
                        $expectedUrl,
                        $request->getUrl(),
                        'Request URL does not match the expected URL.'
                    );
                    $this->assertEquals(Request::METHOD_GET, $request->getMethod(), 'Request method is not GET.');
                    $this->assertEquals(
                        $expectedHeaders,
                        $request->getHeaders(),
                        'Request headers do not match the expected headers.'
                    );
                    return true;
                }
            ))->willReturn($deferredResponseMock);

        // Mock tracking result objects
        $resultObject = $this->createMock(Result::class);
        $this->trackFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultObject);

        $trackingStatus = $this->createMock(Status::class);
        $this->trackStatusFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($trackingStatus);

        $result = $this->trackingService->getRestTracking([$tackingData]);
        $this->assertInstanceOf(Result::class, $result);
        $this->assertNotNull($result);
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws \Throwable
     */
    public function testGetRestTrackingReturnsNullForEmptyTrackingData(): void
    {
        $result = $this->trackingService->getRestTracking([]);
        $this->assertNull($result, 'Expected getRestTracking to return null for empty tracking data.');
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws \Throwable
     */
    public function testGetRestTrackingReturnsNullForFailedHttpRequest(): void
    {
        $trackingData = ['1234567890'];

        // Mock carrier configuration
        $this->carrierMock->expects($this->once())
            ->method('getUrl')
            ->willReturn('tracking_url_api');
        $this->carrierMock->expects($this->once())
            ->method('getOauthAccessRequest')
            ->willReturn('test_token');

        // Simulate HTTP request failure
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('Simulated HTTP request failure'));

        $result = $this->trackingService->getRestTracking($trackingData);
        $this->assertNull($result, 'Expected getRestTracking to return null for failed HTTP request.');
    }

    /**
     * @param string|null $trackingData
     * @return void
     * @throws LocalizedException
     * @throws Exception
     * @throws \Throwable
     */
    #[DataProvider('getTrackingNumbersDataProviderError')]
    public function testGetRestTrackingError(?string $trackingData): void
    {
        $accessToken = 'test_token';
        $apiUrl = 'tracking_url_api';
        // Mock carrier configuration
        $this->carrierMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($apiUrl);

        $this->carrierMock->expects($this->once())
            ->method('getOauthAccessRequest')
            ->willReturn($accessToken);

        // Mock HTTP response
        $responseMock = $this->createMock(Response::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($this->getErrorResponse());

        $deferredResponse = $this->createMock(HttpResponseDeferredInterface::class);
        $deferredResponse->expects($this->once())
            ->method('get')
            ->willReturn($responseMock);

        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->willReturn($deferredResponse);

        // Mock tracking result objects
        $resultObject = $this->createMock(Result::class);
        $this->trackFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultObject);

        $trackingError = $this->createMock(Error::class);
        $this->trackErrorFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($trackingError);

        $result = $this->trackingService->getRestTracking([$trackingData]);
        $this->assertInstanceOf(Result::class, $result, 'Expected getRestTracking to return a Result object.');
        $this->assertNotNull($result, 'Expected getRestTracking to return a non-null Result object.');
    }

    /**
     * Data provider for successful tracking numbers
     *
     * @return array
     */
    public static function getTrackingNumbersDataProviderSuccess(): array
    {
        return [
            ['1234567890'],
            ['636376342634'],
            ['0987654321']
        ];
    }

    /**
     * Data provider for error cases in tracking numbers
     *
     * @return array
     */
    public static function getTrackingNumbersDataProviderError(): array
    {
        return [
            [''],
            ['invalid_tracking_number'],
            [null]
        ];
    }

    /**
     * @param string $trackingNumber
     * @return string
     */
    private function getSuccessResponse(string $trackingNumber): string
    {
        $successResponse = [
            'trackingNumber' => $trackingNumber,
            'statusSummary' => 'Delivered',
            'trackingEvents' => [
                [
                    'eventType' => 'Delivered',
                    'eventCity' => 'New York',
                    'eventState' => 'NY',
                    'eventCountry' => 'US',
                    'eventTimestamp' => '2024-03-20T14:30:00Z'
                ]
            ]
        ];
        return json_encode($successResponse);
    }

    /**
     * @return string
     */
    private function getErrorResponse(): string
    {
        $errorResponse = [
            'error' => [
                'message' => 'Tracking number not found'
            ]
        ];
        return json_encode($errorResponse);
    }
}
