<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Price;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractPriceTest extends TestCase
{
    /**
     * @var AbstractPrice
     */
    protected $price;

    /**
     * @var Base|MockObject
     */
    protected $priceInfoMock;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleableItemMock;

    /**
     * @var Calculator|MockObject
     */
    protected $calculatorMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $qty = 1;
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->priceInfoMock = $this->createMock(Base::class);
        $this->calculatorMock = $this->createMock(Calculator::class);

        $this->saleableItemMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
        $objectManager = new ObjectManager($this);

        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $this->price = $objectManager->getObject(
            Stub::class,
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => $qty,
                'calculator' => $this->calculatorMock,
                'priceCurrency' => $this->priceCurrencyMock,
            ]
        );
    }

    /**
     * Test method testGetDisplayValue
     */
    public function testGetAmount()
    {
        $priceValue = $this->price->getValue();
        $amountValue = 88;
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($priceValue)
            ->willReturn($amountValue);
        $this->assertEquals($amountValue, $this->price->getAmount());
    }

    /**
     * Test method getPriceType
     */
    public function testGetPriceCode()
    {
        $this->assertEquals(AbstractPrice::PRICE_CODE, $this->price->getPriceCode());
    }

    public function testGetCustomAmount()
    {
        $exclude = false;
        $amount = 21.0;
        $convertedValue = 30.25;
        $customAmount = 42.0;

        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->with($amount)
            ->willReturn($convertedValue);
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($convertedValue, $this->saleableItemMock, $exclude)
            ->willReturn($customAmount);

        $this->assertEquals($customAmount, $this->price->getCustomAmount($amount, $exclude));
    }

    public function testGetCustomAmountDefault()
    {
        $customAmount = 42.0;
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($this->price->getValue(), $this->saleableItemMock, null)
            ->willReturn($customAmount);

        $this->assertEquals($customAmount, $this->price->getCustomAmount());
    }

    public function testGetQuantity()
    {
        $this->assertEquals(1, $this->price->getQuantity());
    }
}
