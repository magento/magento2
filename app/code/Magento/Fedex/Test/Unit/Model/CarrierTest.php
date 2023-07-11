<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Fedex\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Fedex\Model\Carrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error as RateResultError;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Shipping\Model\Tracking\Result;
use Magento\Shipping\Model\Tracking\Result\Error;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\Shipping\Model\Tracking\Result\StatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\HTTP\Client\Curl;

/**
 * CarrierTest contains units test for Fedex carrier methods
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $helper;

    /**
     * @var Carrier|MockObject
     */
    private $carrier;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scope;

    /**
     * @var Error|MockObject
     */
    private $error;

    /**
     * @var ErrorFactory|MockObject
     */
    private $errorFactory;

    /**
     * @var ErrorFactory|MockObject
     */
    private $trackErrorFactory;

    /**
     * @var StatusFactory|MockObject
     */
    private $statusFactory;

    /**
     * @var Result
     */
    private $result;

    /**
     * @var \SoapClient|MockObject
     */
    private $soapClient;

    /**
     * @var Json|MockObject
     */
    private $serializer;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var CurrencyFactory|MockObject
     */
    private $currencyFactory;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Curl
     */
    private $curlClient;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->helper = new ObjectManager($this);
        $this->scope = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->scope->expects($this->any())
            ->method('getValue')
            ->willReturnCallback([$this, 'scopeConfigGetValue']);

        $countryFactory = $this->getCountryFactory();
        $rateFactory = $this->getRateFactory();
        $storeManager = $this->getStoreManager();
        $resultFactory = $this->getResultFactory();
        $this->initRateErrorFactory();

        $rateMethodFactory = $this->getRateMethodFactory();

        $this->trackErrorFactory = $this->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->statusFactory = $this->getMockBuilder(StatusFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $elementFactory = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $regionFactory = $this->getMockBuilder(RegionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $data = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stockRegistry = $this->getMockBuilder(StockRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->curlFactory = $this->getMockBuilder(CurlFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->curlClient = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->setMethods(['setHeaders', 'getBody', 'post'])
            ->getMock();

        $this->carrier = $this->getMockBuilder(Carrier::class)
            ->setMethods(['_createSoapClient'])
            ->setConstructorArgs(
                [
                    'scopeConfig' => $this->scope,
                    'rateErrorFactory' => $this->errorFactory,
                    'logger' => $this->logger,
                    'xmlSecurity' => new Security(),
                    'xmlElFactory' => $elementFactory,
                    'rateFactory' => $rateFactory,
                    'rateMethodFactory' => $rateMethodFactory,
                    'trackFactory' => $resultFactory,
                    'trackErrorFactory' => $this->trackErrorFactory,
                    'trackStatusFactory' => $this->statusFactory,
                    'regionFactory' => $regionFactory,
                    'countryFactory' => $countryFactory,
                    'currencyFactory' => $this->currencyFactory,
                    'directoryData' => $data,
                    'stockRegistry' => $stockRegistry,
                    'storeManager' => $storeManager,
                    'configReader' => $reader,
                    'productCollectionFactory' => $collectionFactory,
                    'curlFactory' => $this->curlFactory,
                    'data' => [],
                    'serializer' => $this->serializer,
                ]
            )->getMock();
        $this->soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRates', 'track'])
            ->getMock();
        $this->carrier->method('_createSoapClient')
            ->willReturn($this->soapClient);
    }

    public function testSetRequestWithoutCity()
    {
        $request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDestCity'])
            ->getMock();
        $request->expects($this->once())
            ->method('getDestCity')
            ->willReturn(null);
        $this->carrier->setRequest($request);
    }

    public function testSetRequestWithCity()
    {
        $request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDestCity'])
            ->getMock();
        $request->expects($this->exactly(2))
            ->method('getDestCity')
            ->willReturn('Small Town');
        $this->carrier->setRequest($request);
    }

    /**
     * Callback function, emulates getValue function.
     *
     * @param string $path
     * @return string|null
     */
    public function scopeConfigGetValue(string $path)
    {
        $pathMap = [
            'carriers/fedex/showmethod' => 1,
            'carriers/fedex/allowed_methods' => 'ServiceType',
            'carriers/fedex/debug' => 1,
            'carriers/fedex/api_key' => 'TestApiKey',
            'carriers/fedex/secret_key' => 'TestSecretKey',
            'carriers/fedex/rest_sandbox_webservices_url' => 'https://REST_SANDBOX_URL/',
            'carriers/fedex/rest_production_webservices_url' => 'https://REST_PRODUCTION_URL/',
        ];

        return isset($pathMap[$path]) ? $pathMap[$path] : null;
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     * @param string $rateType
     * @param float $expected
     * @param int $callNum
     * @dataProvider collectRatesDataProvider
     */
    public function testCollectRatesRateAmountOriginBased(
        $amount,
        $currencyCode,
        $baseCurrencyCode,
        $rateType,
        $expected,
        $callNum = 1
    ) {
        $this->scope->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(true);

        // @codingStandardsIgnoreStart
        $netAmount = new \stdClass();
        $netAmount->Amount = $amount;
        $netAmount->Currency = $currencyCode;

        $totalNetCharge = new \stdClass();
        $totalNetCharge->TotalNetCharge = $netAmount;
        $totalNetCharge->RateType = $rateType;

        $ratedShipmentDetail = new \stdClass();
        $ratedShipmentDetail->ShipmentRateDetail = $totalNetCharge;

        $rate = new \stdClass();
        $rate->ServiceType = 'ServiceType';
        $rate->RatedShipmentDetails = [$ratedShipmentDetail];

        $response = new \stdClass();
        $response->HighestSeverity = 'SUCCESS';
        $response->RateReplyDetails = $rate;
        // @codingStandardsIgnoreEnd

        $this->serializer->method('serialize')
            ->willReturn('CollectRateString' . $amount);

        $rateCurrency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rateCurrency->method('load')
            ->willReturnSelf();
        $rateCurrency->method('getAnyRate')
            ->willReturnMap(
                [
                    ['USD', 1],
                    ['EUR', 0.75],
                    ['UNKNOWN', false]
                ]
            );

        if ($baseCurrencyCode === 'UNKNOWN') {
            $this->expectException(LocalizedException::class);
        }

        $this->currencyFactory->method('create')
            ->willReturn($rateCurrency);

        $baseCurrency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $baseCurrency->method('getCode')
            ->willReturn($baseCurrencyCode);

        $request = $this->getMockBuilder(RateRequest::class)
            ->setMethods(['getBaseCurrency'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('getBaseCurrency')
            ->willReturn($baseCurrency);

        $this->soapClient->expects($this->exactly($callNum))
            ->method('getRates')
            ->willReturn($response);

        $allRates1 = $this->carrier->collectRates($request)->getAllRates();
        foreach ($allRates1 as $rate) {
            $this->assertEquals($expected, $rate->getData('cost'));
        }
    }

    /**
     * Get list of rates variations
     * @return array
     */
    public function collectRatesDataProvider()
    {
        return [
            [10.0, 'USD', 'EUR', 'RATED_ACCOUNT_PACKAGE', 7.5],
            [10.0, 'USD', 'UNKNOWN', 'RATED_ACCOUNT_PACKAGE', null, 0],
            [10.0, 'USD', 'USD', 'RATED_ACCOUNT_PACKAGE', 10, 0],
            [11.50, 'USD', 'USD', 'PAYOR_ACCOUNT_PACKAGE', 11.5],
            [11.50, 'USD', 'USD', 'PAYOR_ACCOUNT_PACKAGE', 11.5, 0],
            [100.01, 'USD', 'USD', 'RATED_ACCOUNT_SHIPMENT', 100.01],
            [100.01, 'USD', 'USD', 'RATED_ACCOUNT_SHIPMENT', 100.01, 0],
            [32.2, 'USD', 'USD', 'PAYOR_ACCOUNT_SHIPMENT', 32.2],
            [32.2, 'USD', 'USD', 'PAYOR_ACCOUNT_SHIPMENT', 32.2, 0],
            [15.0, 'USD', 'USD', 'RATED_LIST_PACKAGE', 15],
            [15.0, 'USD', 'USD', 'RATED_LIST_PACKAGE', 15, 0],
            [123.25, 'USD', 'USD', 'PAYOR_LIST_PACKAGE', 123.25],
            [123.25, 'USD', 'USD', 'PAYOR_LIST_PACKAGE', 123.25, 0],
            [12.12, 'USD', 'USD', 'RATED_LIST_SHIPMENT', 12.12],
            [12.12, 'USD', 'USD', 'RATED_LIST_SHIPMENT', 12.12, 0],
            [38.9, 'USD', 'USD', 'PAYOR_LIST_SHIPMENT', 38.9],
            [38.9, 'USD', 'USD', 'PAYOR_LIST_SHIPMENT', 38.9, 0],
        ];
    }

    public function testCollectRatesErrorMessage()
    {
        $this->scope->expects($this->once())
            ->method('isSetFlag')
            ->willReturn(false);

        $this->error->expects($this->once())
            ->method('setCarrier')
            ->with('fedex');
        $this->error->expects($this->once())
            ->method('setCarrierTitle');
        $this->error->expects($this->once())
            ->method('setErrorMessage');

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertSame($this->error, $this->carrier->collectRates($request));
    }

    /**
     * @param string $data
     * @param array $maskFields
     * @param string $expected
     * @dataProvider logDataProvider
     */
    public function testFilterDebugData($data, array $maskFields, $expected)
    {
        $refClass = new \ReflectionClass(Carrier::class);
        $property = $refClass->getProperty('_debugReplacePrivateDataKeys');
        $property->setAccessible(true);
        $property->setValue($this->carrier, $maskFields);

        $refMethod = $refClass->getMethod('filterDebugData');
        $refMethod->setAccessible(true);
        $result = $refMethod->invoke($this->carrier, $data);
        $this->assertEquals($expected, $result);
    }

    /**
     * Get list of variations
     */
    public function logDataProvider()
    {
        return [
            [
                [
                    'WebAuthenticationDetail' => [
                        'UserCredential' => [
                            'Key' => 'testKey',
                            'Password' => 'testPassword',
                        ],
                    ],
                    'ClientDetail' => [
                        'AccountNumber' => 4121213,
                        'MeterNumber' => 'testMeterNumber',
                    ],
                ],
                ['Key', 'Password', 'MeterNumber'],
                [
                    'WebAuthenticationDetail' => [
                        'UserCredential' => [
                            'Key' => '****',
                            'Password' => '****',
                        ],
                    ],
                    'ClientDetail' => [
                        'AccountNumber' => 4121213,
                        'MeterNumber' => '****',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get Track Request
     * @param string $tracking
     * @response array
     */
    public function getTrackRequest(string $tracking) : array
    {
        return [
            'includeDetailedScans' => true,
            'trackingInfo' => [
                [
                    'trackingNumberInfo' => [
                        'trackingNumber'=> $tracking
                    ]
                ]
            ]
        ];
    }

    /**
     * Get Track error response
     */
    public function getTrackErrorResponse() : array
    {
        return [
                'transactionId' => '177a2d98-f68a-4c8e-9008-fc4a8d0aa57f',
                'errors' => [
                                [
                                    'code' => 'SYSTEM.UNEXPECTED.ERROR',
                                    'message' => 'The system has experienced an unexpected problem and is unable
                                                    to complete your request.  Please try again later.
                                                     We regret any inconvenience.',
                                ],
                        ],
          ];
    }

    /**
     * Test case for error in Track Response
     */
    public function testGetTrackingErrorResponse()
    {
        $tracking = '123456789012';
        $errorMessage = 'Tracking information is unavailable.';

        $trackRequest = $this->getTrackRequest($tracking);

        $trackResponse = $this->getTrackErrorResponse();
        $accessTokenResponse = $this->getAccessToken();

        $this->serializer->method('serialize')->willReturn(json_encode($trackRequest));
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnOnConsecutiveCalls($accessTokenResponse, $trackResponse);

        $error = $this->helper->getObject(Error::class);
        $this->trackErrorFactory->expects($this->once())
            ->method('create')
            ->willReturn($error);

        $this->carrier->getTracking($tracking);

        $tracks = $this->carrier->getResult()->getAllTrackings();

        $this->assertCount(1, $tracks);
        /** @var Error $current */
        $current = $tracks[0];
        $this->assertInstanceOf(Error::class, $current);
        $this->assertEquals(__($errorMessage), $current->getErrorMessage());
    }

    /**
     * Expected Track Response
     *
     * @param string $shipTimeStamp
     * @param string $expectedDate
     * @param string $expectedTime
     * @return array
     */
    public function getTrackResponse($shipTimeStamp, $expectedDate, $expectedTime) : array
    {
        $trackResponse = '{"transactionId":"4d37cd0c-f4e8-449f-ac95-d4d3132f0572",
        "output":{"completeTrackResults":[{"trackingNumber":"122816215025810","trackResults":[{"trackingNumberInfo":
        {"trackingNumber":"122816215025810","trackingNumberUniqueId":"12013~122816215025810~FDEG","carrierCode":"FDXG"},
        "additionalTrackingInfo":{"nickname":"","packageIdentifiers":[{"type":"CUSTOMER_REFERENCE","values":
        ["PO#174724"],"trackingNumberUniqueId":"","carrierCode":""}],"hasAssociatedShipments":false},
        "shipperInformation":{"address":{"city":"POST FALLS","stateOrProvinceCode":"ID","countryCode":"US",
        "residential":false,"countryName":"United States"}},"recipientInformation":{"address":{"city":"NORTON",
        "stateOrProvinceCode":"VA","countryCode":"US","residential":false,"countryName":"United States"}},
        "latestStatusDetail":{"code":"DL","derivedCode":"DL","statusByLocale":"Delivered","description":"Delivered",
        "scanLocation":{"city":"Norton","stateOrProvinceCode":"VA","countryCode":"US","residential":false,
        "countryName":"United States"}},"dateAndTimes":[{"type":"ACTUAL_DELIVERY","dateTime":
        "'.$expectedDate.'T'.$expectedTime.'"},{"type":"ACTUAL_PICKUP","dateTime":"2016-08-01T00:00:00-06:00"},
        {"type":"SHIP","dateTime":"'.$shipTimeStamp.'"}],"availableImages":[{"type":"SIGNATURE_PROOF_OF_DELIVERY"}],
        "specialHandlings":[{"type":"DIRECT_SIGNATURE_REQUIRED","description":"Direct Signature Required",
        "paymentType":"OTHER"}],"packageDetails":{"packagingDescription":{"type":"YOUR_PACKAGING","description":
        "Package"},"physicalPackagingType":"PACKAGE","sequenceNumber":"1","count":"1","weightAndDimensions":
        {"weight":[{"value":"21.5","unit":"LB"},{"value":"9.75","unit":"KG"}],"dimensions":[{"length":22,"width":17,
        "height":10,"units":"IN"},{"length":55,"width":43,"height":25,"units":"CM"}]},"packageContent":[]},
        "shipmentDetails":{"possessionStatus":true},"scanEvents":[{"date":"'.$expectedDate.'T'.$expectedTime.'",
        "eventType":"DL","eventDescription":"Delivered","exceptionCode":"","exceptionDescription":"","scanLocation":
        {"streetLines":[""],"city":"Norton","stateOrProvinceCode":"VA","postalCode":"24273","countryCode":"US",
        "residential":false,"countryName":"United States"},"locationType":"DELIVERY_LOCATION","derivedStatusCode":"DL",
        "derivedStatus":"Delivered"},{"date":"2014-01-09T04:18:00-05:00","eventType":"OD","eventDescription":
        "On FedEx vehicle for delivery","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":
        [""],"city":"KINGSPORT","stateOrProvinceCode":"TN","postalCode":"37663","countryCode":"US","residential":false,
        "countryName":"United States"},"locationId":"0376","locationType":"VEHICLE","derivedStatusCode":"IT",
        "derivedStatus":"In transit"},{"date":"2014-01-09T04:09:00-05:00","eventType":"AR","eventDescription":
        "At local FedEx facility","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":[""],
        "city":"KINGSPORT","stateOrProvinceCode":"TN","postalCode":"37663","countryCode":"US","residential":false,
        "countryName":"United States"},"locationId":"0376","locationType":"DESTINATION_FEDEX_FACILITY",
        "derivedStatusCode":"IT","derivedStatus":"In transit"},{"date":"2014-01-08T23:26:00-05:00","eventType":"IT",
        "eventDescription":"In transit","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":[""],
        "city":"KNOXVILLE","stateOrProvinceCode":"TN","postalCode":"37921","countryCode":"US","residential":false,
        "countryName":"United States"},"locationId":"0379","locationType":"FEDEX_FACILITY","derivedStatusCode":"IT",
        "derivedStatus":"In transit"},{"date":"2014-01-08T18:14:07-06:00","eventType":"DP","eventDescription":
        "Departed FedEx location","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":[""],
        "city":"NASHVILLE","stateOrProvinceCode":"TN","postalCode":"37207","countryCode":"US","residential":false,
        "countryName":"United States"},"locationId":"0371","locationType":"FEDEX_FACILITY","derivedStatusCode":"IT",
        "derivedStatus":"In transit"},{"date":"2014-01-08T15:16:00-06:00","eventType":"AR","eventDescription":
        "Arrived at FedEx location","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":[""],
        "city":"NASHVILLE","stateOrProvinceCode":"TN","postalCode":"37207","countryCode":"US","residential":false,
        "countryName":"United States"},"locationId":"0371","locationType":"FEDEX_FACILITY","derivedStatusCode":"IT",
        "derivedStatus":"In transit"},{"date":"2014-01-07T00:29:00-06:00","eventType":"AR","eventDescription":
        "Arrived at FedEx location","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":[""],
        "city":"CHICAGO","stateOrProvinceCode":"IL","postalCode":"60638","countryCode":"US","residential":false,
        "countryName":"United States"},"locationId":"0604","locationType":"FEDEX_FACILITY","derivedStatusCode":"IT",
        "derivedStatus":"In transit"},{"date":"2014-01-03T19:12:30-08:00","eventType":"DP","eventDescription":
        "Left FedEx origin facility","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":[""],
        "city":"SPOKANE","stateOrProvinceCode":"WA","postalCode":"99216","countryCode":"US","residential":false,
        "countryName":"United States"},"locationId":"0992","locationType":"ORIGIN_FEDEX_FACILITY","derivedStatusCode":
        "IT","derivedStatus":"In transit"},{"date":"2014-01-03T18:33:00-08:00","eventType":"AR","eventDescription":
        "Arrived at FedEx location","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":[""],
        "city":"SPOKANE","stateOrProvinceCode":"WA","postalCode":"99216","countryCode":"US","residential":false,
        "countryName":"United States"},"locationId":"0992","locationType":"FEDEX_FACILITY","derivedStatusCode":"IT",
        "derivedStatus":"In transit"},{"date":"2014-01-03T15:00:00-08:00","eventType":"PU","eventDescription":
        "Picked up","exceptionCode":"","exceptionDescription":"","scanLocation":{"streetLines":[""],"city":"SPOKANE",
        "stateOrProvinceCode":"WA","postalCode":"99216","countryCode":"US","residential":false,"countryName":
        "United States"},"locationId":"0992","locationType":"PICKUP_LOCATION","derivedStatusCode":"PU","derivedStatus":
        "Picked up"},{"date":"2014-01-03T14:31:00-08:00","eventType":"OC","eventDescription":
        "Shipment information sent to FedEx","exceptionCode":"","exceptionDescription":"","scanLocation":
        {"streetLines":[""],"postalCode":"83854","countryCode":"US","residential":false,"countryName":"United States"},
        "locationType":"CUSTOMER","derivedStatusCode":"IN","derivedStatus":"Initiated"}],"availableNotifications":
        ["ON_DELIVERY"],"deliveryDetails":{"actualDeliveryAddress":{"city":"Norton","stateOrProvinceCode":"VA",
        "countryCode":"US","residential":false,"countryName":"United States"},"locationType":"SHIPPING_RECEIVING",
        "locationDescription":"Shipping/Receiving","deliveryAttempts":"0","receivedByName":"ROLLINS",
        "deliveryOptionEligibilityDetails":[{"option":"INDIRECT_SIGNATURE_RELEASE","eligibility":"INELIGIBLE"},
        {"option":"REDIRECT_TO_HOLD_AT_LOCATION","eligibility":"INELIGIBLE"},{"option":"REROUTE","eligibility":
        "INELIGIBLE"},{"option":"RESCHEDULE","eligibility":"INELIGIBLE"},{"option":"RETURN_TO_SHIPPER","eligibility":
        "INELIGIBLE"},{"option":"DISPUTE_DELIVERY","eligibility":"INELIGIBLE"},{"option":"SUPPLEMENT_ADDRESS",
        "eligibility":"INELIGIBLE"}]},"originLocation":{"locationContactAndAddress":{"address":{"city":"SPOKANE",
        "stateOrProvinceCode":"WA","countryCode":"US","residential":false,"countryName":"United States"}}},
        "lastUpdatedDestinationAddress":{"city":"Norton","stateOrProvinceCode":"VA","countryCode":"US","residential":
        false,"countryName":"United States"},"serviceDetail":{"type":"FEDEX_GROUND","description":"FedEx Ground",
        "shortDescription":"FG"},"standardTransitTimeWindow":{"window":{"ends":"2016-08-01T00:00:00-06:00"}},
        "estimatedDeliveryTimeWindow":{"window":{}},"goodsClassificationCode":"","returnDetail":{}}]}]}}';

        return json_decode($trackResponse, true);
    }

    /**
     * get Access Token for Rest API
     */
    public function getAccessToken() : array
    {
        $accessTokenResponse = [
            'access_token' => 'TestAccessToken',
            'token_type'=>'bearer',
            'expires_in' => 3600,
            'scope'=>'CXS'
        ];

        $this->curlFactory->expects($this->any())->method('create')->willReturn($this->curlClient);
        $this->curlClient->expects($this->any())->method('setHeaders')->willReturnSelf();
        $this->curlClient->expects($this->any())->method('post')->willReturnSelf();
        $this->curlClient->expects($this->any())->method('getBody')->willReturnSelf();
        return $accessTokenResponse;
    }

    /**
     * @param string $tracking
     * @param string $shipTimeStamp
     * @param string $expectedDate
     * @param string $expectedTime
     * @dataProvider shipDateDataProvider
     */
    public function testGetTracking($tracking, $shipTimeStamp, $expectedDate, $expectedTime)
    {
        $trackRequest = $this->getTrackRequest($tracking);
        $trackResponse = $this->getTrackResponse($shipTimeStamp, $expectedDate, $expectedTime);
        $accessTokenResponse = $this->getAccessToken();

        $this->serializer->method('serialize')->willReturn(json_encode($trackRequest));
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnOnConsecutiveCalls($accessTokenResponse, $trackResponse);

        $status = $this->helper->getObject(Status::class);
        $this->statusFactory->method('create')
            ->willReturn($status);

        $tracks = $this->carrier->getTracking($tracking)->getAllTrackings();
        $this->assertCount(1, $tracks);

        $current = $tracks[0];
        $fields = [
            'signedby',
            'status',
            'service',
            'deliverylocation',
            'weight',
        ];
        array_walk($fields, function ($field) use ($current) {
            $this->assertNotEmpty($current[$field]);
        });

        $this->assertEquals($tracking, $current['tracking']);
        $this->assertEquals($expectedDate, $current['deliverydate']);
        $this->assertEquals($expectedTime, $current['deliverytime']);

        // assert track events
        $this->assertNotEmpty($current['progressdetail']);

        $event = $current['progressdetail'][0];
        $fields = ['activity', 'deliverylocation'];
        array_walk($fields, function ($field) use ($event) {
            $this->assertNotEmpty($event[$field]);
        });
        $this->assertEquals($expectedDate, $event['deliverydate']);
        $this->assertEquals($expectedTime, $event['deliverytime']);
    }

    /**
     * Gets list of variations for testing ship date.
     *
     * @return array
     */
    public function shipDateDataProvider() : array
    {
        return [
            'tracking1' => [
                'tracking1',
                'shipTimestamp' => '2020-08-15T02:06:35+03:00',
                'expectedDate' => '2014-01-09',
                '18:31:00',
                0,
            ],
            'tracking1-again' => [
                'tracking1',
                'shipTimestamp' => '2014-01-09T02:06:35+03:00',
                'expectedDate' => '2014-01-09',
                '18:31:00',
                0,
            ],
            'tracking2' => [
                'tracking2',
                'shipTimestamp' => '2014-01-09T02:06:35+03:00',
                'expectedDate' => '2014-01-09',
                '23:06:35',
            ],
            'tracking3' => [
                'tracking3',
                'shipTimestamp' => '2014-01-09T14:06:35',
                'expectedDate' => '2014-01-09',
                '18:31:00',
            ],
            'tracking4' => [
                'tracking4',
                'shipTimestamp' => '2016-08-05 14:06:35',
                'expectedDate' => null,
                null,
            ],
        ];
    }

    /**
     * Init RateErrorFactory and RateResultErrors mocks
     * @return void
     */
    private function initRateErrorFactory()
    {
        $this->error = $this->getMockBuilder(RateResultError::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();
        $this->errorFactory = $this->getMockBuilder(RateErrorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->errorFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->error);
    }

    /**
     * Creates mock rate result factory
     * @return RateResultFactory|MockObject
     */
    private function getRateFactory()
    {
        $rate = $this->getMockBuilder(RateResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['getError'])
            ->getMock();
        $rateFactory = $this->getMockBuilder(RateResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $rateFactory->expects($this->any())
            ->method('create')
            ->willReturn($rate);

        return $rateFactory;
    }

    /**
     * Creates mock object for CountryFactory class
     * @return CountryFactory|MockObject
     */
    private function getCountryFactory()
    {
        $country = $this->getMockBuilder(Country::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getData'])
            ->getMock();
        $country->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $countryFactory = $this->getMockBuilder(CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $countryFactory->expects($this->any())
            ->method('create')
            ->willReturn($country);

        return $countryFactory;
    }

    /**
     * Creates mock object for ResultFactory class
     * @return ResultFactory|MockObject
     */
    private function getResultFactory()
    {
        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->result = $this->helper->getObject(Result::class);
        $resultFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->result);

        return $resultFactory;
    }

    /**
     * Creates mock object for store manager
     * @return StoreManagerInterface|MockObject
     */
    private function getStoreManager()
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrencyCode'])
            ->getMock();
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        return $storeManager;
    }

    /**
     * Creates mock object for rate method factory
     * @return MethodFactory|MockObject
     */
    private function getRateMethodFactory()
    {
        $priceCurrency = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $rateMethod = $this->getMockBuilder(Method::class)
            ->setConstructorArgs(['priceCurrency' => $priceCurrency])
            ->setMethods(null)
            ->getMock();
        $rateMethodFactory = $this->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $rateMethodFactory->expects($this->any())
            ->method('create')
            ->willReturn($rateMethod);

        return $rateMethodFactory;
    }
}
