<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Pricing\Price\SpecialPrice;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\WebsiteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpecialPriceTest extends TestCase
{
    /**
     * @var SpecialPrice
     */
    protected $model;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleable;

    /**
     * @var Base|MockObject
     */
    protected $priceInfo;

    /**
     * @var TimezoneInterface|MockObject
     */
    protected $localeDate;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->saleable = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDate = $this->getMockForAbstractClass(TimezoneInterface::class);
        $this->priceInfo = $this->createMock(Base::class);

        $this->saleable->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfo);

        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $objectHelper = new ObjectManager($this);
        $this->model = $objectHelper->getObject(
            SpecialPrice::class,
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
            $price = $this->getMockForAbstractClass(PriceInterface::class);
            $this->priceInfo->expects($this->once())
                ->method('getPrice')
                ->with(RegularPrice::PRICE_CODE)
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
    public static function getValueDataProvider()
    {
        return [
            ['regularPrice' => 100, 'specialPrice' => 40, 'isScopeDateInInterval' => true,  'value' => 40,
                'percent' => 40, ],
            ['regularPrice' => 75,  'specialPrice' => 40, 'isScopeDateInInterval' => true,  'value' => 30,
                'percent' => 40],
            ['regularPrice' => 75,  'specialPrice' => 40, 'isScopeDateInInterval' => false, 'value' => false,
                'percent' => null],
            ['regularPrice' => 100,  'specialPrice' => 0, 'isScopeDateInInterval' => true,  'value' => 0,
                'percent' => 0],
        ];
    }
}
