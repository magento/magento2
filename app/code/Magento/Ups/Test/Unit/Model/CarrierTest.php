<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Ups\Test\Unit\Model;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Ups\Helper\Config;
use Magento\Ups\Model\Carrier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for \Magento\Ups\Model\Carrier class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends TestCase
{
    const FREE_METHOD_NAME = 'free_method';

    const PAID_METHOD_NAME = 'paid_method';

    /**
     * Model under test
     *
     * @var Error|MockObject
     */
    private $error;

    /**
     * @var ObjectManager
     */
    private $helper;

    /**
     * Model under test
     *
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
     * @var AbstractModel
     */
    private $abstractModel;

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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->helper = new ObjectManager($this);

        $this->scope = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'isSetFlag'])
            ->getMockForAbstractClass();

        $this->error = $this->getMockBuilder(Error::class)
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();

        $this->errorFactory = $this->getMockBuilder(ErrorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->errorFactory->method('create')
            ->willReturn($this->error);

        $rateFactory = $this->getRateFactory();

        $this->country = $this->getMockBuilder(Country::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getData'])
            ->getMock();

        $this->abstractModel = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->country->method('load')
            ->willReturn($this->abstractModel);

        $this->countryFactory = $this->getMockBuilder(CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->countryFactory->method('create')
            ->willReturn($this->country);

        $xmlFactory = $this->getXmlFactory();
        $httpClientFactory = $this->getHttpClientFactory();

        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->configHelper = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode'])
            ->getMock();

        $this->model = $this->helper->getObject(
            Carrier::class,
            [
                'scopeConfig' => $this->scope,
                'rateErrorFactory' => $this->errorFactory,
                'countryFactory' => $this->countryFactory,
                'rateFactory' => $rateFactory,
                'xmlElFactory' => $xmlFactory,
                'logger' => $this->logger,
                'httpClientFactory' => $httpClientFactory,
                'configHelper' => $this->configHelper,
            ]
        );
    }

    /**
     * Callback function, emulates getValue function.
     *
     * @param string $path
     * @return null|string
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
            'carriers/ups/type' => 'UPS',
            'carriers/ups/debug' => 1,
            'carriers/ups/username' => 'user',
            'carriers/ups/password' => 'pass',
            'carriers/ups/access_license_number' => 'acn',
        ];

        return $pathMap[$path] ?? null;
    }

    /**
     * @dataProvider getMethodPriceProvider
     * @param int $cost
     * @param string $shippingMethod
     * @param bool $freeShippingEnabled
     * @param int $requestSubtotal
     * @param int $expectedPrice
     * @return void
     */
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
        $request->setBaseSubtotalInclTax($requestSubtotal);
        $this->model->setRawRequest($request);
        $price = $this->model->getMethodPrice($cost, $shippingMethod);
        $this->assertEquals($expectedPrice, $price);
    }

    /**
     * Data provider for testGenerate method
     *
     * @return array
     */
    public function getMethodPriceProvider()
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
        $expectedXml = new \SimpleXMLElement($expected);
        $resultXml = new \SimpleXMLElement($result);
        $this->assertEquals($expectedXml->asXML(), $resultXml->asXML());
    }

    /**
     * Get list of variations
     */
    public function logDataProvider()
    {
        return [
            [
                '<?xml version="1.0" encoding="UTF-8"?>
                <RateRequest>
                    <UserId>42121</UserId>
                    <Password>TestPassword</Password>
                    <Package ID="0">
                        <Service>ALL</Service>
                    </Package>
                </RateRequest>',
                ['UserId', 'Password'],
                '<?xml version="1.0" encoding="UTF-8"?>
                <RateRequest>
                    <UserId>****</UserId>
                    <Password>****</Password>
                    <Package ID="0">
                        <Service>ALL</Service>
                    </Package>
                </RateRequest>',
            ],
            [
                '<?xml version="1.0" encoding="UTF-8"?>
                <RateRequest>
                    <Auth>
                        <UserId>1231</UserId>
                    </Auth>
                    <Package ID="0">
                        <Service>ALL</Service>
                    </Package>
                </RateRequest>',
                ['UserId'],
                '<?xml version="1.0" encoding="UTF-8"?>
                <RateRequest>
                    <Auth>
                        <UserId>****</UserId>
                    </Auth>
                    <Package ID="0">
                        <Service>ALL</Service>
                    </Package>
                </RateRequest>',
            ]
        ];
    }

    /**
     * @param string $countryCode
     * @param string $foundCountryCode
     * @dataProvider countryDataProvider
     */
    public function testSetRequest($countryCode, $foundCountryCode)
    {
        /** @var RateRequest $request */
        $request = $this->helper->getObject(RateRequest::class);
        $request->setData(
            [
                'orig_country' => 'USA',
                'orig_region_code' => 'CA',
                'orig_post_code' => 90230,
                'orig_city' => 'Culver City',
                'dest_country_id' => $countryCode,
            ]
        );

        $this->country->expects($this->at(1))
            ->method('load')
            ->with($countryCode)
            ->willReturnSelf();

        $this->country->method('getData')
            ->with('iso2_code')
            ->willReturn($foundCountryCode);

        $this->model->setRequest($request);
    }

    /**
     * Get list of country variations
     * @return array
     */
    public function countryDataProvider()
    {
        return [
            ['countryCode' => 'PR', 'foundCountryCode' => null],
            ['countryCode' => 'US', 'foundCountryCode' => 'US'],
        ];
    }

    /**
     * @dataProvider allowedMethodsDataProvider
     * @param string $carrierType
     * @param string $methodType
     * @param string $methodCode
     * @param string $methodTitle
     * @param string $allowedMethods
     * @param array $expectedMethods
     * @return void
     */
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
                        $allowedMethods,
                    ],
                    [
                        'carriers/ups/type',
                        ScopeInterface::SCOPE_STORE,
                        null,
                        $carrierType,
                    ],
                    [
                        'carriers/ups/origin_shipment',
                        ScopeInterface::SCOPE_STORE,
                        null,
                        'Shipments Originating in United States',
                    ],
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
    public function allowedMethodsDataProvider(): array
    {
        return [
            [
                'UPS',
                'method',
                '1DM',
                'Next Day Air Early AM',
                '',
                [],
            ],
            [
                'UPS',
                'method',
                '1DM',
                'Next Day Air Early AM',
                '1DM,1DML,1DA',
                ['1DM' => 'Next Day Air Early AM'],
            ],
            [
                'UPS_XML',
                'originShipment',
                '01',
                'UPS Next Day Air',
                '01,02,03',
                ['01' => 'UPS Next Day Air'],
            ],
        ];
    }

    /**
     * Creates mock for XML factory.
     *
     * @return ElementFactory|MockObject
     */
    private function getXmlFactory(): MockObject
    {
        $xmlElFactory = $this->getMockBuilder(ElementFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $xmlElFactory->method('create')
            ->willReturnCallback(
                function ($data) {
                    $helper = new ObjectManager($this);

                    return $helper->getObject(
                        Element::class,
                        ['data' => $data['data']]
                    );
                }
            );

        return $xmlElFactory;
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
            ->setMethods(['create'])
            ->getMock();
        $this->httpClient = $this->getMockForAbstractClass(ClientInterface::class);
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
