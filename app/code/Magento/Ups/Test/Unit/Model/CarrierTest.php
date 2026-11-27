<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ups\Test\Unit\Model;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ups\Helper\Config;
use Magento\Ups\Model\Carrier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for \Magento\Ups\Model\Carrier class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CarrierTest extends TestCase
{
    use MockCreationTrait;

    public const FREE_METHOD_NAME = 'free_method';

    public const PAID_METHOD_NAME = 'paid_method';

    /**
     * @var Error|MockObject
     */
    private $error;

    /**
     * @var ObjectManager
     */
    private $helper;

    /**
     * @var Carrier|MockObject
     */
    private $model;

    /**
     * @var ErrorFactory|MockObject
     */
    private $errorFactory;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scope;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var Country|MockObject
     */
    private $country;

    /**
     * @var Result
     */
    private $rate;

    /**
     * @var ClientInterface|MockObject
     */
    private $httpClient;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var Config|MockObject
     */
    private $configHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->helper = new ObjectManager($this);

        $this->scope = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue', 'isSetFlag'])
            ->getMock();

        $this->error = $this->createPartialMockWithReflection(
            Error::class,
            ['setCarrier', 'setCarrierTitle', 'setErrorMessage']
        );

        $this->errorFactory = $this->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->errorFactory->method('create')
            ->willReturn($this->error);

        $rateFactory = $this->getRateFactory();

        $this->country = $this->getMockBuilder(Country::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getData'])
            ->getMock();

        $this->country->method('load')
            ->willReturnCallback([$this, 'getCountryById']);

        $this->countryFactory = $this->getMockBuilder(CountryFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->countryFactory->method('create')
            ->willReturn($this->country);

        $httpClientFactory = $this->getHttpClientFactory();

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->configHelper = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMock();

        $this->model = $this->helper->getObject(
            Carrier::class,
            [
                'scopeConfig' => $this->scope,
                'rateErrorFactory' => $this->errorFactory,
                'countryFactory' => $this->countryFactory,
                'rateFactory' => $rateFactory,
                'logger' => $this->logger,
                'httpClientFactory' => $httpClientFactory,
                'configHelper' => $this->configHelper
            ]
        );
    }

    /**
     * Callback function, emulates getValue function.
     *
     * @param string $path
     *
     * @return null|string|int
     */
    public function scopeConfigGetValue(string $path)
    {
        $pathMap = [
            'carriers/ups/free_method' => 'free_method',
            'carriers/ups/free_shipping_subtotal' => 5,
            'carriers/ups/showmethod' => 1,
            'carriers/ups/title' => 'ups Title',
            'carriers/ups/specificerrmsg' => 'ups error message',
            'carriers/ups/min_package_weight' => 2,
            'carriers/ups/debug' => 1,
            'carriers/ups/username' => 'user',
            'carriers/ups/password' => 'pass'
        ];

        return $pathMap[$path] ?? null;
    }

    /**
     * @param int $cost
     * @param string $shippingMethod
     * @param bool $freeShippingEnabled
     * @param int $requestSubtotal
     * @param int $expectedPrice
     * @return void
     */
    #[DataProvider('getMethodPriceProvider')]
    public function testGetMethodPrice(
        int $cost,
        string $shippingMethod,
        bool $freeShippingEnabled,
        int $requestSubtotal,
        int $expectedPrice
    ): void {
        $this->scope->method('getValue')
            ->willReturnCallback([$this, 'scopeConfigGetValue']);
        $path = 'carriers/' . $this->model->getCarrierCode() . '/';
        $this->scope->method('isSetFlag')
            ->with($path . 'free_shipping_enable')
            ->willReturn($freeShippingEnabled);

        $request = new RateRequest();
        $request->setValueWithDiscount($requestSubtotal);
        $this->model->setRawRequest($request);
        $price = $this->model->getMethodPrice($cost, $shippingMethod);
        $this->assertEquals($expectedPrice, $price);
    }

    /**
     * Data provider for testGenerate method.
     *
     * @return array
     */
    public static function getMethodPriceProvider(): array
    {
        return [
            [3, self::FREE_METHOD_NAME, true, 6, 0],
            [3, self::FREE_METHOD_NAME, true, 4, 3],
            [3, self::FREE_METHOD_NAME, false, 6, 3],
            [3, self::FREE_METHOD_NAME, false, 4, 3],
            [3, self::PAID_METHOD_NAME, true, 6, 3],
            [3, self::PAID_METHOD_NAME, true, 4, 3],
            [3, self::PAID_METHOD_NAME, false, 6, 3],
            [3, self::PAID_METHOD_NAME, false, 4, 3],
            [7, self::FREE_METHOD_NAME, true, 6, 0],
            [7, self::FREE_METHOD_NAME, true, 4, 7],
            [7, self::FREE_METHOD_NAME, false, 6, 7],
            [7, self::FREE_METHOD_NAME, false, 4, 7],
            [7, self::PAID_METHOD_NAME, true, 6, 7],
            [7, self::PAID_METHOD_NAME, true, 4, 7],
            [7, self::PAID_METHOD_NAME, false, 6, 7],
            [7, self::PAID_METHOD_NAME, false, 4, 7],
            [3, self::FREE_METHOD_NAME, true, 0, 3],
            [3, self::FREE_METHOD_NAME, true, 0, 3],
            [3, self::FREE_METHOD_NAME, false, 0, 3],
            [3, self::FREE_METHOD_NAME, false, 0, 3],
            [3, self::PAID_METHOD_NAME, true, 0, 3],
            [3, self::PAID_METHOD_NAME, true, 0, 3],
            [3, self::PAID_METHOD_NAME, false, 0, 3],
            [3, self::PAID_METHOD_NAME, false, 0, 3]
        ];
    }

    /**
     * @return void
     */
    public function testCollectRatesErrorMessage(): void
    {
        $this->scope->method('getValue')
            ->willReturnCallback([$this, 'scopeConfigGetValue']);
        $this->scope->method('isSetFlag')
            ->willReturn(false);

        $this->error->method('setCarrier')
            ->with('ups');
        $this->error->method('setCarrierTitle');
        $this->error->method('setErrorMessage');

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertSame($this->error, $this->model->collectRates($request));
    }

    /**
     * @param string $gatewayUrl
     * @param string $expectedUrlPrefix
     * @return void
     */
    #[DataProvider('cgiGatewayUrlDataProvider')]
    public function testGetCgiQuotesUsesHttpClient(string $gatewayUrl, string $expectedUrlPrefix): void
    {
        $config = [
            'carriers/ups/type' => 'UPS',
            'carriers/ups/gateway_url' => $gatewayUrl,
            'carriers/ups/allowed_methods' => 'GND',
            'carriers/ups/title' => 'UPS',
        ];
        $this->scope->method('getValue')
            ->willReturnCallback(static fn ($path) => $config[$path] ?? null);
        $this->configHelper->method('getCode')->willReturnMap([['method', 'GND', 'Ground']]);

        $httpClientFactory = $this->createMock(ClientFactory::class);
        $httpClientFactory->expects($this->once())->method('create')->willReturn($this->httpClient);
        $this->httpClient->expects($this->once())->method('setTimeout')->with(30);
        $this->httpClient->expects($this->once())->method('get')->with(
            $expectedUrlPrefix . 'accept_UPS_license_agreement=yes&10_action=4&13_product=GND'
            . '&14_origCountry=US&15_origPostal=90230&origCity=Los+Angeles+%26+County'
            . '&19_destPostal=11236&22_destCountry=US&23_weight=2&47_rate_chart=Daily+Pickup'
            . '&48_container=CP&49_residential=RES&weight_std=lbs'
        );
        $responseBody = '3%GND%US%90230%US%11236%2%0%12.50';
        $this->httpClient->expects($this->once())->method('getBody')->willReturn($responseBody);

        $localeFormat = $this->createMock(FormatInterface::class);
        $localeFormat->method('getNumber')->willReturnMap([['12.50', 12.5]]);
        $priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $priceCurrency->method('round')->willReturnMap([[12.5, 12.5]]);
        $method = $this->helper->getObject(Method::class, ['priceCurrency' => $priceCurrency]);
        $rateMethodFactory = $this->createMock(MethodFactory::class);
        $rateMethodFactory->expects($this->once())->method('create')->willReturn($method);

        $model = $this->getMockBuilder(Carrier::class)
            ->setConstructorArgs(array_values($this->helper->getConstructArguments(Carrier::class, [
                'scopeConfig' => $this->scope,
                'configHelper' => $this->configHelper,
                'httpClientFactory' => $httpClientFactory,
                'localeFormat' => $localeFormat,
                'rateFactory' => $this->getRateFactory(),
                'rateMethodFactory' => $rateMethodFactory,
            ])))
            ->onlyMethods(['_getCachedQuotes', '_setCachedQuotes'])
            ->getMock();
        $model->expects($this->once())->method('_getCachedQuotes')->willReturn(null);
        $model->expects($this->once())->method('_setCachedQuotes')->with($this->anything(), $responseBody);
        $model->setRawRequest(new DataObject([
            'action' => '4',
            'product' => 'GND',
            'orig_country' => 'US',
            'orig_postal' => '90230',
            'orig_city' => 'Los Angeles & County',
            'dest_postal' => '11236-1234',
            'dest_country' => 'US',
            'weight' => 2,
            'pickup' => ['label' => 'Daily Pickup'],
            'container' => 'CP',
            'dest_type' => 'RES',
            'unit_measure' => 'LBS',
        ]));

        $getQuotes = new \ReflectionMethod(Carrier::class, '_getQuotes');
        $result = $getQuotes->invoke($model);

        $this->assertSame($this->rate, $result);
        $this->assertSame([$method], $result->getAllRates());
        $this->assertSame('ups', $method->getCarrier());
        $this->assertSame('GND', $method->getMethod());
        $this->assertSame('Ground', $method->getMethodTitle());
        $this->assertSame(12.5, $method->getCost());
        $this->assertSame(12.5, $method->getPrice());
    }

    /**
     * @return void
     */
    public function testGetCgiQuotesHandlesHttpClientException(): void
    {
        $config = [
            'carriers/ups/type' => 'UPS',
            'carriers/ups/gateway_url' => 'https://example.com/rates',
        ];
        $this->scope->method('getValue')
            ->willReturnCallback(static fn ($path) => $config[$path] ?? null);

        $httpClientFactory = $this->createMock(ClientFactory::class);
        $httpClientFactory->expects($this->once())->method('create')->willReturn($this->httpClient);
        $this->httpClient->expects($this->once())->method('setTimeout')->with(30);
        $this->httpClient->expects($this->once())->method('get')
            ->willThrowException(new \RuntimeException('Transport error'));
        $this->httpClient->expects($this->never())->method('getBody');

        $model = $this->getMockBuilder(Carrier::class)
            ->setConstructorArgs(array_values($this->helper->getConstructArguments(Carrier::class, [
                'scopeConfig' => $this->scope,
                'httpClientFactory' => $httpClientFactory,
            ])))
            ->onlyMethods(['_getCachedQuotes', '_setCachedQuotes', '_parseCgiResponse'])
            ->getMock();
        $model->expects($this->once())->method('_getCachedQuotes')->willReturn(null);
        $model->expects($this->never())->method('_setCachedQuotes');
        $model->expects($this->once())->method('_parseCgiResponse')->with('')->willReturn($this->rate);
        $model->setRawRequest(new DataObject(['pickup' => ['label' => 'Daily Pickup']]));

        $getQuotes = new \ReflectionMethod(Carrier::class, '_getQuotes');

        $this->assertSame($this->rate, $getQuotes->invoke($model));
    }

    /**
     * @return array
     */
    public static function cgiGatewayUrlDataProvider(): array
    {
        return [
            'default gateway' => ['', 'https://www.ups.com/using/services/rave/qcostcgi.cgi?'],
            'configured gateway' => ['https://example.com/rates', 'https://example.com/rates?'],
            'existing query' => ['https://example.com/rates?mode=cgi', 'https://example.com/rates?mode=cgi&'],
        ];
    }

    /**
     * @param array $requestData
     * @param array $rawRequestData
     *
     * @return void
     */
    #[DataProvider('countryDataProvider')]
    public function testSetRequest(array $requestData, array $rawRequestData): void
    {
        /** @var RateRequest $request */
        $request = $this->helper->getObject(RateRequest::class);
        $request->setData($requestData);
        $this->model->setRequest($request);
        $property = new \ReflectionProperty($this->model, '_rawRequest');
        $rawRequest = $property->getValue($this->model);
        $this->assertEquals($rawRequestData, array_intersect_key($rawRequest->getData(), $rawRequestData));
    }

    /**
     * Get list of request variations for setRequest.
     *
     * @return array
     */
    public static function countryDataProvider(): array
    {
        return [
            [
                [
                    'orig_region_code' => 'CA',
                    'orig_postcode' => '90230',
                    'orig_country' => 'US',
                    'dest_region_code' => 'NY',
                    'dest_postcode' => '11236',
                    'dest_country_id' => 'US',
                ],
                [
                    'orig_region_code' => 'CA',
                    'orig_postal' => '90230',
                    'orig_country' => 'US',
                    'dest_region_code' => 'NY',
                    'dest_postal' => '11236',
                    'dest_country' => 'US',
                ]
            ],
            [
                [
                    'orig_region_code' => 'CA',
                    'orig_postcode' => '90230',
                    'orig_country' => 'US',
                    'dest_region_code' => 'PR',
                    'dest_postcode' => '00968',
                    'dest_country_id' => 'US',
                ],
                [
                    'orig_region_code' => 'CA',
                    'orig_postal' => '90230',
                    'orig_country' => 'US',
                    'dest_region_code' => 'PR',
                    'dest_postal' => '00968',
                    'dest_country' => 'PR',
                ]
            ],
            [
                [
                    'orig_region_code' => 'PR',
                    'orig_postcode' => '00968',
                    'orig_country' => 'US',
                    'dest_region_code' => 'CA',
                    'dest_postcode' => '90230',
                    'dest_country_id' => 'US',
                ],
                [
                    'orig_region_code' => 'PR',
                    'orig_postal' => '00968',
                    'orig_country' => 'PR',
                    'dest_region_code' => 'CA',
                    'dest_postal' => '90230',
                    'dest_country' => 'US',
                ]
            ],
        ];
    }

    /**
     * @param array $requestData
     * @param array $expectedRequestData
     */
    #[DataProvider('requestToShipmentDataProvider')]
    public function testRequestToShipment(array $requestData, array $expectedRequestData): void
    {
        /** @var \Magento\Shipping\Model\Shipment\Request $request */
        $request = $this->helper->getObject(\Magento\Shipping\Model\Shipment\Request::class);
        $shipmentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Shipment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrder'])
            ->getMock();
        $orderMock =  $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getIncrementId'])
            ->getMock();

        $shipmentMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->any())
            ->method('getIncrementId')
            ->willReturn('100000001');

        $requestData['order_shipment'] = $shipmentMock;
        $request->setData($requestData);
        $request->setPackages([['items' => [], 'params' => ['container' => '']]]);
        $this->model->requestToShipment($request);
        $this->assertEquals($expectedRequestData, array_intersect_key($request->getData(), $expectedRequestData));
    }

    /**
     * Get list of request variations for requestToShipment.
     *
     * @return array
     */
    public static function requestToShipmentDataProvider(): array
    {
        return [
            [
                [
                    'recipient_address_state_or_province_code' => 'CA',
                    'recipient_address_postal_code' => '90230',
                    'recipient_address_country_code' => 'US',
                    'shipper_address_state_or_province_code' => 'NY',
                    'shipper_address_postal_code' => '11236',
                    'shipper_address_country_code' => 'US',
                ],
                [
                    'recipient_address_state_or_province_code' => 'CA',
                    'recipient_address_postal_code' => '90230',
                    'recipient_address_country_code' => 'US',
                    'shipper_address_state_or_province_code' => 'NY',
                    'shipper_address_postal_code' => '11236',
                    'shipper_address_country_code' => 'US',
                ]
            ],
            [
                [
                    'recipient_address_state_or_province_code' => 'CA',
                    'recipient_address_postal_code' => '90230',
                    'recipient_address_country_code' => 'US',
                    'shipper_address_state_or_province_code' => 'PR',
                    'shipper_address_postal_code' => '00968',
                    'shipper_address_country_code' => 'US',
                ],
                [
                    'recipient_address_state_or_province_code' => 'CA',
                    'recipient_address_postal_code' => '90230',
                    'recipient_address_country_code' => 'US',
                    'shipper_address_state_or_province_code' => 'PR',
                    'shipper_address_postal_code' => '00968',
                    'shipper_address_country_code' => 'US',
                ]
            ],
            [
                [
                    'recipient_address_state_or_province_code' => 'PR',
                    'recipient_address_postal_code' => '00968',
                    'recipient_address_country_code' => 'US',
                    'shipper_address_state_or_province_code' => 'CA',
                    'shipper_address_postal_code' => '90230',
                    'shipper_address_country_code' => 'US',
                ],
                [
                    'recipient_address_state_or_province_code' => 'PR',
                    'recipient_address_postal_code' => '00968',
                    'recipient_address_country_code' => 'US',
                    'shipper_address_state_or_province_code' => 'CA',
                    'shipper_address_postal_code' => '90230',
                    'shipper_address_country_code' => 'US',
                ]
            ],
        ];
    }

    /**
     * @param string|null $id
     * @return Country
     */
    public function getCountryById(?string $id): Country
    {
        $countries = [
            'US' => 'US'
        ];
        $countryMock = $this->getMockBuilder(Country::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', '__call'])
            ->getMock();
        $countryMock->setData('iso2_code', $countries[$id] ?? null);
        $countryMock->method('getData')
            ->with('iso2_code')
            ->willReturn($countries[$id] ?? null);
        return $countryMock;
    }

    /**
     * @param string $carrierType
     * @param string $methodType
     * @param string $methodCode
     * @param string $methodTitle
     * @param string $allowedMethods
     * @param array $expectedMethods
     *
     * @return void
     */
    #[DataProvider('allowedMethodsDataProvider')]
    public function testGetAllowedMethods(
        string $carrierType,
        string $methodType,
        string $methodCode,
        string $methodTitle,
        string $allowedMethods,
        array $expectedMethods
    ): void {
        $this->scope->method('getValue')
            ->willReturnMap(
                [
                    [
                        'carriers/ups/allowed_methods',
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $allowedMethods
                    ],
                    [
                        'carriers/ups/type',
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $carrierType
                    ],
                    [
                        'carriers/ups/origin_shipment',
                        ScopeInterface::SCOPE_STORE,
                        null,
                        'Shipments Originating in United States'
                    ]
                ]
            );
        $this->configHelper->method('getCode')
            ->with($methodType)
            ->willReturn([$methodCode => new Phrase($methodTitle)]);
        $actualMethods = $this->model->getAllowedMethods();
        $this->assertEquals($expectedMethods, $actualMethods);
    }

    /**
     * @return array
     */
    public static function allowedMethodsDataProvider(): array
    {
        return [
            [
                'UPS',
                'method',
                '1DM',
                'Next Day Air Early AM',
                '',
                []
            ],
            [
                'UPS',
                'method',
                '1DM',
                'Next Day Air Early AM',
                '1DM,1DML,1DA',
                ['1DM' => 'Next Day Air Early AM']
            ],
            [
                'UPS_XML',
                'originShipment',
                '01',
                'UPS Next Day Air',
                '01,02,03',
                ['01' => 'UPS Next Day Air']
            ],
            [
                'UPS_REST',
                'originShipment',
                '03',
                'UPS Ground',
                '01,02,03',
                ['03' => 'UPS Ground']
            ]
        ];
    }

    /**
     * Creates mocks for http client factory and client.
     *
     * @return ClientFactory|MockObject
     */
    private function getHttpClientFactory(): MockObject
    {
        $httpClientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->httpClient = $this->createMock(ClientInterface::class);
        $httpClientFactory->method('create')
            ->willReturn($this->httpClient);

        return $httpClientFactory;
    }

    /**
     * @return MockObject
     */
    private function getRateFactory(): MockObject
    {
        $this->rate = $this->createPartialMock(Result::class, ['getError']);
        $rateFactory = $this->createPartialMock(ResultFactory::class, ['create']);

        $rateFactory->method('create')
            ->willReturn($this->rate);

        return $rateFactory;
    }
}
