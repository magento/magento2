<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Pricing\Price;

use \Magento\Bundle\Pricing\Price\SpecialPrice;
use Magento\Store\Api\Data\WebsiteInterface;

class SpecialPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SpecialPrice
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $saleable;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base |\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceInfo;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->saleable = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfo);

        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

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
            ->willReturn($specialPrice);

        $this->saleable->expects($this->once())
            ->method('getSpecialFromDate')
            ->willReturn($specialFromDate);
        $this->saleable->expects($this->once())
            ->method('getSpecialToDate')
            ->willReturn($specialToDate);

        $this->localeDate->expects($this->once())
            ->method('isScopeDateInInterval')
            ->with(WebsiteInterface::ADMIN_CODE, $specialFromDate, $specialToDate)
            ->willReturn($isScopeDateInInterval);

        $this->priceCurrencyMock->expects($this->never())
            ->method('convertAndRound');

        if ($isScopeDateInInterval) {
            $price = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
            $this->priceInfo->expects($this->once())
                ->method('getPrice')
                ->with(\Magento\Catalog\Pricing\Price\RegularPrice::PRICE_CODE)
                ->willReturn($price);
            $price->expects($this->once())
                ->method('getValue')
                ->willReturn($regularPrice);
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
