<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Model\TotalsInformationManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Single-shipping cart-fixed discount + shipping VAT regressions.
 *
 * Multi-shipping cart-fixed confidence is covered by CartFixedTest (unchanged multi path).
 *
 * Ticket matrix (EU-style B2C):
 * - price/shipping/discount tax: including tax
 * - shipping tax class: taxable goods
 * - cross-border trade: enabled
 * - flatrate per order: 5.00
 * - product 51 taxable, cart-fixed 56 apply_to_shipping
 *
 * @magentoAppArea frontend
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartFixedShippingTaxTest extends TestCase
{
    private const EPSILON = 0.01;

    private const COUPON_CODE = 'CART_FIXED_SHIP_TAX';

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CouponManagementInterface
     */
    private $couponManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->couponManagement = $objectManager->get(CouponManagementInterface::class);
    }

    /**
     * The bug path: coupon applied before shipping is set (incl tax, discount on incl).
     *
     * Expected: full rule −56, shipping discount 5.00 (incl VAT), grand total 0.
     * Buggy path used excl shipping basis → shipping discount 4.17 and residual 0.83.
     */
    #[
        DbIsolation(true),
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/calculation/price_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/discount_tax', '1', 'store', 'default'),
        Config('tax/calculation/apply_after_discount', '0', 'store', 'default'),
        Config('tax/calculation/cross_border_trade_enabled', '1', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        DataFixture(
            TaxRateFixture::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 20],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$'],
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => 51, 'tax_class_id' => 2], 'product'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 56,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'coupon_code' => self::COUPON_CODE,
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testCartFixedApplyToShippingCouponBeforeShippingInclTax(): void
    {
        $quote = $this->getCartQuote();
        $this->couponManagement->set((int) $quote->getId(), self::COUPON_CODE);
        $quote = $this->getCartQuote();
        $this->assignCaliforniaShipping($quote);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $quote = $this->getCartQuote();
        $address = $quote->getShippingAddress();

        $shippingDiscount = (float) $address->getShippingDiscountAmount();

        $this->assertGreaterThan(0.0, (float) $address->getTaxAmount(), 'Expected tax to apply');
        $this->assertEqualsWithDelta(0.0, (float) $quote->getGrandTotal(), self::EPSILON);
        $this->assertEqualsWithDelta(-56.0, (float) $address->getDiscountAmount(), self::EPSILON);
        // Explicit regression: must cover shipping incl VAT (5), not excl (4.17).
        $this->assertEqualsWithDelta(5.0, $shippingDiscount, self::EPSILON);
        $this->assertNotEqualsWithDelta(
            4.17,
            $shippingDiscount,
            self::EPSILON,
            'Shipping discount must not use excl-tax basis when discount_tax=1'
        );
    }

    /**
     * Control path: shipping method first, then coupon — same finals as coupon-first.
     */
    #[
        DbIsolation(true),
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/calculation/price_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/discount_tax', '1', 'store', 'default'),
        Config('tax/calculation/apply_after_discount', '0', 'store', 'default'),
        Config('tax/calculation/cross_border_trade_enabled', '1', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        DataFixture(
            TaxRateFixture::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 20],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$'],
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => 51, 'tax_class_id' => 2], 'product'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 56,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'coupon_code' => self::COUPON_CODE,
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testCartFixedApplyToShippingShippingBeforeCouponInclTax(): void
    {
        $quote = $this->getCartQuote();
        $this->assignCaliforniaShipping($quote);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->couponManagement->set((int) $quote->getId(), self::COUPON_CODE);
        $quote = $this->getCartQuote();
        $address = $quote->getShippingAddress();

        $shippingDiscount = (float) $address->getShippingDiscountAmount();

        $this->assertEqualsWithDelta(0.0, (float) $quote->getGrandTotal(), self::EPSILON);
        $this->assertEqualsWithDelta(-56.0, (float) $address->getDiscountAmount(), self::EPSILON);
        $this->assertEqualsWithDelta(5.0, $shippingDiscount, self::EPSILON);
        $this->assertNotEqualsWithDelta(4.17, $shippingDiscount, self::EPSILON);
    }

    /**
     * Cart estimate style.
     *
     * Models: guest cart with coupon, then TotalsInformationManagement::calculate with
     * destination + flatrate (shipping estimation / "estimate shipping and tax" style
     * recollect without a full checkout place-order flow).
     */
    #[
        DbIsolation(true),
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/calculation/price_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/discount_tax', '1', 'store', 'default'),
        Config('tax/calculation/apply_after_discount', '0', 'store', 'default'),
        Config('tax/calculation/cross_border_trade_enabled', '1', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        DataFixture(
            TaxRateFixture::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 20],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$'],
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => 51, 'tax_class_id' => 2], 'product'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 56,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'coupon_code' => self::COUPON_CODE,
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testTicket32468CartEstimateStyle(): void
    {
        $quote = $this->getCartQuote();
        $cartId = (int) $quote->getId();
        $this->couponManagement->set($cartId, self::COUPON_CODE);

        $objectManager = Bootstrap::getObjectManager();
        /** @var Address $address */
        $address = $objectManager->get(AddressFactory::class)->create();
        $address->setAddressType(Address::ADDRESS_TYPE_SHIPPING)
            ->setCountryId('US')
            ->setRegionId(12)
            ->setRegion('California')
            ->setPostcode('90210')
            ->setCity('Los Angeles');
        $addressInformation = $objectManager->create(
            TotalsInformationInterface::class,
            [
                'data' => [
                    'address' => $address,
                    'shipping_method_code' => 'flatrate',
                    'shipping_carrier_code' => 'flatrate',
                ],
            ]
        );

        /** @var TotalsInformationManagement $totalsManagement */
        $totalsManagement = $objectManager->get(TotalsInformationManagement::class);
        $totals = $totalsManagement->calculate($cartId, $addressInformation);

        $this->assertEqualsWithDelta(0.0, (float) $totals->getGrandTotal(), self::EPSILON);
        $this->assertEqualsWithDelta(-56.0, (float) $totals->getDiscountAmount(), self::EPSILON);

        // Persist path: re-load quote after estimate-style collect and assert shipping share.
        $quote = $this->getCartQuote();
        $shippingDiscount = (float) $quote->getShippingAddress()->getShippingDiscountAmount();
        $this->assertEqualsWithDelta(5.0, $shippingDiscount, self::EPSILON);
        $this->assertNotEqualsWithDelta(4.17, $shippingDiscount, self::EPSILON);
    }

    /**
     * Tax applied after discount (apply_after_discount=1) still fully covers 51+5 with rule 56.
     *
     * Ticket notes both apply_after_discount 0 and 1 reproduce the shipping VAT miss without the fix.
     */
    #[
        DbIsolation(true),
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/calculation/price_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/discount_tax', '1', 'store', 'default'),
        Config('tax/calculation/apply_after_discount', '1', 'store', 'default'),
        Config('tax/calculation/cross_border_trade_enabled', '1', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        DataFixture(
            TaxRateFixture::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 20],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$'],
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => 51, 'tax_class_id' => 2], 'product'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 56,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'coupon_code' => self::COUPON_CODE,
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testCartFixedApplyToShippingCouponBeforeShippingTaxAfterDiscount(): void
    {
        $quote = $this->getCartQuote();
        $this->couponManagement->set((int) $quote->getId(), self::COUPON_CODE);
        $quote = $this->getCartQuote();
        $this->assignCaliforniaShipping($quote);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $quote = $this->getCartQuote();
        $address = $quote->getShippingAddress();

        $this->assertEqualsWithDelta(0.0, (float) $quote->getGrandTotal(), self::EPSILON);
        $this->assertEqualsWithDelta(-56.0, (float) $address->getDiscountAmount(), self::EPSILON);
        $this->assertEqualsWithDelta(5.0, (float) $address->getShippingDiscountAmount(), self::EPSILON);
    }

    /**
     * Discount on excl tax (discount_tax=0): shipping discount uses excl shipping basis.
     *
     * Expected: shipping disc ≈ 4.17 (5/1.20), not full 5.00 VAT-inclusive cover.
     * Total discount is limited by excl-tax item+ship basis (≈ 46.67), not rule amount 56.
     */
    #[
        DbIsolation(true),
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/calculation/price_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/discount_tax', '0', 'store', 'default'),
        Config('tax/calculation/apply_after_discount', '0', 'store', 'default'),
        Config('tax/calculation/cross_border_trade_enabled', '1', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        DataFixture(
            TaxRateFixture::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 20],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$'],
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => 51, 'tax_class_id' => 2], 'product'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 56,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'coupon_code' => self::COUPON_CODE,
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testCartFixedApplyToShippingDiscountOnExclTax(): void
    {
        $quote = $this->getCartQuote();
        $this->couponManagement->set((int) $quote->getId(), self::COUPON_CODE);
        $quote = $this->getCartQuote();
        $this->assignCaliforniaShipping($quote);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $quote = $this->getCartQuote();
        $address = $quote->getShippingAddress();

        $this->assertGreaterThan(0.0, (float) $address->getTaxAmount(), 'Tax must apply for excl-discount assertions');
        // Shipping amount stored excl when shipping_includes_tax display path resolves excl basis.
        $this->assertEqualsWithDelta(4.17, (float) $address->getShippingAmount(), self::EPSILON);
        $this->assertEqualsWithDelta(
            4.17,
            (float) $address->getShippingDiscountAmount(),
            self::EPSILON,
            'With discount_tax=0 shipping discount must not cover full incl-tax 5.00'
        );
        $this->assertEqualsWithDelta(-46.67, (float) $address->getDiscountAmount(), self::EPSILON);
        // Must not exceed rule amount even when tax basis is excl.
        $this->assertLessThanOrEqual(56.0 + self::EPSILON, abs((float) $address->getDiscountAmount()));
    }

    /**
     * Partial remaining: rule 53 on items+ship basis 56 → shipping share ≈ 53 * 5/56 ≈ 4.73.
     */
    #[
        DbIsolation(true),
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/calculation/price_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/discount_tax', '1', 'store', 'default'),
        Config('tax/calculation/apply_after_discount', '0', 'store', 'default'),
        Config('tax/calculation/cross_border_trade_enabled', '1', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        DataFixture(
            TaxRateFixture::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 20],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$'],
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => 51, 'tax_class_id' => 2], 'product'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 53,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'coupon_code' => self::COUPON_CODE,
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testCartFixedPartialRemainingAppliedToShipping(): void
    {
        $quote = $this->getCartQuote();
        $this->assignCaliforniaShipping($quote);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $this->couponManagement->set((int) $quote->getId(), self::COUPON_CODE);
        $quote = $this->getCartQuote();
        $address = $quote->getShippingAddress();

        // Fixed 53 with items+ship basis 56 → shipping share ≈ 53 * 5/56 ≈ 4.73
        $this->assertEqualsWithDelta(4.73, (float) $address->getShippingDiscountAmount(), self::EPSILON);
        $this->assertEqualsWithDelta(-53.0, (float) $address->getDiscountAmount(), self::EPSILON);
        $this->assertLessThanOrEqual(53.0 + self::EPSILON, abs((float) $address->getDiscountAmount()));
    }

    /**
     * Money invariant: cart-fixed total discount never exceeds the rule amount.
     *
     * Rule 100 with product 51 + ship 5 (incl basis 56): discount must be ≤ 100 and
     * practically capped by cart value (≈ 56), shipping still fully covered at 5.00.
     */
    #[
        DbIsolation(true),
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/calculation/price_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/discount_tax', '1', 'store', 'default'),
        Config('tax/calculation/apply_after_discount', '0', 'store', 'default'),
        Config('tax/calculation/cross_border_trade_enabled', '1', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        DataFixture(
            TaxRateFixture::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 20],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$'],
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => 51, 'tax_class_id' => 2], 'product'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 100,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'coupon_code' => self::COUPON_CODE,
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testCartFixedDiscountNeverExceedsRuleAmount(): void
    {
        $ruleAmount = 100.0;
        $quote = $this->getCartQuote();
        $this->couponManagement->set((int) $quote->getId(), self::COUPON_CODE);
        $quote = $this->getCartQuote();
        $this->assignCaliforniaShipping($quote);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $quote = $this->getCartQuote();
        $address = $quote->getShippingAddress();

        $discountAmount = abs((float) $address->getDiscountAmount());
        $shippingDiscount = (float) $address->getShippingDiscountAmount();

        $this->assertLessThanOrEqual(
            $ruleAmount + self::EPSILON,
            $discountAmount,
            'Cart-fixed total discount must never exceed the rule amount'
        );
        // Cart basis is 51 + 5 = 56; oversize rule must not invent extra discount.
        $this->assertLessThanOrEqual(56.0 + self::EPSILON, $discountAmount);
        $this->assertEqualsWithDelta(5.0, $shippingDiscount, self::EPSILON);
        $this->assertEqualsWithDelta(0.0, (float) $quote->getGrandTotal(), self::EPSILON);
        $this->assertEqualsWithDelta(-56.0, (float) $address->getDiscountAmount(), self::EPSILON);
    }

    /**
     * Catalog excl tax, shipping incl tax, discount on incl tax (stable mixed config).
     */
    #[
        DbIsolation(true),
        Config('tax/classes/shipping_tax_class', '2', 'store', 'default'),
        Config('tax/calculation/price_includes_tax', '0', 'store', 'default'),
        Config('tax/calculation/based_on', 'shipping', 'store', 'default'),
        Config('tax/calculation/shipping_includes_tax', '1', 'store', 'default'),
        Config('tax/calculation/discount_tax', '1', 'store', 'default'),
        Config('tax/calculation/apply_after_discount', '0', 'store', 'default'),
        Config('tax/calculation/cross_border_trade_enabled', '1', 'store', 'default'),
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('carriers/flatrate/price', '5', 'store', 'default'),
        Config('carriers/flatrate/type', 'O', 'store', 'default'),
        DataFixture(
            TaxRateFixture::class,
            ['tax_country_id' => 'US', 'tax_region_id' => 0, 'tax_postcode' => '*', 'rate' => 20],
            'taxRate'
        ),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => [2],
                'tax_rate_ids' => ['$taxRate.id$'],
            ]
        ),
        DataFixture(ProductFixture::class, ['price' => 42.5, 'tax_class_id' => 2], 'product'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 56,
                'apply_to_shipping' => 1,
                'stop_rules_processing' => 0,
                'coupon_code' => self::COUPON_CODE,
            ],
            'rule'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testCartFixedMixedCatalogExclShippingInclDiscountIncl(): void
    {
        $quote = $this->getCartQuote();
        $this->couponManagement->set((int) $quote->getId(), self::COUPON_CODE);
        $quote = $this->getCartQuote();
        $this->assignCaliforniaShipping($quote);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $quote = $this->getCartQuote();
        $address = $quote->getShippingAddress();

        $this->assertEqualsWithDelta(0.0, (float) $quote->getGrandTotal(), self::EPSILON);
        $this->assertEqualsWithDelta(-56.0, (float) $address->getDiscountAmount(), self::EPSILON);
        $this->assertEqualsWithDelta(5.0, (float) $address->getShippingDiscountAmount(), self::EPSILON);
        $this->assertLessThanOrEqual(56.0 + self::EPSILON, abs((float) $address->getDiscountAmount()));
    }

    private function getCartQuote(): Quote
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        return $this->quoteRepository->get((int) $cart->getId());
    }

    private function assignCaliforniaShipping(Quote $quote): void
    {
        $addressData = [
            'firstname' => 'Test',
            'lastname' => 'User',
            'street' => ['123 Test St'],
            'city' => 'Los Angeles',
            'region' => 'CA',
            'region_id' => 12,
            'postcode' => '90210',
            'country_id' => 'US',
            'telephone' => '5555555555',
            'email' => 'cart-fixed-shipping-tax@example.com',
        ];
        $quote->getBillingAddress()->addData($addressData);
        $quote->getShippingAddress()
            ->addData($addressData)
            ->setCollectShippingRates(true)
            ->setShippingMethod('flatrate_flatrate')
            ->setSameAsBilling(1);
    }
}
