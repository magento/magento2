<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Test\Unit\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Ups\Model\Carrier;
use Magento\Directory\Model\Country;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\HTTP\ClientInterface;

/**
 * Tests \Magento\Ups\Model\Carrier
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends \PHPUnit_Framework_TestCase
{
    const FREE_METHOD_NAME = 'free_method';

    const PAID_METHOD_NAME = 'paid_method';

    /**
     * Model under test
     *
     * @var \Magento\Quote\Model\Quote\Address\RateResult\Error|MockObject
     */
    protected $error;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helper;

    /**
     * Model under test
     *
     * @var Carrier|MockObject
     */
    protected $model;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory|MockObject
     */
    protected $errorFactory;

    /**
     * @var Carrier|MockObject
     */
    protected $carrier;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject
     */
    protected $scope;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var Country|MockObject
     */
    protected $country;

    /**
     * @var \Magento\Framework\Model\AbstractModel
     */
    protected $abstractModel;

    /**
     * @var \Magento\Shipping\Model\Rate\Result
     */
    protected $rate;

    /**
     * @var ClientInterface|MockObject
     */
    private $httpClientMock;

    /**
     * @var string
     */
    private $url = 'http://url';

    protected function setUp()
    {
        $this->helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scope = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->scope->expects($this->any())
            ->method('getValue')
            ->willReturnCallback([$this, 'scopeConfiggetValue']);

        $this->error = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\Error::class)
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])
            ->getMock();

        $this->errorFactory = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->errorFactory->expects($this->any())->method('create')->willReturn($this->error);

        $this->rate = $this->getMock(\Magento\Shipping\Model\Rate\Result::class, ['getError'], [], '', false);
        $rateFactory = $this->getMock(\Magento\Shipping\Model\Rate\ResultFactory::class, ['create'], [], '', false);

        $rateFactory->expects($this->any())->method('create')->willReturn($this->rate);

        $this->country = $this->getMockBuilder(Country::class)
            ->disableOriginalConstructor()
            ->setMethods(['load'])
            ->getMock();

        $this->abstractModel = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->country->expects($this->any())->method('load')->willReturn($this->abstractModel);

        $this->countryFactory = $this->getMockBuilder(\Magento\Directory\Model\CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->countryFactory->expects($this->any())->method('create')->willReturn($this->country);

        $httpClientFactory = $this->getHttpClientFactory();

        $this->model = $this->helper->getObject(
            Carrier::class,
            [
                'scopeConfig' => $this->scope,
                'rateErrorFactory' => $this->errorFactory,
                'countryFactory' => $this->countryFactory,
                'rateFactory' => $rateFactory,
                'httpClientFactory' => $httpClientFactory
            ]
        );
    }

    /**
     * Callback function, emulates getValue function
     * @param $path
     * @return null|string
     */
    public function scopeConfiggetValue($path)
    {
        $pathMap = [
            'carriers/ups/free_method' => 'free_method',
            'carriers/ups/free_shipping_subtotal' => 5,
            'carriers/ups/showmethod' => 1,
            'carriers/ups/title' => 'ups Title',
            'carriers/ups/specificerrmsg' => 'ups error message',
            'carriers/ups/min_package_weight' => 2,
            'carriers/ups/type' => 'UPS_XML',
            'carriers/ups/gateway_xml_url' => $this->url,
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
     * @covers       \Magento\Shipping\Model\Carrier\AbstractCarrierOnline::getMethodPrice
     */
    public function testGetMethodPrice(
        $cost,
        $shippingMethod,
        $freeShippingEnabled,
        $requestSubtotal,
        $expectedPrice
    ) {
        $path = 'carriers/' . $this->model->getCarrierCode() . '/';
        $this->scope->expects($this->any())->method('isSetFlag')->with($path . 'free_shipping_enable')->will(
            $this->returnValue($freeShippingEnabled)
        );

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
        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(false);

        $this->error->expects($this->once())->method('setCarrier')->with('ups');
        $this->error->expects($this->once())->method('setCarrierTitle');
        $this->error->expects($this->once())->method('setErrorMessage');

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertSame($this->error, $this->model->collectRates($request));
    }

    /**
     * @return void
     */
    public function testCollectRatesFail()
    {
        $this->scope->expects($this->any())->method('isSetFlag')->willReturn(true);

        $this->httpClientMock->expects($this->once())
            ->method('setOptions')
            ->with(
                [
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_HEADER => 0,
                ]
            );
        $this->httpClientMock->expects($this->once())->method('post');
        $this->httpClientMock->expects($this->once())->method('getBody')->willReturn(false);

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
        static::assertEquals($expectedXml->asXML(), $resultXml->asXML());
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
     * @covers Carrier::setRequest
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

        $this->country->expects(static::at(1))
            ->method('load')
            ->with($countryCode)
            ->willReturnSelf();

        $this->country->expects(static::any())
            ->method('getData')
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
     * Creates mocks for http client factory and client.
     *
     * @return ClientFactory|MockObject
     */
    private function getHttpClientFactory()
    {
        $httpClientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->httpClientMock = $this->getMockForAbstractClass(
            ClientInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setOptions', 'post', 'getBody']
        );

        $httpClientFactory->method('create')->willReturn($this->httpClientMock);

        return $httpClientFactory;
    }
}
