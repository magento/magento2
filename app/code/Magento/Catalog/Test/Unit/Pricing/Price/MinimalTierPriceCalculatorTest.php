<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Pricing\Price\MinimalTierPriceCalculator;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;

class MinimalTierPriceCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MinimalTierPriceCalculator
     */
    private $object;

    /**
     * @var SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saleable;

    /**
     * @var PriceInfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceInfo;

    /**
     * @var TierPrice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $price;

    /**
     * @var CalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calculator;

    public function setUp()
    {
        $this->price = $this->getMock(TierPrice::class, [], [], '', false);
        $this->priceInfo = $this->getMockForAbstractClass(PriceInfoInterface::class);
        $this->saleable = $this->getMockForAbstractClass(SaleableInterface::class);

        $this->objectManager = new ObjectManager($this);

        $this->calculator = $this->getMockForAbstractClass(CalculatorInterface::class);
        $this->object = $this->objectManager->getObject(
            MinimalTierPriceCalculator::class,
            ['calculator' => $this->calculator]
        );
    }

    private function getValueTierPricesExistShouldReturnMinTierPrice()
    {
        $minPrice = 5;
        $notMinPrice = 10;

        $minAmount = $this->getMockForAbstractClass(AmountInterface::class);
        $minAmount->expects($this->once())->method('getValue')->willReturn($minPrice);

        $notMinAmount = $this->getMockForAbstractClass(AmountInterface::class);
        $notMinAmount->expects($this->once())->method('getValue')->willReturn($notMinPrice);

        $tierPriceList = [
            [
                'price' => $minAmount
            ],
            [
                'price' => $notMinAmount
            ]
        ];

        $this->price->expects($this->once())->method('getTierPriceList')->willReturn($tierPriceList);

        $this->priceInfo->expects($this->once())->method('getPrice')->with(TierPrice::PRICE_CODE)
            ->willReturn($this->price);

        $this->saleable->expects($this->once())->method('getPriceInfo')->willReturn($this->priceInfo);
        return $minPrice;
    }

    public function testGetValueTierPricesExistShouldReturnMinTierPrice()
    {
        $minPrice = $this->getValueTierPricesExistShouldReturnMinTierPrice();
        $this->assertEquals($minPrice, $this->object->getValue($this->saleable));
    }

    public function testGetGetAmountMinTierPriceExistShouldReturnAmountObject()
    {
        $minPrice = $this->getValueTierPricesExistShouldReturnMinTierPrice();

        $amount = $this->getMockForAbstractClass(AmountInterface::class);

        $this->calculator->expects($this->once())
            ->method('getAmount')
            ->with($minPrice, $this->saleable)
            ->willReturn($amount);

        $this->assertSame($amount, $this->object->getAmount($this->saleable));
    }
}
