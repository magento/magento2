<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Test\Unit\Model;

use Magento\Fedex\Model\Carrier;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Class CarrierTest
 * @package Magento\Fedex\Model
 * TODO refactor me
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Fedex\Model\Carrier
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scope;

    /**
     * Model under test
     *
     * @var \Magento\Quote\Model\Quote\Address\RateResult\Error|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $error;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $errorFactory;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->scope = $this->getMockBuilder(
            \Magento\Framework\App\Config\ScopeConfigInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->scope->expects($this->any())->method('getValue')->willReturnCallback([$this, 'scopeConfiggetValue']);
        $country = $this->getMock(
            \Magento\Directory\Model\Country::class,
            ['load', 'getData', '__wakeup'],
            [],
            '',
            false
        );
        $country->expects($this->any())->method('load')->will($this->returnSelf());
        $countryFactory = $this->getMock(\Magento\Directory\Model\CountryFactory::class, ['create'], [], '', false);
        $countryFactory->expects($this->any())->method('create')->will($this->returnValue($country));

        $rate = $this->getMock(\Magento\Shipping\Model\Rate\Result::class, ['getError'], [], '', false);
        $rateFactory = $this->getMock(\Magento\Shipping\Model\Rate\ResultFactory::class, ['create'], [], '', false);
        $rateFactory->expects($this->any())->method('create')->will($this->returnValue($rate));
        $this->error = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\Error::class)
            ->setMethods(['setCarrier', 'setCarrierTitle', 'setErrorMessage'])->getMock();
        $this->errorFactory = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory::class)
            ->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->errorFactory->expects($this->any())->method('create')->willReturn($this->error);

        $store = $this->getMock(\Magento\Store\Model\Store::class, ['getBaseCurrencyCode', '__wakeup'], [], '', false);
        $storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)->getMock();

        $rateMethod = $this->getMock(
            \Magento\Quote\Model\Quote\Address\RateResult\Method::class,
            null,
            ['priceCurrency' => $priceCurrency]
        );
        $rateMethodFactory = $this->getMock(
            \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $rateMethodFactory->expects($this->any())->method('create')->will($this->returnValue($rateMethod));
        $this->_model = $this->getMock(
            \Magento\Fedex\Model\Carrier::class,
            ['_getCachedQuotes', '_debug'],
            [
                'scopeConfig' => $this->scope,
                'rateErrorFactory' => $this->errorFactory,
                'logger' => $this->getMock(\Psr\Log\LoggerInterface::class),
                'xmlSecurity' => new Security(),
                'xmlElFactory' => $this->getMock(
                    \Magento\Shipping\Model\Simplexml\ElementFactory::class,
                    [],
                    [],
                    '',
                    false
                ),
                'rateFactory' => $rateFactory,
                'rateMethodFactory' => $rateMethodFactory,
                'trackFactory' => $this->getMock(
                    \Magento\Shipping\Model\Tracking\ResultFactory::class,
                    [],
                    [],
                    '',
                    false
                ),
                'trackErrorFactory' => $this->getMock(\Magento\Shipping\Model\Tracking\Result\ErrorFactory::class, [], [], '', false),
                'trackStatusFactory' => $this->getMock(\Magento\Shipping\Model\Tracking\Result\StatusFactory::class, [], [], '', false),
                'regionFactory' => $this->getMock(\Magento\Directory\Model\RegionFactory::class, [], [], '', false),
                'countryFactory' => $countryFactory,
                'currencyFactory' => $this->getMock(\Magento\Directory\Model\CurrencyFactory::class, [], [], '', false),
                'directoryData' => $this->getMock(\Magento\Directory\Helper\Data::class, [], [], '', false),
                'stockRegistry' => $this->getMock(
                    \Magento\CatalogInventory\Model\StockRegistry::class,
                    [],
                    [],
                    '',
                    false
                ),
                'storeManager' => $storeManager,
                'configReader' => $this->getMock(\Magento\Framework\Module\Dir\Reader::class, [], [], '', false),
                'productCollectionFactory' => $this->getMock(
                        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
                        [],
                        [],
                        '',
                        false
                    ),
                'data' => [],
            ]
        );
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
        $this->_model->setRequest($requestMock);
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
        $this->_model->setRequest($requestMock);
    }

    /**
     * Callback function, emulates getValue function
     * @param $path
     * @return null|string
     */
    public function scopeConfiggetValue($path)
    {
        switch ($path) {
            case 'carriers/fedex/showmethod':
                return 1;
                break;
            case 'carriers/fedex/allowed_methods':
                return 'ServiceType';
                break;
        }
    }

    /**
     * @dataProvider collectRatesDataProvider
     */
    public function testCollectRatesRateAmountOriginBased($amount, $rateType, $expected)
    {
        $this->scope->expects($this->any())->method('isSetFlag')->will($this->returnValue(true));

        // @codingStandardsIgnoreStart
        $netAmount = new \Magento\Framework\DataObject([]);
        $netAmount->Amount = $amount;

        $totalNetCharge = new \Magento\Framework\DataObject([]);
        $totalNetCharge->TotalNetCharge = $netAmount;
        $totalNetCharge->RateType = $rateType;

        $ratedShipmentDetail = new \Magento\Framework\DataObject([]);
        $ratedShipmentDetail->ShipmentRateDetail = $totalNetCharge;

        $rate = new \Magento\Framework\DataObject([]);
        $rate->ServiceType = 'ServiceType';
        $rate->RatedShipmentDetails = [$ratedShipmentDetail];

        $response = new \Magento\Framework\DataObject([]);
        $response->HighestSeverity = 'SUCCESS';
        $response->RateReplyDetails = $rate;

        $this->_model->expects($this->any())->method('_getCachedQuotes')->will(
            $this->returnValue(serialize($response))
        );
        $request = $this->getMock(
            \Magento\Quote\Model\Quote\Address\RateRequest::class,
            ['getDestCity'],
            [],
            '',
            false
        );
        $request->expects($this->exactly(2))
            ->method('getDestCity')
            ->willReturn('Wonderful City');
        foreach ($this->_model->collectRates($request)->getAllRates() as $allRates) {
            $this->assertEquals($expected, $allRates->getData('cost'));
        }
        // @codingStandardsIgnoreEnd
    }

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
        $this->scope->expects($this->once())->method('isSetFlag')->willReturn(false);

        $this->error->expects($this->once())->method('setCarrier')->with('fedex');
        $this->error->expects($this->once())->method('setCarrierTitle');
        $this->error->expects($this->once())->method('setErrorMessage');

        $request = new RateRequest();
        $request->setPackageWeight(1);

        $this->assertSame($this->error, $this->_model->collectRates($request));
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
        $property->setValue($this->_model, $maskFields);

        $refMethod = $refClass->getMethod('filterDebugData');
        $refMethod->setAccessible(true);
        $result = $refMethod->invoke($this->_model, $data);
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
}
