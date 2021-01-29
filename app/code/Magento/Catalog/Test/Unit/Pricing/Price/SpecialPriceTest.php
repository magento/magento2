<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Price;

class SpecialPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
            \Magento\Catalog\Pricing\Price\SpecialPrice::class,
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product
     */
    protected function prepareSaleableItem($specialPrice)
    {
        $saleableItemMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getSpecialPrice', 'getPriceInfo', 'getStore', '__wakeup']
        );

        $saleableItemMock->expects($this->any())
            ->method('getSpecialPrice')
            ->willReturn($specialPrice);

        $priceInfo = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceInfoInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $priceInfo->expects($this->any())
            ->method('getAdjustments')
            ->willReturn([]);

        $saleableItemMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfo);

        return $saleableItemMock;
    }

    /**
     * @param bool $isValidInterval
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected function prepareLocaleDate($isValidInterval)
    {
        $localeDate = $this->getMockBuilder(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();

        $localeDate->expects($this->any())
            ->method('isScopeDateInInterval')
            ->willReturn($isValidInterval);

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
