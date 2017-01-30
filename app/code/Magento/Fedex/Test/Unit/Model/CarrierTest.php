<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Fedex\Model\Carrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Pricing\PriceCurrencyInterface;
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
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * CarrierTest contains units test for Fedex carrier methods
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $helper;

    /**
     * @var Carrier|MockObject
     */
    private $model;

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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->helper = new ObjectManager($this);
        $this->scope = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scope->expects(static::any())
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

        $currencyFactory = $this->getMockBuilder(CurrencyFactory::class)
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

        $this->model = $this->getMockBuilder(Carrier::class)
            ->setMethods(['_getCachedQuotes', '_debug'])
            ->setConstructorArgs(
                [
                    'scopeConfig' => $this->scope,
                    'rateErrorFactory' => $this->errorFactory,
                    'logger' => $this->getMock(LoggerInterface::class),
                    'xmlSecurity' => new Security(),
                    'xmlElFactory' => $elementFactory,
                    'rateFactory' => $rateFactory,
                    'rateMethodFactory' => $rateMethodFactory,
                    'trackFactory' => $resultFactory,
                    'trackErrorFactory' => $this->trackErrorFactory,
                    'trackStatusFactory' => $this->statusFactory,
                    'regionFactory' => $regionFactory,
                    'countryFactory' => $countryFactory,
                    'currencyFactory' => $currencyFactory,
                    'directoryData' => $data,
                    'stockRegistry' => $stockRegistry,
                    'storeManager' => $storeManager,
                    'configReader' => $reader,
                    'productCollectionFactory' => $collectionFactory,
                ]
            )
            ->getMock();
    }

    public function testSetRequestWithoutCity()
    {
        $requestMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDestCity'])
            ->getMock();
        $requestMock->expects($this->once())
            ->method('getDestCity')
            ->willReturn(null);
        $this->model->setRequest($requestMock);
    }

    public function testSetRequestWithCity()
    {
        $requestMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDestCity'])
            ->getMock();
        $requestMock->expects($this->exactly(2))
            ->method('getDestCity')
            ->willReturn('Small Town');
        $this->model->setRequest($requestMock);
    }

    /**
     * Callback function, emulates getValue function
     * @param $path
     * @return null|string
     */
    public function scopeConfigGetValue($path)
    {
        switch ($path) {
            case 'carriers/fedex/showmethod':
                return 1;
                break;
            case 'carriers/fedex/allowed_methods':
                return 'ServiceType';
                break;
        }
        return null;
    }

    /**
     * @param float $amount
     * @param string $rateType
     * @param float $expected
     * @dataProvider collectRatesDataProvider
     */
    public function testCollectRatesRateAmountOriginBased($amount, $rateType, $expected)
    {
        $this->scope->expects(static::any())
            ->method('isSetFlag')
            ->willReturn(true);

        // @codingStandardsIgnoreStart
        $netAmount = new \stdClass();
        $netAmount->Amount = $amount;

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

        $this->model->expects(static::any())
            ->method('_getCachedQuotes')
            ->willReturn(serialize($response));
        $request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($this->model->collectRates($request)->getAllRates() as $allRates) {
            $this->assertEquals($expected, $allRates->getData('cost'));
        }
    }

    /**
     * Get list of rates variations
     * @return array
     */
    public function collectRatesDataProvider()
    {
        return [
            [10.0, 'RATED_ACCOUNT_PACKAGE', 10],
            [11.50, 'PAYOR_ACCOUNT_PACKAGE', 11.5],
            [100.01, 'RATED_ACCOUNT_SHIPMENT', 100.01],
            [32.2, 'PAYOR_ACCOUNT_SHIPMENT', 32.2],
            [15.0, 'RATED_LIST_PACKAGE', 15],
            [123.25, 'PAYOR_LIST_PACKAGE', 123.25],
            [12.12, 'RATED_LIST_SHIPMENT', 12.12],
            [38.9, 'PAYOR_LIST_SHIPMENT', 38.9],
        ];
    }

    public function testCollectRatesErrorMessage()
    {
        $this->scope->expects(static::once())
            ->method('isSetFlag')
            ->willReturn(false);

        $this->error->expects(static::once())
            ->method('setCarrier')
            ->with('fedex');
        $this->error->expects(static::once())
            ->method('setCarrierTitle');
        $this->error->expects(static::once())
            ->method('setErrorMessage');

        $request = new RateRequest();
        $request->setPackageWeight(1);

        static::assertSame($this->error, $this->model->collectRates($request));
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
        $property->setValue($this->model, $maskFields);

        $refMethod = $refClass->getMethod('filterDebugData');
        $refMethod->setAccessible(true);
        $result = $refMethod->invoke($this->model, $data);
        static::assertEquals($expected, $result);
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
                            'Password' => 'testPassword'
                        ]
                    ],
                    'ClientDetail' => [
                        'AccountNumber' => 4121213,
                        'MeterNumber' => 'testMeterNumber'
                    ]
                ],
                ['Key', 'Password', 'MeterNumber'],
                [
                    'WebAuthenticationDetail' => [
                        'UserCredential' => [
                            'Key' => '****',
                            'Password' => '****'
                        ]
                    ],
                    'ClientDetail' => [
                        'AccountNumber' => 4121213,
                        'MeterNumber' => '****'
                    ]
                ],
            ],
        ];
    }

    /**
     * @covers \Magento\Fedex\Model\Carrier::getTracking
     */
    public function testGetTrackingErrorResponse()
    {
        $tracking = '123456789012';
        $errorMessage = 'Tracking information is unavailable.';

        // @codingStandardsIgnoreStart
        $response = new \stdClass();
        $response->HighestSeverity = 'ERROR';
        $response->Notifications = new \stdClass();
        $response->Notifications->Message = $errorMessage;
        // @codingStandardsIgnoreEnd

        $this->model->expects(static::once())
            ->method('_getCachedQuotes')
            ->willReturn(serialize($response));

        $error = $this->helper->getObject(Error::class);
        $this->trackErrorFactory->expects(static::once())
            ->method('create')
            ->willReturn($error);

        $this->model->getTracking($tracking);
        $tracks = $this->model->getResult()->getAllTrackings();

        static::assertEquals(1, count($tracks));

        /** @var Error $current */
        $current = $tracks[0];
        static::assertInstanceOf(Error::class, $current);
        static::assertEquals(__($errorMessage), $current->getErrorMessage());
    }

    /**
     * @covers \Magento\Fedex\Model\Carrier::getTracking
     * @param string $shipTimestamp
     * @param string $expectedDate
     * @dataProvider shipDateDataProvider
     */
    public function testGetTracking($shipTimestamp, $expectedDate)
    {
        $tracking = '123456789012';

        // @codingStandardsIgnoreStart
        $response = new \stdClass();
        $response->HighestSeverity = 'SUCCESS';
        $response->CompletedTrackDetails = new \stdClass();

        $trackDetails = new \stdClass();
        $trackDetails->ShipTimestamp = $shipTimestamp;
        $trackDetails->DeliverySignatureName = 'signature';

        $trackDetails->StatusDetail = new \stdClass();
        $trackDetails->StatusDetail->Description = 'SUCCESS';

        $trackDetails->Service = new \stdClass();
        $trackDetails->Service->Description = 'ground';
        $trackDetails->EstimatedDeliveryTimestamp = '2016-08-10T10:20:26+00:00';

        $trackDetails->EstimatedDeliveryAddress = new \stdClass();
        $trackDetails->EstimatedDeliveryAddress->City = 'Culver City';
        $trackDetails->EstimatedDeliveryAddress->StateOrProvinceCode = 'CA';
        $trackDetails->EstimatedDeliveryAddress->CountryCode = 'US';

        $trackDetails->PackageWeight = new \stdClass();
        $trackDetails->PackageWeight->Value = 23;
        $trackDetails->PackageWeight->Units = 'LB';

        $response->CompletedTrackDetails->TrackDetails = [$trackDetails];
        // @codingStandardsIgnoreEnd

        $this->model->expects(static::once())
            ->method('_getCachedQuotes')
            ->willReturn(serialize($response));

        $status = $this->helper->getObject(Status::class);
        $this->statusFactory->expects(static::once())
            ->method('create')
            ->willReturn($status);

        $this->model->getTracking($tracking);
        $tracks = $this->model->getResult()->getAllTrackings();
        static::assertEquals(1, count($tracks));

        $current = $tracks[0];
        $fields = [
            'signedby',
            'status',
            'service',
            'deliverydate',
            'deliverytime',
            'deliverylocation',
            'weight',
        ];
        array_walk($fields, function ($field) use ($current) {
            static::assertNotEmpty($current[$field]);
        });

        static::assertEquals('2016-08-10', $current['deliverydate']);
        static::assertEquals('10:20:26', $current['deliverytime']);
        static::assertEquals($expectedDate, $current['shippeddate']);
    }

    /**
     * Get list of variations for testing ship date
     * @return array
     */
    public function shipDateDataProvider()
    {
        return [
            ['shipTimestamp' => '2016-08-05T14:06:35+00:00', 'expectedDate' => '2016-08-05'],
            ['shipTimestamp' => '2016-08-05T14:06:35', 'expectedDate' => null],
            ['shipTimestamp' => '2016-08-05 14:06:35', 'expectedDate' => null],
            ['shipTimestamp' => '2016-08-05 14:06:35+00:00', 'expectedDate' => null],
            ['shipTimestamp' => '2016-08-05', 'expectedDate' => null],
            ['shipTimestamp' => '2016/08/05', 'expectedDate' => null],
        ];
    }

    /**
     * @covers \Magento\Fedex\Model\Carrier::getTracking
     */
    public function testGetTrackingWithEvents()
    {
        $tracking = '123456789012';

        // @codingStandardsIgnoreStart
        $response = new \stdClass();
        $response->HighestSeverity = 'SUCCESS';
        $response->CompletedTrackDetails = new \stdClass();

        $event = new \stdClass();
        $event->EventDescription = 'Test';
        $event->Timestamp = '2016-08-05T19:14:53+00:00';
        $event->Address = new \stdClass();

        $event->Address->City = 'Culver City';
        $event->Address->StateOrProvinceCode = 'CA';
        $event->Address->CountryCode = 'US';

        $trackDetails = new \stdClass();
        $trackDetails->Events = $event;

        $response->CompletedTrackDetails->TrackDetails = $trackDetails;
        // @codingStandardsIgnoreEnd

        $this->model->expects(static::once())
            ->method('_getCachedQuotes')
            ->willReturn(serialize($response));

        $status = $this->helper->getObject(Status::class);
        $this->statusFactory->expects(static::once())
            ->method('create')
            ->willReturn($status);

        $this->model->getTracking($tracking);
        $tracks = $this->model->getResult()->getAllTrackings();
        static::assertEquals(1, count($tracks));

        $current = $tracks[0];
        static::assertNotEmpty($current['progressdetail']);
        static::assertEquals(1, count($current['progressdetail']));

        $event = $current['progressdetail'][0];
        $fields = ['activity', 'deliverydate', 'deliverytime', 'deliverylocation'];
        array_walk($fields, function ($field) use ($event) {
            static::assertNotEmpty($event[$field]);
        });
        static::assertEquals('2016-08-05', $event['deliverydate']);
        static::assertEquals('19:14:53', $event['deliverytime']);
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
        $this->errorFactory->expects(static::any())
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
        $rateFactory->expects(static::any())
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
        $country->expects(static::any())
            ->method('load')
            ->willReturnSelf();

        $countryFactory = $this->getMockBuilder(CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $countryFactory->expects(static::any())
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
        $resultFactory->expects(static::any())
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
        $storeManager = $this->getMock(StoreManagerInterface::class);
        $storeManager->expects(static::any())
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
        $priceCurrency = $this->getMock(PriceCurrencyInterface::class);
        $rateMethod = $this->getMockBuilder(Method::class)
            ->setConstructorArgs(['priceCurrency' => $priceCurrency])
            ->setMethods(null)
            ->getMock();
        $rateMethodFactory = $this->getMockBuilder(MethodFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $rateMethodFactory->expects(static::any())
            ->method('create')
            ->willReturn($rateMethod);

        return $rateMethodFactory;
    }
}
