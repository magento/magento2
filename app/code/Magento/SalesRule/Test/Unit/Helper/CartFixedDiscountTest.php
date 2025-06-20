<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\DeltaPriceRound;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CartFixedDiscountTest extends TestCase
{
    /**
     * @var DeltaPriceRound|MockObject
     */
    private DeltaPriceRound $deltaPriceRound;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @var ShippingMethodConverter|MockObject
     */
    private ShippingMethodConverter $shippingMethodConverter;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @inhertidoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->deltaPriceRound = $this->createMock(DeltaPriceRound::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->shippingMethodConverter = $this->createMock(ShippingMethodConverter::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
    }

    /**
     * @return void
     */
    public function testGetDiscountedAmountProportionally(): void
    {
        $ruleDiscount = 5;
        $qty = 2.0;
        $baseItemPrice = 10.0;
        $baseItemDiscountAmount = 0.0;
        $baseRuleTotalsDiscount = 10;
        $discountType = 'fixed';
        $expected = 5.0;

        $cartFixedDiscount = new CartFixedDiscount(
            $this->deltaPriceRound,
            $this->priceCurrency,
            $this->shippingMethodConverter,
            $this->scopeConfig
        );
        $this->deltaPriceRound->expects($this->once())
            ->method('round')
            ->with(5, $discountType)
            ->willReturn($expected);
        $this->assertSame(
            $expected,
            $cartFixedDiscount->getDiscountedAmountProportionally(
                $ruleDiscount,
                $qty,
                $baseItemPrice,
                $baseItemDiscountAmount,
                $baseRuleTotalsDiscount,
                $discountType
            )
        );
    }
}
