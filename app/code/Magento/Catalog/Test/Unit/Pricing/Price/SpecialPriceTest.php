<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Price;

class SpecialPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp()
    {
        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');

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
            'Magento\Catalog\Pricing\Price\SpecialPrice',
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     */
    protected function prepareSaleableItem($specialPrice)
    {
        $saleableItemMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getSpecialPrice', 'getPriceInfo', 'getStore', '__wakeup'],
            [],
            '',
            false
        );

        $saleableItemMock->expects($this->any())
            ->method('getSpecialPrice')
            ->will($this->returnValue($specialPrice));

        $priceInfo = $this->getMockBuilder(
            'Magento\Framework\Pricing\PriceInfoInterface'
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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected function prepareLocaleDate($isValidInterval)
    {
        $localeDate = $this->getMockBuilder(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface'
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
