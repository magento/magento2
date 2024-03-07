<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\MinimalTierPriceCalculator;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MinimalTierPriceCalculatorTest extends TestCase
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
     * @var SaleableInterface|MockObject
     */
    private $saleable;

    /**
     * @var PriceInfoInterface|MockObject
     */
    private $priceInfo;

    /**
     * @var TierPrice|MockObject
     */
    private $price;

    /**
     * @var CalculatorInterface|MockObject
     */
    private $calculator;

    protected function setUp(): void
    {
        $this->price = $this->createMock(TierPrice::class);
        $this->priceInfo = $this->getMockForAbstractClass(PriceInfoInterface::class);
        $this->saleable = $this->getMockForAbstractClass(SaleableInterface::class);

        $this->objectManager = new ObjectManager($this);

        $this->calculator = $this->getMockForAbstractClass(CalculatorInterface::class);
        $this->object = $this->objectManager->getObject(
            MinimalTierPriceCalculator::class,
            ['calculator' => $this->calculator]
        );
    }

    /**
     * @return int
     */
    private function getValueTierPricesExistShouldReturnMinTierPrice()
    {
        $minPrice = 5;
        $notMinPrice = 10;

        $minAmount = $this->getMockForAbstractClass(AmountInterface::class);
        $minAmount->expects($this->atLeastOnce())->method('getValue')->willReturn($minPrice);

        $notMinAmount = $this->getMockForAbstractClass(AmountInterface::class);
        $notMinAmount->expects($this->atLeastOnce())->method('getValue')->willReturn($notMinPrice);

        $tierPriceList = [
            [
                'price' => $minAmount
            ],
            [
                'price' => $notMinAmount
            ]
        ];

        $this->price->expects($this->once())->method('getTierPriceList')->willReturn($tierPriceList);

        $this->priceInfo->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturnCallback(fn($param) => match ([$param]) {
                [TierPrice::PRICE_CODE] => $this->price,
                [FinalPrice::PRICE_CODE] => $notMinAmount
            });

        $this->saleable->expects($this->atLeastOnce())->method('getPriceInfo')->willReturn($this->priceInfo);
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
        $amount->method('getValue')->willReturn($minPrice);

        $this->assertEquals($amount, $this->object->getAmount($this->saleable));
    }
}
