<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Model;

use Magento\Framework\Object;

/**
 * Class CarrierTest
 * @package Magento\Fedex\Model
 * TODO refactor me
 */
class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \Magento\Fedex\Model\Carrier
     */
    protected $_model;

    /**
     * @return void
     */
    public function setUp()
    {
        $scopeConfig = $this->getMockForAbstractClass('Magento\Framework\App\Config\ScopeConfigInterface');
        $scopeConfig->expects($this->any())->method('isSetFlag')->will($this->returnValue(true));
        $scopeConfig->expects($this->any())->method('getValue')->will($this->returnValue('ServiceType'));
        $country = $this->getMock(
            'Magento\Directory\Model\Country',
            ['load', 'getData', '__wakeup'],
            [],
            '',
            false
        );
        $country->expects($this->any())->method('load')->will($this->returnSelf());
        $countryFactory = $this->getMock('Magento\Directory\Model\CountryFactory', ['create'], [], '', false);
        $countryFactory->expects($this->any())->method('create')->will($this->returnValue($country));

        $rate = $this->getMock('Magento\Shipping\Model\Rate\Result', ['getError'], [], '', false);
        $rateFactory = $this->getMock('Magento\Shipping\Model\Rate\ResultFactory', ['create'], [], '', false);
        $rateFactory->expects($this->any())->method('create')->will($this->returnValue($rate));

        $store = $this->getMock('Magento\Store\Model\Store', ['getBaseCurrencyCode', '__wakeup'], [], '', false);
        $storeManager = $this->getMockForAbstractClass('Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
        $priceCurrency->expects($this->once())
            ->method('round')
            ->willReturnCallback(
                function ($price) {
                    round($price, 2);
                }
            );
        $rateMethod = $this->getMock(
            'Magento\Sales\Model\Quote\Address\RateResult\Method',
            null,
            ['priceCurrency' => $priceCurrency]
        );
        $rateMethodFactory = $this->getMock(
            'Magento\Sales\Model\Quote\Address\RateResult\MethodFactory',
            ['create'],
            [],
            '',
            false
        );
        $rateMethodFactory->expects($this->any())->method('create')->will($this->returnValue($rateMethod));
        $this->_model = $this->getMock(
            'Magento\Fedex\Model\Carrier',
            ['_getCachedQuotes', '_debug'],
            [
                'scopeConfig' => $scopeConfig,
                'rateErrorFactory' =>
                    $this->getMock('Magento\Sales\Model\Quote\Address\RateResult\ErrorFactory', [], [], '', false),
                'logger' => $this->getMock('Psr\Log\LoggerInterface'),
                'xmlElFactory' => $this->getMock('Magento\Shipping\Model\Simplexml\ElementFactory', [], [], '', false),
                'rateFactory' => $rateFactory,
                'rateMethodFactory' => $rateMethodFactory,
                'trackFactory' => $this->getMock('Magento\Shipping\Model\Tracking\ResultFactory', [], [], '', false),
                'trackErrorFactory' =>
                    $this->getMock('Magento\Shipping\Model\Tracking\Result\ErrorFactory', [], [], '', false),
                'trackStatusFactory' =>
                    $this->getMock('Magento\Shipping\Model\Tracking\Result\StatusFactory', [], [], '', false),
                'regionFactory' => $this->getMock('Magento\Directory\Model\RegionFactory', [], [], '', false),
                'countryFactory' => $countryFactory,
                'currencyFactory' => $this->getMock('Magento\Directory\Model\CurrencyFactory', [], [], '', false),
                'directoryData' => $this->getMock('Magento\Directory\Helper\Data', [], [], '', false),
                'stockRegistry' => $this->getMock('Magento\CatalogInventory\Model\StockRegistry', [], [], '', false),
                'storeManager' => $storeManager,
                'configReader' => $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false),
                'productCollectionFactory' =>
                    $this->getMock('Magento\Catalog\Model\Resource\Product\CollectionFactory', [], [], '', false),
                'data' => []
            ]
        );
    }

    /**
     * @dataProvider collectRatesDataProvider
     */
    public function testCollectRatesRateAmountOriginBased($amount, $rateType, $expected)
    {
        // @codingStandardsIgnoreStart
        $netAmount = new \Magento\Framework\Object([]);
        $netAmount->Amount = $amount;

        $totalNetCharge = new \Magento\Framework\Object([]);
        $totalNetCharge->TotalNetCharge = $netAmount;
        $totalNetCharge->RateType = $rateType;

        $ratedShipmentDetail = new \Magento\Framework\Object([]);
        $ratedShipmentDetail->ShipmentRateDetail = $totalNetCharge;

        $rate = new \Magento\Framework\Object([]);
        $rate->ServiceType = 'ServiceType';
        $rate->RatedShipmentDetails = [$ratedShipmentDetail];

        $response = new \Magento\Framework\Object([]);
        $response->HighestSeverity = 'SUCCESS';
        $response->RateReplyDetails = $rate;

        $this->_model->expects($this->any())->method('_getCachedQuotes')->will(
            $this->returnValue(serialize($response))
        );
        $request = $this->getMock('Magento\Sales\Model\Quote\Address\RateRequest', [], [], '', false);
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
}
