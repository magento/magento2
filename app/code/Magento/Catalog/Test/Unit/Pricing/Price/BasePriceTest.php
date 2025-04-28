<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BasePriceTest extends TestCase
{
    /**
     * @var BasePrice|MockObject
     */
    protected $basePrice;

    /**
     * @var Base|MockObject
     */
    protected $priceInfoMock;

    /**
     * @var Product|MockObject
     */
    protected $saleableItemMock;

    /**
     * @var Calculator|MockObject
     */
    protected $calculatorMock;

    /**
     * @var RegularPrice|MockObject
     */
    protected $regularPriceMock;

    /**
     * @var TierPrice|MockObject
     */
    protected $tierPriceMock;

    /**
     * @var SpecialPrice|MockObject
     */
    protected $specialPriceMock;

    /**
     * @var MockObject[]
     */
    protected $prices;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $qty = 1;
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->priceInfoMock = $this->createMock(Base::class);
        $this->regularPriceMock = $this->createMock(RegularPrice::class);
        $this->tierPriceMock = $this->createMock(TierPrice::class);
        $this->specialPriceMock = $this->createMock(SpecialPrice::class);
        $this->calculatorMock = $this->createMock(Calculator::class);

        $this->saleableItemMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
        $this->prices = [
            'regular_price' => $this->regularPriceMock,
            'tier_price' => $this->tierPriceMock,
            'special_price' => $this->specialPriceMock,
        ];

        $helper = new ObjectManager($this);
        $this->basePrice = $helper->getObject(
            BasePrice::class,
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => $qty,
                'calculator' => $this->calculatorMock
            ]
        );
    }

    /**
     * test method getValue
     *
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($specialPriceValue, $expectedResult)
    {
        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->willReturn($this->prices);
        $this->regularPriceMock->expects($this->exactly(3))
            ->method('getValue')
            ->willReturn(100);
        $this->tierPriceMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn(99);
        $this->specialPriceMock->expects($this->any())
            ->method('getValue')
            ->willReturn($specialPriceValue);
        $this->assertSame($expectedResult, $this->basePrice->getValue());
    }

    /**
     * @return array
     */
    public static function getValueDataProvider()
    {
        return [[77, 77], [0, 0], [false, 99]];
    }

    public function testGetAmount()
    {
        $amount = 20.;

        $priceMock = $this->getMockBuilder(PriceInterface::class)
            ->getMockForAbstractClass();

        $this->priceInfoMock->expects($this->once())
            ->method('getPrices')
            ->willReturn([$priceMock]);

        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with(false, $this->saleableItemMock)
            ->willReturn($amount);

        $this->assertEquals($amount, $this->basePrice->getAmount());
    }
}
