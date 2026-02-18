<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpecialPriceTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param bool $isValidInterval
     * @param float $specialPrice
     * @param float|bool $specialPriceValue
     */
    #[DataProvider('specialPriceDataProvider')]
    public function testGetValue($isValidInterval, $specialPrice, $specialPriceValue)
    {
        $expected = 56.34;
        $specialPriceModel = $this->objectManager->getObject(
            SpecialPrice::class,
            [
                'saleableItem' => $this->prepareSaleableItem($specialPrice),
                'localeDate'  => $this->prepareLocaleDate($isValidInterval),
                'priceCurrency' => $this->priceCurrencyMock,
            ]
        );

        if ($isValidInterval) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convertAndRound')
                ->with($specialPriceValue)
                ->willReturn($expected);
        } else {
            $expected = $specialPriceValue;
        }

        $this->assertSame($expected, $specialPriceModel->getValue());
    }

    /**
     * @param float $specialPrice
     * @return MockObject|Product
     */
    protected function prepareSaleableItem($specialPrice)
    {
        $saleableItemMock = $this->createPartialMock(
            Product::class,
            ['getSpecialPrice', 'getPriceInfo', 'getStore']
        );

        $saleableItemMock->method('getSpecialPrice')->willReturn($specialPrice);

        $priceInfo = $this->createMock(PriceInfoInterface::class);

        $priceInfo->method('getAdjustments')->willReturn([]);

        $saleableItemMock->method('getPriceInfo')->willReturn($priceInfo);

        return $saleableItemMock;
    }

    /**
     * @param bool $isValidInterval
     * @return MockObject|TimezoneInterface
     */
    protected function prepareLocaleDate($isValidInterval)
    {
        $localeDate = $this->createMock(TimezoneInterface::class);

        $localeDate->method('isScopeDateInInterval')->willReturn($isValidInterval);

        return $localeDate;
    }

    /**
     * @return array
     */
    public static function specialPriceDataProvider()
    {
        return [
            'validInterval' => [
                'isValidInterval' => true,
                'specialPrice' => 50.15,
                'specialPriceValue'      => 50.15,
            ],
            'validZeroValue' => [
                'isValidInterval' => true,
                'specialPrice' => 0.,
                'specialPriceValue'      => 0.,
            ],
            'invalidInterval' => [
                'isValidInterval' => false,
                'specialPrice' => 20.,
                'specialPriceValue'      => false,
            ]
        ];
    }
}
