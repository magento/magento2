<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for cart-fixed discount helpers (single-ship remaining/cap + multi balance seed).
 */
class CartFixedDiscountTest extends TestCase
{
    use MockCreationTrait;

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
     * @var CartFixedDiscount
     */
    private CartFixedDiscount $cartFixedDiscount;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->deltaPriceRound = $this->createMock(DeltaPriceRound::class);
        $this->priceCurrency = $this->createPartialMockWithReflection(
            \Magento\Directory\Model\PriceCurrency::class,
            ['convert', 'roundPrice']
        );
        $this->shippingMethodConverter = $this->createMock(ShippingMethodConverter::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->cartFixedDiscount = new CartFixedDiscount(
            $this->deltaPriceRound,
            $this->priceCurrency,
            $this->shippingMethodConverter,
            $this->scopeConfig
        );
    }

    /**
     * @return void
     */
    public function testGetDiscountedAmountProportionally(): void
    {
        $this->deltaPriceRound->expects($this->once())
            ->method('round')
            ->with(5, 'fixed')
            ->willReturn(5.0);
        $this->assertSame(
            5.0,
            $this->cartFixedDiscount->getDiscountedAmountProportionally(5, 2.0, 10.0, 0.0, 10, 'fixed')
        );
    }

    /**
     * Single shipping seeds balance from quote remaining after item allocation.
     *
     * @return void
     */
    public function testGetCartFixedShippingRuleBalanceSingleUsesQuoteRemaining(): void
    {
        $rule = $this->createPartialMockWithReflection(Rule::class, ['getDiscountAmount']);
        $rule->method('getDiscountAmount')->willReturn(56.0);
        $quote = $this->createPartialMockWithReflection(Quote::class, ['getCartFixedRules']);
        $quote->method('getCartFixedRules')->willReturn([7 => 5.0]);

        $this->assertSame(
            5.0,
            $this->cartFixedDiscount->getCartFixedShippingRuleBalance($quote, $rule, 7, false)
        );
    }

    /**
     * Single shipping falls back to full rule amount when quote has no remaining key.
     *
     * @return void
     */
    public function testGetCartFixedShippingRuleBalanceSingleFallsBackToRuleAmount(): void
    {
        $rule = $this->createPartialMockWithReflection(Rule::class, ['getDiscountAmount']);
        $rule->method('getDiscountAmount')->willReturn(56.0);
        $quote = $this->createPartialMockWithReflection(Quote::class, ['getCartFixedRules']);
        $quote->method('getCartFixedRules')->willReturn([]);

        $this->assertSame(
            56.0,
            $this->cartFixedDiscount->getCartFixedShippingRuleBalance($quote, $rule, 7, false)
        );
    }

    /**
     * Multi shipping always seeds address balance with the full rule amount (legacy).
     *
     * @return void
     */
    public function testGetCartFixedShippingRuleBalanceMultiUsesFullRule(): void
    {
        $rule = $this->createPartialMockWithReflection(Rule::class, ['getDiscountAmount']);
        $rule->method('getDiscountAmount')->willReturn(56.0);
        $quote = $this->createPartialMockWithReflection(Quote::class, ['getCartFixedRules']);
        $quote->method('getCartFixedRules')->willReturn([7 => 5.0]);

        $this->assertSame(
            56.0,
            $this->cartFixedDiscount->getCartFixedShippingRuleBalance($quote, $rule, 7, true)
        );
    }

    /**
     * Single-ship cart-fixed shipping discount uses remaining balance and shipping cap.
     *
     * @param float $availableRuleBalance
     * @param float $shippingAmountForDiscount
     * @param float $baseShippingAmountForDiscount
     * @param float $appliedShippingDiscount
     * @param float $baseAppliedShippingDiscount
     * @param float $expectedQuoteAmount
     * @param float $expectedBaseAmount
     * @return void
     */
    #[DataProvider('calculateSingleShippingCartFixedDiscountDataProvider')]
    public function testCalculateSingleShippingCartFixedDiscountUsesRemainingAndCaps(
        float $availableRuleBalance,
        float $shippingAmountForDiscount,
        float $baseShippingAmountForDiscount,
        float $appliedShippingDiscount,
        float $baseAppliedShippingDiscount,
        float $expectedQuoteAmount,
        float $expectedBaseAmount
    ): void {
        $store = $this->createMock(Store::class);
        $quote = $this->createPartialMockWithReflection(Quote::class, ['getStore']);
        $quote->method('getStore')->willReturn($store);

        // Identity convert for unit math (quote currency == base).
        $this->priceCurrency->method('convert')->willReturnCallback(static function ($amount) {
            return $amount;
        });

        [$amount, $baseAmount] = $this->cartFixedDiscount->calculateSingleShippingCartFixedDiscount(
            $quote,
            $availableRuleBalance,
            $shippingAmountForDiscount,
            $baseShippingAmountForDiscount,
            $appliedShippingDiscount,
            $baseAppliedShippingDiscount
        );

        $this->assertSame($expectedQuoteAmount, $amount);
        $this->assertSame($expectedBaseAmount, $baseAmount);
    }

    /**
     * @return array<string, array<int, float>>
     */
    public static function calculateSingleShippingCartFixedDiscountDataProvider(): array
    {
        return [
            'remaining equals shipping' => [
                5.0,
                5.0,
                5.0,
                0.0,
                0.0,
                5.0,
                5.0,
            ],
            'remaining larger than shipping is capped' => [
                10.0,
                5.0,
                5.0,
                0.0,
                0.0,
                5.0,
                5.0,
            ],
            'remaining reduced by already applied shipping discount' => [
                5.0,
                5.0,
                5.0,
                2.0,
                2.0,
                3.0,
                3.0,
            ],
            'clamps to zero when applied exceeds shipping' => [
                5.0,
                5.0,
                5.0,
                6.0,
                6.0,
                0.0,
                0.0,
            ],
            'zero remaining yields zero discount' => [
                0.0,
                5.0,
                5.0,
                0.0,
                0.0,
                0.0,
                0.0,
            ],
        ];
    }

    /**
     * Sync writes remaining cart-fixed balance onto the quote ledger.
     *
     * @return void
     */
    public function testSyncQuoteCartFixedRuleBalance(): void
    {
        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            ['getCartFixedRules', 'setCartFixedRules']
        );
        $quote->method('getCartFixedRules')->willReturn([7 => 10.0]);
        $quote->expects($this->once())
            ->method('setCartFixedRules')
            ->with([7 => 3.0]);

        $this->cartFixedDiscount->syncQuoteCartFixedRuleBalance($quote, 7, 3.0);
    }

    /**
     * Sync initializes quote cart fixed rules when storage is missing/non-array.
     *
     * @return void
     */
    public function testSyncQuoteCartFixedRuleBalanceWhenRulesMissing(): void
    {
        $quote = $this->createPartialMockWithReflection(
            Quote::class,
            ['getCartFixedRules', 'setCartFixedRules']
        );
        $quote->method('getCartFixedRules')->willReturn(null);
        $quote->expects($this->once())
            ->method('setCartFixedRules')
            ->with([11 => 4.5]);

        $this->cartFixedDiscount->syncQuoteCartFixedRuleBalance($quote, 11, 4.5);
    }
}
