<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Fedex\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
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
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error as RateResultError;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Sales\Model\Order;
use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Magento\Shipping\Model\Shipment\Request;
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
use Magento\Catalog\Api\Data\ProductInterface;

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
    private ObjectManager $helper;

    /**
     * @var Carrier|MockObject
     */
    private Carrier $carrier;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $scope;

    /**
     * @var RateResultError|MockObject
     */
    private RateResultError $error;

    /**
     * @var RateErrorFactory|MockObject
     */
    private RateErrorFactory $errorFactory;

    /**
     * @var ErrorFactory|MockObject
     */
    private ErrorFactory $trackErrorFactory;

    /**
     * @var StatusFactory|MockObject
     */
    private StatusFactory $statusFactory;

    /**
     * @var Result|MockObject
     */
    private Result $result;

    /**
     * @var Json|MockObject
     */
    private Json $serializer;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var CurrencyFactory|MockObject
     */
    private CurrencyFactory $currencyFactory;

    /**
     * @var CollectionFactory|CollectionFactory&MockObject|MockObject
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @var Curl
     */
    private Curl $curlClient;

    /**
     * @var DecoderInterface
     */
    private DecoderInterface $decoderInterface;

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
            ->onlyMethods(['create'])
            ->getMock();

        $this->statusFactory = $this->getMockBuilder(StatusFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $elementFactory = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
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

        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->curlFactory = $this->getMockBuilder(CurlFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->curlClient = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setHeaders', 'getBody', 'post'])
            ->getMock();

        $this->decoderInterface = $this->getMockBuilder(DecoderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['decode'])
            ->getMock();

        $this->carrier = $this->getMockBuilder(Carrier::class)
            ->addMethods(['rateRequest'])
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
                    'productCollectionFactory' => $this->collectionFactory,
                    'curlFactory' => $this->curlFactory,
                    'decoderInterface' => $this->decoderInterface,
                    'data' => [],
                    'serializer' => $this->serializer,
                ]
            )->getMock();
    }

    public function testRequestToShipmentExceptionNoPackages(): void
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPackages'])
            ->getMock();
        $request->expects($this->once())->method('getPackages')->willReturn(null);
        $this->expectException(LocalizedException::class);

        $this->carrier->requestToShipment($request);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testRequestToShipmentSuccess(): void
    {
        $request = $this->getShipmentRequestMock();
        $customsValue = $orderItemPrice = '100';
        $height = $width = $length = '1';
        $weightUnits = 'POUND';
        $orderItemId = '2';
        $items = [
            $orderItemId => [
                'qty' => 1,
                'customs_value' => $customsValue,
                'price' => '',
                'name' => '',
                'weight' => '',
                'product_id' => '',
                'order_item_id' => ''
            ]
        ];
        $packages = [
            1 => [
                'params' => [
                    'container' => 'YOUR_PACKAGING',
                    'weight' => '100',
                    'customs_value' => '100',
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'weight_units' => $weightUnits,
                    'dimension_units' => 'INCH',
                    'content_type' => '',
                    'content_type_other' => '',
                    'delivery_confirmation' => 'NO_SIGNATURE_REQUIRED'
                ],
                'items' => $items
            ]
        ];
        $storeId = 1;
        $phoneNumber = '1234567890';
        $request->expects($this->once())->method('getPackages')->willReturn($packages);
        $request->expects($this->exactly(3))->method('getStoreId')->willReturn($storeId);
        $request->expects($this->once())->method('setPackageId');
        $request->expects($this->once())->method('setPackagingType');
        $request->expects($this->once())->method('setPackageWeight');
        $request->expects($this->once())->method('setPackageParams');
        $request->expects($this->once())->method('setPackageItems');
        $request->expects($this->exactly(2))->method('getShipperContactPhoneNumber')->willReturn($phoneNumber);
        $request->expects($this->once())->method('setShipperContactPhoneNumber')->with($phoneNumber);
        $request->expects($this->exactly(2))->method('getRecipientContactPhoneNumber')->willReturn($phoneNumber);
        $request->expects($this->once())->method('setRecipientContactPhoneNumber')->with($phoneNumber);
        $request->expects($this->exactly(2))->method('getReferenceData')->willReturn('Reference data');
        $request->expects($this->once())->method('getPackageItems')->willReturn($items);

        $orderShipment = $this->createMock(\Magento\Sales\Model\Order\Shipment::class);
        $order = $this->createMock(Order::class);
        $orderItem = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPrice', 'getName', 'getProductId'])
            ->getMock();
        $orderItem->expects($this->once())->method('getPrice')->willReturn($orderItemPrice);
        $orderItem->expects($this->once())->method('getName')->willReturn('Simple product');
        $orderItem->expects($this->once())->method('getProductId')->willReturn(1);
        $order->expects($this->once())->method('getItemById')->with($orderItemId)->willReturn($orderItem);
        $orderShipment->expects($this->once())->method('getOrder')->willReturn($order);
        $request->expects($this->once())->method('getOrderShipment')->willReturn($orderShipment);

        $packageParams = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomsValue', 'getHeight', 'getWidth', 'getLength', 'getWeightUnits'])
            ->getMock();
        $packageParams->expects($this->once())->method('getCustomsValue')->willReturn($customsValue);
        $packageParams->expects($this->once())->method('getHeight')->willReturn($height);
        $packageParams->expects($this->once())->method('getWidth')->willReturn($width);
        $packageParams->expects($this->once())->method('getLength')->willReturn($length);
        $packageParams->expects($this->once())->method('getWeightUnits')->willReturn($weightUnits);
        $request->expects($this->once())->method('getPackageParams')->willReturn($packageParams);

        $this->serializer
            ->method('unserialize')
            ->willReturnOnConsecutiveCalls($this->getAccessToken(), $this->getProcessShipmentResponse());
        $this->serializer->method('serialize')->willReturn('');
        $this->curlFactory->expects($this->any())->method('create')->willReturn($this->curlClient);
        $this->curlClient->expects($this->any())->method('getBody')->willReturnSelf();

        $product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCountryOfManufacture'])
            ->getMockForAbstractClass();
        $product->expects($this->any())->method('getCountryOfManufacture');
        $productCollection = $this->createMock(Collection::class);
        $productCollection->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([]));
        $productCollection->expects($this->once())->method('addStoreFilter')->willReturn($productCollection);
        $productCollection->expects($this->once())->method('addFieldToFilter')->willReturn($productCollection);
        $productCollection->expects($this->once())->method('addAttributeToSelect')->willReturn($productCollection);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($productCollection);

        $this->carrier->requestToShipment($request);
    }

    public function testSetRequestWithoutCity(): void
    {
        $request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDestCity'])
            ->getMock();
        $request->expects($this->once())
            ->method('getDestCity')
            ->willReturn(null);
        $this->carrier->setRequest($request);
    }

    public function testSetRequestWithCity(): void
    {
        $request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->addMethods(['getDestCity'])
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
     * @return int|string|null
     */
    public function scopeConfigGetValue(string $path): int|string|null
    {
        $pathMap = [
            'carriers/fedex/showmethod' => 1,
            'carriers/fedex/allowed_methods' => 'ServiceType',
            'carriers/fedex/debug' => 1,
            'carriers/fedex/api_key' => 'TestApiKey',
            'carriers/fedex/secret_key' => 'TestSecretKey',
            'carriers/fedex/rest_sandbox_webservices_url' => 'https://rest.sandbox.url/',
            'carriers/fedex/rest_production_webservices_url' => 'https://rest.production.url/',
        ];

        return $pathMap[$path] ?? null;
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     * @param string $rateType
     * @param float $expected
     * @dataProvider collectRatesDataProvider
     */
    public function testCollectRatesRateAmountOriginBased(
        $amount,
        $currencyCode,
        $baseCurrencyCode,
        $rateType,
        $expected
    ): void {
        $this->scope->expects($this->any())
            ->method('isSetFlag')
            ->willReturn(true);

        $accessTokenResponse = $this->getAccessToken();
        $rateResponse = $this->getRateResponse($amount, $currencyCode, $rateType);

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
            ->addMethods(['getBaseCurrency'])
            ->disableOriginalConstructor()
            ->getMock();
        $request->method('getBaseCurrency')
            ->willReturn($baseCurrency);

        $this->curlFactory->expects($this->any())->method('create')->willReturn($this->curlClient);
        $this->curlClient->expects($this->any())->method('getBody')->willReturnSelf();

        $this->serializer
            ->method('unserialize')
            ->willReturnOnConsecutiveCalls($accessTokenResponse, $rateResponse);

        $allRates1 = $this->carrier->collectRates($request)->getAllRates();
        foreach ($allRates1 as $rate) {
            $this->assertEquals($expected, $rate->getData('cost'));
        }
    }

    /**
     * Get list of rates variations
     * @return array
     */
    public static function collectRatesDataProvider(): array
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

    public function testCollectRatesErrorMessage(): void
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
     * @throws \ReflectionException
     */
    public function testFilterDebugData($data, array $maskFields, $expected): void
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
    public static function logDataProvider(): array
    {
        return [
            [
                [
                    'client_id' => 'testClientId',
                    'client_secret' => 'testClientSecret'
                ],
                ['client_id', 'client_secret'],
                [
                    'client_id' => '****',
                    'client_secret' => '****'
                ],
            ],
        ];
    }

    /**
     * Get Track Request
     * @param string $tracking
     * @return array
     */
    public static function getTrackRequest(string $tracking): array
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
     * @return array
     */
    public function getTrackErrorResponse(): array
    {
        return [
                'transactionId' => '177a2d98-f68a-4c8e-9008-fc4a8d0aa57f',
                'errors' => [
                                [
                                    'code' => 'SYSTEM.UNEXPECTED.ERROR',
                                    'message' => 'The system has experienced an unexpected problem and is unable
                                                    to complete your request. Please try again later.
                                                     We regret any inconvenience.',
                                ],
                        ],
          ];
    }

    /**
     * Test case for error in Track Response
     */
    public function testGetTrackingErrorResponse(): void
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
    public function getTrackResponse($shipTimeStamp, $expectedDate, $expectedTime): array
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
     * Expected Rate Response
     *
     * @param string $amount
     * @param string $currencyCode
     * @param string $rateType
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getRateResponse($amount, $currencyCode, $rateType): array
    {
        $rateResponse = '{"transactionId":"9eb0f436-8bb1-4200-b951-ae10442489f3","output":{"alerts":[{"code":
        "ORIGIN.STATEORPROVINCECODE.CHANGED","message":"The origin state/province code has been changed.",
        "alertType":"NOTE"},{"code":"DESTINATION.STATEORPROVINCECODE.CHANGED","message":
        "The destination state/province code has been changed.","alertType":"NOTE"}],"rateReplyDetails":
        [{"serviceType":"FIRST_OVERNIGHT","serviceName":"FedEx First Overnight®","packagingType":"YOUR_PACKAGING",
        "ratedShipmentDetails":[{"rateType":"LIST","ratedWeightMethod":"ACTUAL","totalDiscounts":0.0,"totalBaseCharge"
        :276.19,"totalNetCharge":290.0,"totalNetFedExCharge":290.0,"shipmentRateDetail":{"rateZone":"05",
        "dimDivisor":0,"fuelSurchargePercent":5.0,"totalSurcharges":13.81,"totalFreightDiscount":0.0,"surCharges":
        [{"type":"FUEL","description":"Fuel Surcharge","amount":13.81}],"pricingCode":"PACKAGE","totalBillingWeight":
        {"units":"KG","value":10.0},"currency":"USD","rateScale":"12"},"ratedPackages":[{"groupNumber":0,
        "effectiveNetDiscount":0.0,"packageRateDetail":{"rateType":"PAYOR_LIST_PACKAGE","ratedWeightMethod":"ACTUAL",
        "baseCharge":276.19,"netFreight":276.19,"totalSurcharges":13.81,"netFedExCharge":290.0,"totalTaxes":0.0,
        "netCharge":290.0,"totalRebates":0.0,"billingWeight":{"units":"KG","value":10.0},"totalFreightDiscounts":0.0,
        "surcharges":[{"type":"FUEL","description":"Fuel Surcharge","amount":13.81}],"currency":"USD"}}],
        "currency":"USD"}],"operationalDetail":{"ineligibleForMoneyBackGuarantee":false,"astraDescription":"1ST OVR",
        "airportId":"ELP","serviceCode":"06"},"signatureOptionType":"SERVICE_DEFAULT","serviceDescription":
        {"serviceId":"EP1000000006","serviceType":"FIRST_OVERNIGHT","code":"06","names":[{"type":"long",
        "encoding":"utf-8","value":"FedEx First Overnight®"},{"type":"long","encoding":"ascii","value":
        "FedEx First Overnight"},{"type":"medium","encoding":"utf-8","value":"FedEx First Overnight®"},
        {"type":"medium","encoding":"ascii","value":"FedEx First Overnight"},{"type":"short","encoding":
        "utf-8","value":"FO"},{"type":"short","encoding":"ascii","value":"FO"},{"type":"abbrv","encoding":"ascii",
        "value":"FO"}],"serviceCategory":"parcel","description":"First Overnight","astraDescription":"1ST OVR"}},
        {"serviceType":"PRIORITY_OVERNIGHT","serviceName":"FedEx Priority Overnight®","packagingType":"YOUR_PACKAGING",
        "ratedShipmentDetails":[{"rateType":"LIST","ratedWeightMethod":"ACTUAL","totalDiscounts":0.0,
        "totalBaseCharge":245.19,"totalNetCharge":257.45,"totalNetFedExCharge":257.45,"shipmentRateDetail":
        {"rateZone":"05","dimDivisor":0,"fuelSurchargePercent":5.0,"totalSurcharges":12.26,"totalFreightDiscount":0.0,
        "surCharges":[{"type":"FUEL","description":"Fuel Surcharge","amount":12.26}],"pricingCode":"PACKAGE",
        "totalBillingWeight":{"units":"KG","value":10.0},"currency":"USD","rateScale":"1552"},"ratedPackages":
        [{"groupNumber":0,"effectiveNetDiscount":0.0,"packageRateDetail":{"rateType":"PAYOR_LIST_PACKAGE",
        "ratedWeightMethod":"ACTUAL","baseCharge":245.19,"netFreight":245.19,"totalSurcharges":12.26,"netFedExCharge":
        257.45,"totalTaxes":0.0,"netCharge":257.45,"totalRebates":0.0,"billingWeight":{"units":"KG","value":10.0},
        "totalFreightDiscounts":0.0,"surcharges":[{"type":"FUEL","description":"Fuel Surcharge","amount":12.26}],
        "currency":"USD"}}],"currency":"USD"}],"operationalDetail":{"ineligibleForMoneyBackGuarantee":false,
        "astraDescription":"P1","airportId":"ELP","serviceCode":"01"},"signatureOptionType":"SERVICE_DEFAULT",
        "serviceDescription":{"serviceId":"EP1000000002","serviceType":"PRIORITY_OVERNIGHT","code":"01",
        "names":[{"type":"long","encoding":"utf-8","value":"FedEx Priority Overnight®"},{"type":"long",
        "encoding":"ascii","value":"FedEx Priority Overnight"},{"type":"medium","encoding":"utf-8","value":
        "FedEx Priority Overnight®"},{"type":"medium","encoding":"ascii","value":"FedEx Priority Overnight"},
        {"type":"short","encoding":"utf-8","value":"P-1"},{"type":"short","encoding":"ascii","value":"P-1"},
        {"type":"abbrv","encoding":"ascii","value":"PO"}],"serviceCategory":"parcel","description":
        "Priority Overnight","astraDescription":"P1"}},{"serviceType":"STANDARD_OVERNIGHT","serviceName":
        "FedEx Standard Overnight®","packagingType":"YOUR_PACKAGING","ratedShipmentDetails":[{"rateType":"LIST",
        "ratedWeightMethod":"ACTUAL","totalDiscounts":0.0,"totalBaseCharge":235.26,"totalNetCharge":247.02,
        "totalNetFedExCharge":247.02,"shipmentRateDetail":{"rateZone":"05","dimDivisor":0,"fuelSurchargePercent":5.0,
        "totalSurcharges":11.76,"totalFreightDiscount":0.0,"surCharges":[{"type":"FUEL","description":"Fuel Surcharge",
        "amount":11.76}],"pricingCode":"PACKAGE","totalBillingWeight":{"units":"KG","value":10.0},"currency":"USD",
        "rateScale":"1349"},"ratedPackages":[{"groupNumber":0,"effectiveNetDiscount":0.0,"packageRateDetail":
        {"rateType":"PAYOR_LIST_PACKAGE","ratedWeightMethod":"ACTUAL","baseCharge":235.26,"netFreight":235.26,
        "totalSurcharges":11.76,"netFedExCharge":247.02,"totalTaxes":0.0,"netCharge":247.02,"totalRebates":0.0,
        "billingWeight":{"units":"KG","value":10.0},"totalFreightDiscounts":0.0,"surcharges":[{"type":"FUEL",
        "description":"Fuel Surcharge","amount":11.76}],"currency":"USD"}}],"currency":"USD"}],"operationalDetail":
        {"ineligibleForMoneyBackGuarantee":false,"astraDescription":"STD OVR","airportId":"ELP","serviceCode":"05"},
        "signatureOptionType":"SERVICE_DEFAULT","serviceDescription":{"serviceId":"EP1000000005","serviceType":
        "STANDARD_OVERNIGHT","code":"05","names":[{"type":"long","encoding":"utf-8","value":"FedEx Standard Overnight®"}
        ,{"type":"long","encoding":"ascii","value":"FedEx Standard Overnight"},{"type":"medium","encoding":"utf-8",
        "value":"FedEx Standard Overnight®"},{"type":"medium","encoding":"ascii","value":"FedEx Standard Overnight"},
        {"type":"short","encoding":"utf-8","value":"SOS"},{"type":"short","encoding":"ascii","value":"SOS"},{"type":
        "abbrv","encoding":"ascii","value":"SO"}],"serviceCategory":"parcel","description":"Standard Overnight",
        "astraDescription":"STD OVR"}},{"serviceType":"FEDEX_2_DAY_AM","serviceName":"FedEx 2Day® AM","packagingType":
        "YOUR_PACKAGING","ratedShipmentDetails":[{"rateType":"LIST","ratedWeightMethod":"ACTUAL","totalDiscounts":0.0,
        "totalBaseCharge":142.78,"totalNetCharge":149.92,"totalNetFedExCharge":149.92,"shipmentRateDetail":{"rateZone":
        "05","dimDivisor":0,"fuelSurchargePercent":5.0,"totalSurcharges":7.14,"totalFreightDiscount":0.0,"surCharges":
        [{"type":"FUEL","description":"Fuel Surcharge","amount":7.14}],"pricingCode":"PACKAGE","totalBillingWeight":
        {"units":"KG","value":10.0},"currency":"USD","rateScale":"10"},"ratedPackages":[{"groupNumber":0,
        "effectiveNetDiscount":0.0,"packageRateDetail":{"rateType":"PAYOR_LIST_PACKAGE","ratedWeightMethod":"ACTUAL",
        "baseCharge":142.78,"netFreight":142.78,"totalSurcharges":7.14,"netFedExCharge":149.92,"totalTaxes":0.0,
        "netCharge":149.92,"totalRebates":0.0,"billingWeight":{"units":"KG","value":10.0},"totalFreightDiscounts":0.0,
        "surcharges":[{"type":"FUEL","description":"Fuel Surcharge","amount":7.14}],"currency":"USD"}}],"currency":
        "USD"}],"operationalDetail":{"ineligibleForMoneyBackGuarantee":false,"astraDescription":"2DAY AM","airportId":
        "ELP","serviceCode":"49"},"signatureOptionType":"SERVICE_DEFAULT","serviceDescription":{"serviceId":
        "EP1000000023","serviceType":"FEDEX_2_DAY_AM","code":"49","names":[{"type":"long","encoding":"utf-8","value":
        "FedEx 2Day® AM"},{"type":"long","encoding":"ascii","value":"FedEx 2Day AM"},{"type":"medium","encoding":
        "utf-8","value":"FedEx 2Day® AM"},{"type":"medium","encoding":"ascii","value":"FedEx 2Day AM"},{"type":"short",
        "encoding":"utf-8","value":"E2AM"},{"type":"short","encoding":"ascii","value":"E2AM"},{"type":"abbrv",
        "encoding":"ascii","value":"TA"}],"serviceCategory":"parcel","description":"2DAY AM","astraDescription":
        "2DAY AM"}},{"serviceType":"FEDEX_2_DAY","serviceName":"FedEx 2Day®","packagingType":"YOUR_PACKAGING",
        "ratedShipmentDetails":[{"rateType":"LIST","ratedWeightMethod":"ACTUAL","totalDiscounts":0.0,"totalBaseCharge":
        116.68,"totalNetCharge":122.51,"totalNetFedExCharge":122.51,"shipmentRateDetail":{"rateZone":"05","dimDivisor":
        0,"fuelSurchargePercent":5.0,"totalSurcharges":5.83,"totalFreightDiscount":0.0,"surCharges":[{"type":"FUEL",
        "description":"Fuel Surcharge","amount":5.83}],"pricingCode":"PACKAGE","totalBillingWeight":{"units":"KG",
        "value":10.0},"currency":"USD","rateScale":"6046"},"ratedPackages":[{"groupNumber":0,"effectiveNetDiscount":0.0,
        "packageRateDetail":{"rateType":"PAYOR_LIST_PACKAGE","ratedWeightMethod":"ACTUAL","baseCharge":116.68,
        "netFreight":116.68,"totalSurcharges":5.83,"netFedExCharge":122.51,"totalTaxes":0.0,"netCharge":122.51,
        "totalRebates":0.0,"billingWeight":{"units":"KG","value":10.0},"totalFreightDiscounts":0.0,"surcharges":
        [{"type":"FUEL","description":"Fuel Surcharge","amount":5.83}],"currency":"USD"}}],"currency":"USD"}],
        "operationalDetail":{"ineligibleForMoneyBackGuarantee":false,"astraDescription":"E2","airportId":"ELP",
        "serviceCode":"03"},"signatureOptionType":"SERVICE_DEFAULT","serviceDescription":{"serviceId":"EP1000000003",
        "serviceType":"FEDEX_2_DAY","code":"03","names":[{"type":"long","encoding":"utf-8","value":"FedEx 2Day®"},
        {"type":"long","encoding":"ascii","value":"FedEx 2Day"},{"type":"medium","encoding":"utf-8","value":
        "FedEx 2Day®"},{"type":"medium","encoding":"ascii","value":"FedEx 2Day"},{"type":"short","encoding":"utf-8",
        "value":"P-2"},{"type":"short","encoding":"ascii","value":"P-2"},{"type":"abbrv","encoding":"ascii","value":
        "ES"}],"serviceCategory":"parcel","description":"2Day","astraDescription":"E2"}},{"serviceType":
        "FEDEX_EXPRESS_SAVER","serviceName":"FedEx Express Saver®","packagingType":"YOUR_PACKAGING",
        "ratedShipmentDetails":[{"rateType":"LIST","ratedWeightMethod":"ACTUAL","totalDiscounts":0.0,"totalBaseCharge"
        :90.25,"totalNetCharge":94.76,"totalNetFedExCharge":94.76,"shipmentRateDetail":{"rateZone":"05","dimDivisor":0,
        "fuelSurchargePercent":5.0,"totalSurcharges":4.51,"totalFreightDiscount":0.0,"surCharges":[{"type":"FUEL",
        "description":"Fuel Surcharge","amount":4.51}],"pricingCode":"PACKAGE","totalBillingWeight":{"units":"KG",
        "value":10.0},"currency":"USD","rateScale":"7173"},"ratedPackages":[{"groupNumber":0,"effectiveNetDiscount":0.0,
        "packageRateDetail":{"rateType":"PAYOR_LIST_PACKAGE","ratedWeightMethod":"ACTUAL","baseCharge":90.25,
        "netFreight":90.25,"totalSurcharges":4.51,"netFedExCharge":94.76,"totalTaxes":0.0,"netCharge":94.76,
        "totalRebates":0.0,"billingWeight":{"units":"KG","value":10.0},"totalFreightDiscounts":0.0,"surcharges":
        [{"type":"FUEL","description":"Fuel Surcharge","amount":4.51}],"currency":"USD"}}],"currency":"USD"}],
        "operationalDetail":{"ineligibleForMoneyBackGuarantee":false,"astraDescription":"XS","airportId":"ELP",
        "serviceCode":"20"},"signatureOptionType":"SERVICE_DEFAULT","serviceDescription":{"serviceId":"EP1000000013",
        "serviceType":"FEDEX_EXPRESS_SAVER","code":"20","names":[{"type":"long","encoding":"utf-8","value":
        "FedEx Express Saver®"},{"type":"long","encoding":"ascii","value":"FedEx Express Saver"},{"type":"medium",
        "encoding":"utf-8","value":"FedEx Express Saver®"},{"type":"medium","encoding":"ascii","value":
        "FedEx Express Saver"}],"serviceCategory":"parcel","description":"Express Saver","astraDescription":"XS"}},
        {"serviceType":"ServiceType","serviceName":"FedEx Ground®","packagingType":"YOUR_PACKAGING",
        "ratedShipmentDetails":[{"rateType":"LIST","ratedWeightMethod":"ACTUAL","totalDiscounts":0.0,"totalBaseCharge":
        24.26,"totalNetCharge":'.$amount.',"totalNetFedExCharge":28.75,"shipmentRateDetail":{"rateZone":"5","dimDivisor"
        :0,"fuelSurchargePercent":18.5,"totalSurcharges":4.49,"totalFreightDiscount":0.0,"surCharges":[{"type":"FUEL",
        "description":"Fuel Surcharge","level":"PACKAGE","amount":4.49}],"totalBillingWeight":{"units":"LB","value":
        23.0},"currency":"'.$currencyCode.'"},"ratedPackages":[{"groupNumber":0,"effectiveNetDiscount":0.0,
        "packageRateDetail":{"rateType":"'.$rateType.'","ratedWeightMethod":"ACTUAL","baseCharge":24.26,"netFreight":
        24.26,"totalSurcharges":4.49,"netFedExCharge":28.75,"totalTaxes":0.0,"netCharge":28.75,"totalRebates":0.0,
        "billingWeight":{"units":"KG","value":10.43},"totalFreightDiscounts":0.0,"surcharges":[{"type":"FUEL",
        "description":"Fuel Surcharge","level":"PACKAGE","amount":4.49}],"currency":"USD"}}],"currency":"USD"}],
        "operationalDetail":{"ineligibleForMoneyBackGuarantee":false,"astraDescription":"FXG","airportId":"ELP",
        "serviceCode":"92"},"signatureOptionType":"SERVICE_DEFAULT","serviceDescription":{"serviceId":"EP1000000134",
        "serviceType":"FEDEX_GROUND","code":"92","names":[{"type":"long","encoding":"utf-8","value":"FedEx Ground®"},
        {"type":"long","encoding":"ascii","value":"FedEx Ground"},{"type":"medium","encoding":"utf-8","value":"Ground®"}
        ,{"type":"medium","encoding":"ascii","value":"Ground"},{"type":"short","encoding":"utf-8","value":"FG"},
        {"type":"short","encoding":"ascii","value":"FG"},{"type":"abbrv","encoding":"ascii","value":"SG"}],
        "description":"FedEx Ground","astraDescription":"FXG"}}],"quoteDate":"2023-07-13","encoded":false}}';
        return json_decode($rateResponse, true);
    }

    /**
     * get Access Token for Rest API
     */
    public function getAccessToken(): array
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
        $this->curlClient->expects($this->any())->method('getBody')->willReturn(json_encode($accessTokenResponse));
        return $accessTokenResponse;
    }

    /**
     * @param string $tracking
     * @param string $shipTimeStamp
     * @param string $expectedDate
     * @param string $expectedTime
     * @dataProvider shipDateDataProvider
     */
    public function testGetTracking($tracking, $shipTimeStamp, $expectedDate, $expectedTime): void
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
    public function shipDateDataProvider(): array
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
            'tracking5' => [
                'tracking5',
                'shipTimestamp' => '2016-08-05 14:06:35+00:00',
                'expectedDate' => null,
                null,
            ],
            'tracking6' => [
                'tracking6',
                'shipTimestamp' => '2016-08-05',
                'expectedDate' => null,
                null,
            ],
            'tracking7' => [
                'tracking7',
                'shipTimestamp' => '2016/08/05',
                'expectedDate' => null,
                null
            ],
        ];
    }

    /**
     * Init RateErrorFactory and RateResultErrors mocks
     * @return void
     */
    private function initRateErrorFactory(): void
    {
        $this->error = $this->getMockBuilder(RateResultError::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();
        $this->errorFactory = $this->getMockBuilder(RateErrorFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
            ->onlyMethods(['getError'])
            ->getMock();
        $rateFactory = $this->getMockBuilder(RateResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
            ->onlyMethods(['load', 'getData'])
            ->getMock();
        $country->expects($this->any())
            ->method('load')
            ->willReturnSelf();

        $countryFactory = $this->getMockBuilder(CountryFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
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
            ->onlyMethods(['create'])
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
            ->onlyMethods(['getBaseCurrencyCode'])
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
            ->onlyMethods([])
            ->getMock();
        $rateMethodFactory = $this->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $rateMethodFactory->expects($this->any())
            ->method('create')
            ->willReturn($rateMethod);

        return $rateMethodFactory;
    }

    /**
     * @return array
     */
    private function getProcessShipmentResponse(): array
    {
        return [
            'output' => [
                'transactionShipments' => [
                    0 => [
                        'pieceResponses' => [
                            0 => [
                                'packageDocuments' => [
                                    0 => ['encodedLabel' => 'label']
                                ],
                                'trackingNumber' => '123'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @return Request&MockObject|MockObject
     */
    private function getShipmentRequestMock(): MockObject
    {
        return $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->addMethods([
                'getPackages',
                'getStoreId',
                'setPackageId',
                'setPackagingType',
                'setPackageWeight',
                'setPackageParams',
                'setPackageItems',
                'getShipperContactPhoneNumber',
                'setShipperContactPhoneNumber',
                'getRecipientContactPhoneNumber',
                'setRecipientContactPhoneNumber',
                'getReferenceData',
                'getPackageItems',
                'getOrderShipment',
                'getPackageParams'
            ])
            ->getMock();
    }
}
