<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var \SoapClient|MockObject
     */
    private \SoapClient $soapClient;

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

        $reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

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
                    'productCollectionFactory' => $this->collectionFactory,
                    'data' => [],
                    'serializer' => $this->serializer,
                ]
            )->getMock();
        $this->soapClient = $this->getMockBuilder(\SoapClient::class)
            ->disableOriginalConstructor()
            ->addMethods(['processShipment', 'getRates', 'track'])
            ->getMock();
        $this->carrier->method('_createSoapClient')
            ->willReturn($this->soapClient);
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

    public function testRequestToShipmentSuccess(): void
    {
        $request = $this->getShipmentRequestMock();
        $customsValue = '100';
        $height = '1';
        $width = '1';
        $length = '1';
        $weightUnits = 'POUND';
        $orderItemId = '2';
        $orderItemPrice = '100';
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
        $request->expects($this->exactly(4))->method('getStoreId')->willReturn($storeId);
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

        $this->soapClient->expects($this->once())
            ->method('processShipment')
            ->willReturn($this->getProcessShipmentResponse());
        $this->carrier->expects($this->once())->method('_createSoapClient')->willReturn($this->soapClient);

        $product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCountryOfManufacture'])
            ->getMockForAbstractClass();
        $product->expects($this->any())->method('getCountryOfManufacture');
        $productCollection = $this->createMock(Collection::class);
        $productCollection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));
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
            ->setMethods(['getDestCity'])
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
    public function scopeConfigGetValue(string $path): ?string
    {
        $pathMap = [
            'carriers/fedex/showmethod' => 1,
            'carriers/fedex/allowed_methods' => 'ServiceType',
            'carriers/fedex/debug' => 1,
        ];

        return $pathMap[$path] ?? null;
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
    ): void {
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
    public function collectRatesDataProvider(): array
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
    public function logDataProvider(): array
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

    public function testGetTrackingErrorResponse(): void
    {
        $tracking = '123456789012';
        $errorMessage = 'Tracking information is unavailable.';

        // @codingStandardsIgnoreStart
        $response = new \stdClass();
        $response->HighestSeverity = 'ERROR';
        $response->Notifications = new \stdClass();
        $response->Notifications->Message = $errorMessage;
        // @codingStandardsIgnoreEnd

        $error = $this->helper->getObject(Error::class);
        $this->trackErrorFactory->expects($this->once())
            ->method('create')
            ->willReturn($error);
        $this->serializer->method('serialize')->willReturn('');
        $this->carrier->getTracking($tracking);
        $tracks = $this->carrier->getResult()->getAllTrackings();

        $this->assertCount(1, $tracks);

        /** @var Error $current */
        $current = $tracks[0];
        $this->assertInstanceOf(Error::class, $current);
        $this->assertEquals(__($errorMessage), $current->getErrorMessage());
    }

    /**
     * @param string $tracking
     * @param string $shipTimeStamp
     * @param string $expectedDate
     * @param string $expectedTime
     * @dataProvider shipDateDataProvider
     */
    public function testGetTracking($tracking, $shipTimeStamp, $expectedDate, $expectedTime, $callNum = 1): void
    {
        // @codingStandardsIgnoreStart
        $response = new \stdClass();
        $response->HighestSeverity = 'SUCCESS';
        $response->CompletedTrackDetails = new \stdClass();

        $trackDetails = new \stdClass();
        $trackDetails->ShipTimestamp = $shipTimeStamp;
        $trackDetails->DeliverySignatureName = 'signature';

        $trackDetails->StatusDetail = new \stdClass();
        $trackDetails->StatusDetail->Description = 'SUCCESS';

        $trackDetails->Service = new \stdClass();
        $trackDetails->Service->Description = 'ground';
        $trackDetails->EstimatedDeliveryTimestamp = $shipTimeStamp;

        $trackDetails->EstimatedDeliveryAddress = new \stdClass();
        $trackDetails->EstimatedDeliveryAddress->City = 'Culver City';
        $trackDetails->EstimatedDeliveryAddress->StateOrProvinceCode = 'CA';
        $trackDetails->EstimatedDeliveryAddress->CountryCode = 'US';

        $trackDetails->PackageWeight = new \stdClass();
        $trackDetails->PackageWeight->Value = 23;
        $trackDetails->PackageWeight->Units = 'LB';

        $response->CompletedTrackDetails->TrackDetails = [$trackDetails];
        // @codingStandardsIgnoreEnd

        $this->soapClient->expects($this->exactly($callNum))
            ->method('track')
            ->willReturn($response);

        $this->serializer->method('serialize')
            ->willReturn('TrackingString' . $tracking);

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

        $this->assertEquals($expectedDate, $current['deliverydate']);
        $this->assertEquals($expectedTime, $current['deliverytime']);
        $this->assertEquals($expectedDate, $current['shippeddate']);
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
                'shipTimestamp' => '2016-08-05T14:06:35+01:00',
                'expectedDate' => '2016-08-05',
                '13:06:35',
            ],
            'tracking1-again' => [
                'tracking1',
                'shipTimestamp' => '2016-08-05T02:06:35+03:00',
                'expectedDate' => '2016-08-05',
                '13:06:35',
                0,
            ],
            'tracking2' => [
                'tracking2',
                'shipTimestamp' => '2016-08-05T02:06:35+03:00',
                'expectedDate' => '2016-08-04',
                '23:06:35',
            ],
            'tracking3' => [
                'tracking3',
                'shipTimestamp' => '2016-08-05T14:06:35',
                'expectedDate' => '2016-08-05',
                '14:06:35',
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
     * @param string $tracking
     * @param string $shipTimeStamp
     * @param string $expectedDate
     * @param string $expectedTime
     * @param int $callNum
     * @dataProvider shipDateDataProvider
     */
    public function testGetTrackingWithEvents(
        $tracking,
        $shipTimeStamp,
        $expectedDate,
        $expectedTime,
        $callNum = 1
    ): void {
        $tracking = $tracking . 'WithEvent';

        // @codingStandardsIgnoreStart
        $response = new \stdClass();
        $response->HighestSeverity = 'SUCCESS';
        $response->CompletedTrackDetails = new \stdClass();

        $event = new \stdClass();
        $event->EventDescription = 'Test';
        $event->Timestamp = $shipTimeStamp;
        $event->Address = new \stdClass();

        $event->Address->City = 'Culver City';
        $event->Address->StateOrProvinceCode = 'CA';
        $event->Address->CountryCode = 'US';

        $trackDetails = new \stdClass();
        $trackDetails->Events = $event;

        $response->CompletedTrackDetails->TrackDetails = $trackDetails;
        // @codingStandardsIgnoreEnd

        $this->soapClient->expects($this->exactly($callNum))
            ->method('track')
            ->willReturn($response);

        $this->serializer->method('serialize')
            ->willReturn('TrackingWithEventsString' . $tracking);

        $status = $this->helper->getObject(Status::class);
        $this->statusFactory->method('create')
            ->willReturn($status);

        $this->carrier->getTracking($tracking);
        $tracks = $this->carrier->getResult()->getAllTrackings();
        $this->assertCount(1, $tracks);

        $current = $tracks[0];
        $this->assertNotEmpty($current['progressdetail']);
        $this->assertCount(1, $current['progressdetail']);

        $event = $current['progressdetail'][0];
        $fields = ['activity', 'deliverylocation'];
        array_walk($fields, function ($field) use ($event) {
            $this->assertNotEmpty($event[$field]);
        });
        $this->assertEquals($expectedDate, $event['deliverydate']);
        $this->assertEquals($expectedTime, $event['deliverytime']);
    }

    /**
     * Init RateErrorFactory and RateResultErrors mocks
     * @return void
     */
    private function initRateErrorFactory(): void
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

    /**
     * @return \stdClass
     */
    private function getProcessShipmentResponse(): \stdClass
    {
        $response = new \stdClass();
        $response->HighestSeverity = 'NORMAL';
        $shipmentDetail = new \stdClass();
        $packageDetail = new \stdClass();
        $label = new \stdClass();
        $image = new \stdClass();
        $parts = new \stdClass();
        $parts->Image = $image;
        $label->Parts = $parts;
        $packageDetail->Label = $label;
        $trackingNumber = new \stdClass();
        $trackingNumber->TrackingNumber = 1;
        $packageDetail->TrackingIds = [$trackingNumber];
        $shipmentDetail->CompletedPackageDetails = $packageDetail;
        $response->CompletedShipmentDetail = $shipmentDetail;

        return $response;
    }

    /**
     * @return Request&MockObject|MockObject
     */
    public function getShipmentRequestMock(): MockObject
    {
        $request = $this->getMockBuilder(Request::class)
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

        return $request;
    }
}
