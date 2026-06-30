<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\ApplyCoupon as ApplyCouponFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\AdminOrder\EmailSender;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\SalesRule\Test\Fixture\RuleCoupon as CouponFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for cart price rule usage limit handling during admin order edit.
 *
 * @see https://github.com/magento/magento2/issues/40624
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponUsageLimitOrderEditTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * Verify coupon rule with usage limits survives initFromOrder, quote reload, and quantity change.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        AppArea('adminhtml'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => 10,
                'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
                'uses_per_customer' => 1,
            ],
            'rule'
        ),
        DataFixture(
            CouponFixture::class,
            ['rule_id' => '$rule.id$', 'code' => 'LIMITED1', 'usage_limit' => 1, 'usage_per_customer' => 1],
            'coupon'
        ),
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], 'customer'),
        DataFixture(ProductFixture::class, ['price' => 100], 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(ApplyCouponFixture::class, ['cart_id' => '$cart.id$', 'coupon_codes' => ['LIMITED1']]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testRuleWithUsageLimitIsPreservedWhenEditingOrder(): void
    {
        $order = $this->fixtures->get('order');
        $ruleId = (int)$this->fixtures->get('rule')->getId();

        $this->assertNotEmpty($order->getAppliedRuleIds(), 'Order must have applied rules');
        $this->assertEquals('LIMITED1', $order->getCouponCode());

        $orderCreateModel = $this->createOrderCreateModel();
        $orderCreateModel->initFromOrder($order);

        $quote = $orderCreateModel->getQuote();
        $this->assertEquals('LIMITED1', $quote->getCouponCode());
        $this->assertDiscountPreserved($quote, $ruleId, 10.0, 'LIMITED1');

        $reloadedQuote = $this->simulateQuoteReloadAndRecollect($orderCreateModel, $order);
        $this->assertDiscountPreserved($reloadedQuote, $ruleId, 10.0, 'LIMITED1');

        $quote = $this->changeItemQtyAndRecollect($orderCreateModel, 2);
        $this->assertRulePreservedAfterQtyChange($quote, $ruleId, 'LIMITED1');
    }

    /**
     * Verify automatic cart price rule (no coupon) with uses_per_customer survives admin order edit flows.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        AppArea('adminhtml'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => 10,
                'coupon_type' => SalesRule::COUPON_TYPE_NO_COUPON,
                'uses_per_customer' => 1,
            ],
            'rule'
        ),
        DataFixture(CustomerFixture::class, ['email' => 'customer3@example.com'], 'customer'),
        DataFixture(ProductFixture::class, ['price' => 100], 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testAutomaticRuleWithUsagePerCustomerIsPreservedWhenEditingOrder(): void
    {
        $order = $this->fixtures->get('order');
        $ruleId = (int)$this->fixtures->get('rule')->getId();

        $this->assertNotEmpty($order->getAppliedRuleIds(), 'Order must have applied rules');
        $this->assertEmpty($order->getCouponCode());

        $orderCreateModel = $this->createOrderCreateModel();
        $orderCreateModel->initFromOrder($order);

        $quote = $orderCreateModel->getQuote();
        $this->assertDiscountPreserved($quote, $ruleId, 10.0);

        $reloadedQuote = $this->simulateQuoteReloadAndRecollect($orderCreateModel, $order);
        $this->assertDiscountPreserved($reloadedQuote, $ruleId, 10.0);

        $quote = $this->changeItemQtyAndRecollect($orderCreateModel, 2);
        $this->assertRulePreservedAfterQtyChange($quote, $ruleId);
    }

    /**
     * Verify that original_order_applied_rule_ids is set on the quote during order edit.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        AppArea('adminhtml'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => 10,
                'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
                'uses_per_customer' => 1,
            ],
            'rule'
        ),
        DataFixture(
            CouponFixture::class,
            ['rule_id' => '$rule.id$', 'code' => 'LIMITED2', 'usage_limit' => 1, 'usage_per_customer' => 1],
            'coupon'
        ),
        DataFixture(CustomerFixture::class, ['email' => 'customer2@example.com'], 'customer'),
        DataFixture(ProductFixture::class, ['price' => 100], 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(ApplyCouponFixture::class, ['cart_id' => '$cart.id$', 'coupon_codes' => ['LIMITED2']]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOriginalOrderRuleIdsAreSetOnQuoteDuringEdit(): void
    {
        $order = $this->fixtures->get('order');
        $orderCreateModel = $this->createOrderCreateModel();
        $orderCreateModel->initFromOrder($order);

        $quote = $orderCreateModel->getQuote();

        $this->assertEquals(
            $order->getAppliedRuleIds(),
            $quote->getData(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS),
            'Quote should carry the original order applied rule IDs for usage limit offset'
        );
    }

    /**
     * Create admin order create model primed for edit flow.
     */
    private function createOrderCreateModel(): Create
    {
        $this->objectManager->get(Registry::class)->unregister('rule_data');

        return $this->objectManager->create(
            Create::class,
            ['emailSender' => $this->createMock(EmailSender::class)]
        );
    }

    /**
     * Assert rule is applied and discount amount matches expectation.
     */
    private function assertDiscountPreserved(
        Quote $quote,
        int $ruleId,
        float $expectedDiscount,
        ?string $couponCode = null
    ): void {
        if ($couponCode !== null) {
            $this->assertEquals($couponCode, $quote->getCouponCode());
        }

        $this->assertNotEmpty(
            $quote->getAppliedRuleIds(),
            'Applied rule IDs should not be empty — the discount must be preserved during order edit'
        );
        $this->assertContains(
            (string)$ruleId,
            explode(',', (string)$quote->getAppliedRuleIds()),
            'Expected sales rule must remain applied during order edit'
        );

        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $this->assertEqualsWithDelta(
            $expectedDiscount,
            abs((float)$address->getDiscountAmount()),
            0.01,
            'Discount amount must reflect the preserved cart price rule'
        );
    }

    /**
     * Assert rule remains applied with a non-zero discount after quantity change.
     */
    private function assertRulePreservedAfterQtyChange(Quote $quote, int $ruleId, ?string $couponCode = null): void
    {
        if ($couponCode !== null) {
            $this->assertEquals($couponCode, $quote->getCouponCode());
        }

        $this->assertNotEmpty(
            $quote->getAppliedRuleIds(),
            'Applied rule IDs should not be empty after quantity change during order edit'
        );
        $this->assertContains(
            (string)$ruleId,
            explode(',', (string)$quote->getAppliedRuleIds()),
            'Expected sales rule must remain applied after quantity change during order edit'
        );

        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $this->assertGreaterThan(
            0,
            abs((float)$address->getDiscountAmount()),
            'Discount must remain applied after quantity change during order edit'
        );
    }

    /**
     * Simulate a subsequent admin request where the quote is reloaded from persistence.
     */
    private function simulateQuoteReloadAndRecollect(Create $orderCreateModel, Order $order): Quote
    {
        $quoteId = (int)$orderCreateModel->getQuote()->getId();
        $reloadedQuote = $this->quoteRepository->get($quoteId);
        $orderCreateModel->setQuote($reloadedQuote);

        $this->assertEmpty(
            $reloadedQuote->getData(Create::ORIGINAL_ORDER_APPLIED_RULE_IDS),
            'Reloaded quote must not carry edit-context data — session fallback should be used instead'
        );

        if ($order->getCouponCode()) {
            $reloadedQuote->setCouponCode($order->getCouponCode());
        }

        $reloadedQuote->collectTotals();

        return $reloadedQuote;
    }

    /**
     * Simulate admin changing item quantity and recollecting totals.
     */
    private function changeItemQtyAndRecollect(Create $orderCreateModel, float $qty): Quote
    {
        $item = current($orderCreateModel->getQuote()->getAllVisibleItems());
        $this->assertNotFalse($item, 'Edit quote must contain at least one visible item');

        $orderCreateModel->updateQuoteItems([(int)$item->getId() => ['qty' => $qty]]);
        $orderCreateModel->saveQuote();

        return $orderCreateModel->getQuote();
    }
}
