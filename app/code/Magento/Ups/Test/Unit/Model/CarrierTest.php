<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Test\Unit\Model;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Shipping\Model\Simplexml\ElementFactory;
use Magento\Ups\Model\Carrier;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        $this->helper = new ObjectManager($this);

        $this->scope = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scope->method('getValue')
            ->willReturnCallback([$this, 'scopeConfigGetValue']);

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

        $this->model = $this->helper->getObject(
            Carrier::class,
            [
                'scopeConfig' => $this->scope,
                'rateErrorFactory' => $this->errorFactory,
                'countryFactory' => $this->countryFactory,
                'rateFactory' => $rateFactory,
                'xmlElFactory' => $xmlFactory,
                'logger' => $this->logger,
                'httpClientFactory' => $httpClientFactory
            ]
        );
    }

    /**
     * Callback function, emulates getValue function.
     *
     * @param $path
     * @return null|string
     */
    public function scopeConfigGetValue($path)
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

        return isset($pathMap[$path]) ? $pathMap[$path] : null;
    }

    /**
     * @dataProvider getMethodPriceProvider
     * @param int $cost
     * @param string $shippingMethod
     * @param bool $freeShippingEnabled
     * @param int $requestSubtotal
     * @param int $expectedPrice
     */
    public function testGetMethodPrice(
        $cost,
        $shippingMethod,
        $freeShippingEnabled,
        $requestSubtotal,
        $expectedPrice
    ) {
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

    public function testCollectRatesErrorMessage()
    {
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

    public function testCollectRatesFail()
    {
        $this->scope->method('isSetFlag')
            ->willReturn(true);

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertSame($this->rate, $this->model->collectRates($request));
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
        self::assertEquals($expectedXml->asXML(), $resultXml->asXML());
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
        $request->setData([
            'orig_country' => 'USA',
            'orig_region_code' => 'CA',
            'orig_post_code' => 90230,
            'orig_city' => 'Culver City',
            'dest_country_id' => $countryCode,
        ]);

        $this->country->expects(self::at(1))
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
     * Checks a case when UPS processes request to create shipment.
     */
    public function testRequestToShipment()
    {
        // the same tracking number is specified in the fixtures XML file.
        $trackingNumber = '1Z207W886698856557';
        $packages = $this->getPackages();
        $request = new DataObject(['packages' => $packages]);
        $shipmentResponse = simplexml_load_file(__DIR__ . '/../Fixtures/ShipmentConfirmResponse.xml');
        $acceptResponse = simplexml_load_file(__DIR__ . '/../Fixtures/ShipmentAcceptResponse.xml');

        $this->httpClient->method('getBody')
            ->willReturnOnConsecutiveCalls($shipmentResponse->asXML(), $acceptResponse->asXML());

        $this->logger->expects(self::atLeastOnce())
            ->method('debug')
            ->with(self::stringContains('<UserId>****</UserId>'));

        $result = $this->model->requestToShipment($request);
        self::assertEmpty($result->getErrors());

        $info = $result->getInfo()[0];
        self::assertEquals($trackingNumber, $info['tracking_number'], 'Tracking Number must match.');
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
     * @return array
     */
    private function getPackages(): array
    {
        $packages = [
            'package' => [
                'params' => [
                    'width' => '3',
                    'length' => '3',
                    'height' => '3',
                    'dimension_units' => 'INCH',
                    'weight_units' => 'POUND',
                    'weight' => '0.454000000001',
                    'customs_value' => '10.00',
                    'container' => 'Small Express Box'
                ],
                'items' => [
                    'item1' => [
                        'name' => 'item_name',
                    ],
                ],
            ]
        ];

        return $packages;
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
