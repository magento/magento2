<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Price;

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
     *
     * @dataProvider specialPriceDataProvider
     */
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
                ->will($this->returnValue($expected));
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
            ['getSpecialPrice', 'getPriceInfo', 'getStore', '__wakeup']
        );

        $saleableItemMock->expects($this->any())
            ->method('getSpecialPrice')
            ->will($this->returnValue($specialPrice));

        $priceInfo = $this->getMockBuilder(
            PriceInfoInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $priceInfo->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue([]));

        $saleableItemMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfo));

        return $saleableItemMock;
    }

    /**
     * @param bool $isValidInterval
     * @return MockObject|TimezoneInterface
     */
    protected function prepareLocaleDate($isValidInterval)
    {
        $localeDate = $this->getMockBuilder(
            TimezoneInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $localeDate->expects($this->any())
            ->method('isScopeDateInInterval')
            ->will($this->returnValue($isValidInterval));

        return $localeDate;
    }

    /**
     * @return array
     */
    public function specialPriceDataProvider()
    {
        return [
            'validInterval' => [
                'is_valid_date' => true,
                'special_price' => 50.15,
                'expected'      => 50.15,
            ],
            'validZeroValue' => [
                'is_valid_date' => true,
                'special_price' => 0.,
                'expected'      => 0.,
            ],
            'invalidInterval' => [
                'is_valid_date' => false,
                'special_price' => 20.,
                'expected'      => false,
            ]
        ];
    }
}
