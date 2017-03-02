<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Pricing\Price;

use \Magento\Bundle\Pricing\Price\SpecialPrice;

class SpecialPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SpecialPrice
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $saleable;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp()
    {
        $this->saleable = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDate = $this->getMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->priceInfo = $this->getMock(\Magento\Framework\Pricing\PriceInfo\Base::class, [], [], '', false);

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfo));

        $this->priceCurrencyMock = $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectHelper->getObject(
            \Magento\Bundle\Pricing\Price\SpecialPrice::class,
            [
                'saleableItem' => $this->saleable,
                'localeDate' => $this->localeDate,
                'priceCurrency' => $this->priceCurrencyMock
            ]
        );
    }

    /**
     * @param $regularPrice
     * @param $specialPrice
     * @param $isScopeDateInInterval
     * @param $value
     * @param $percent
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($regularPrice, $specialPrice, $isScopeDateInInterval, $value, $percent)
    {
        $specialFromDate =  'some date from';
        $specialToDate =  'some date to';

        $this->saleable->expects($this->once())
            ->method('getSpecialPrice')
            ->will($this->returnValue($specialPrice));

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->saleable->expects($this->once())
            ->method('getStore')
            ->will($this->returnValue($store));
        $this->saleable->expects($this->once())
            ->method('getSpecialFromDate')
            ->will($this->returnValue($specialFromDate));
        $this->saleable->expects($this->once())
            ->method('getSpecialToDate')
            ->will($this->returnValue($specialToDate));

        $this->localeDate->expects($this->once())
            ->method('isScopeDateInInterval')
            ->with($store, $specialFromDate, $specialToDate)
            ->will($this->returnValue($isScopeDateInInterval));

        $this->priceCurrencyMock->expects($this->never())
            ->method('convertAndRound');

        if ($isScopeDateInInterval) {
            $price = $this->getMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
            $this->priceInfo->expects($this->once())
                ->method('getPrice')
                ->with(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
                ->will($this->returnValue($price));
            $price->expects($this->once())
                ->method('getValue')
                ->will($this->returnValue($regularPrice));
        }

        $this->assertEquals($value, $this->model->getValue());

        //check that the second call will get data from cache the same as in first call
        $this->assertEquals($value, $this->model->getValue());
        $this->assertEquals($percent, $this->model->getDiscountPercent());
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return [
            ['regularPrice' => 100, 'specialPrice' => 40, 'isScopeDateInInterval' => true,  'value' => 40,
                'percent' => 40, ],
            ['regularPrice' => 75,  'specialPrice' => 40, 'isScopeDateInInterval' => true,  'value' => 30,
                'percent' => 40],
            ['regularPrice' => 75,  'specialPrice' => 40, 'isScopeDateInInterval' => false, 'value' => false,
                'percent' => null],
        ];
    }
}
