<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SalesRule;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Test\Fixture\ProductCondition as ProductConditionFixture;
use Magento\SalesRule\Test\Fixture\ProductFoundInCartConditions as ProductFoundFixture;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQL tests for the Free Gift cart price rule action.
 *
 * Tests use addProductsToCart / updateCartItems mutations instead of fixtures,
 * complete guest checkout, and verify order totals via the guestOrder query.
 */
class FreeGiftTest extends GraphQlAbstract
{
    private const GUEST_EMAIL = 'guest@example.com';
    private const BILLING_LASTNAME = 'Doe';

    private DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()
            ->get(DataFixtureStorageManager::class)
            ->getStorage();
    }

    /**
     * Auto-add free gift (no coupon): add qualifying product, verify gift appears with correct
     * discount in cart, place order, verify order totals.
     */
    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying1', 'price' => 50],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift1', 'price' => 25],
            as: 'giftProduct'
        ),
        DataFixture(SalesRuleFixture::class, [
            'simple_action' => 'free_gift',
            'gift_sku' => 'fg_gift1',
            'gift_qty' => 1,
            'discount_amount' => 0,
            'stop_rules_processing' => false,
            'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
        ], as: 'rule'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftAutoAddedAndDiscounted(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        $cartResponse = $this->addProductToCart($maskedQuoteId, 'fg_qualifying1', 1);
        $cart = $cartResponse['addProductsToCart']['cart'];

        $items = $cart['items'];
        $this->assertCount(2, $items, 'Cart should contain qualifying product and auto-added gift');

        $giftItem = $this->findItemBySku($items, 'fg_gift1');
        $qualifyingItem = $this->findItemBySku($items, 'fg_qualifying1');
        $this->assertNotNull($giftItem, 'Gift product should be in the cart');
        $this->assertNotNull($qualifyingItem, 'Qualifying product should be in the cart');

        $this->assertEquals(1, $giftItem['quantity']);
        $this->assertEquals(25, $giftItem['prices']['price']['value']);
        $this->assertNotEmpty($giftItem['prices']['discounts'], 'Gift should have discount');
        $this->assertEquals(25, $giftItem['prices']['discounts'][0]['amount']['value']);

        $qualifyingDiscounts = $qualifyingItem['prices']['discounts'] ?? [];
        $this->assertEmpty($qualifyingDiscounts, 'Qualifying product should have no discount');

        $this->assertEquals(50, $cart['prices']['grand_total']['value']);

        $orderNumber = $this->placeGuestOrder($maskedQuoteId);
        // Order grand total includes flatrate shipping ($5/item × 2 items = $10)
        $this->assertOrderTotals($orderNumber, 60, 75, 25, 'fg_gift1');
    }

    /**
     * Gift qty cap: rule with gift_qty = 2 adds 2 gift units, both fully discounted.
     */
    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying2', 'price' => 80],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift2', 'price' => 20],
            as: 'giftProduct'
        ),
        DataFixture(SalesRuleFixture::class, [
            'simple_action' => 'free_gift',
            'gift_sku' => 'fg_gift2',
            'gift_qty' => 2,
            'discount_amount' => 0,
            'stop_rules_processing' => false,
            'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
        ], as: 'rule'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftQtyCap(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        $cartResponse = $this->addProductToCart($maskedQuoteId, 'fg_qualifying2', 1);
        $cart = $cartResponse['addProductsToCart']['cart'];
        $items = $cart['items'];

        $giftItem = $this->findItemBySku($items, 'fg_gift2');
        $this->assertNotNull($giftItem, 'Gift product should be in the cart');
        $this->assertEquals(2, $giftItem['quantity'], 'Gift quantity should match gift_qty = 2');
        $this->assertNotEmpty($giftItem['prices']['discounts']);
        $this->assertEquals(40, $giftItem['prices']['discounts'][0]['amount']['value']);

        $this->assertEquals(80, $cart['prices']['grand_total']['value']);

        $orderNumber = $this->placeGuestOrder($maskedQuoteId);
        // Order grand total includes flatrate shipping ($5/item × 3 items = $15)
        $this->assertOrderTotals($orderNumber, 95, 120, 40, 'fg_gift2');
    }

    /**
     * Coupon-gated rule: gift is NOT added when no coupon is applied.
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying3', 'price' => 30],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift3', 'price' => 15],
            as: 'giftProduct'
        ),
        DataFixture(SalesRuleFixture::class, [
            'simple_action' => 'free_gift',
            'gift_sku' => 'fg_gift3',
            'gift_qty' => 1,
            'discount_amount' => 0,
            'stop_rules_processing' => false,
            'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
            'coupon_code' => 'FREEGIFT_COUPON',
        ], as: 'rule'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftNotAddedWithoutCoupon(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        $cartResponse = $this->addProductToCart($maskedQuoteId, 'fg_qualifying3', 1);
        $cart = $cartResponse['addProductsToCart']['cart'];
        $items = $cart['items'];

        $this->assertCount(1, $items, 'Cart should only contain the qualifying product');
        $this->assertEquals('fg_qualifying3', $items[0]['product']['sku']);

        $cartDiscounts = $cart['prices']['discounts'] ?? [];
        $this->assertEmpty($cartDiscounts, 'No discount should be applied');
    }

    /**
     * Coupon-gated rule: gift is added after coupon is applied, then place order and verify totals.
     */
    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying4', 'price' => 60],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift4', 'price' => 30],
            as: 'giftProduct'
        ),
        DataFixture(SalesRuleFixture::class, [
            'simple_action' => 'free_gift',
            'gift_sku' => 'fg_gift4',
            'gift_qty' => 1,
            'discount_amount' => 0,
            'stop_rules_processing' => false,
            'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
            'coupon_code' => 'FREE_GIFT_CODE',
        ], as: 'rule'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftAddedAfterCouponApplied(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        $cartResponse = $this->addProductToCart($maskedQuoteId, 'fg_qualifying4', 1);
        $cart = $cartResponse['addProductsToCart']['cart'];
        $this->assertCount(1, $cart['items'], 'Before coupon: only qualifying product');

        $this->graphQlMutation($this->getApplyCouponMutation($maskedQuoteId, 'FREE_GIFT_CODE'));

        $afterCouponCart = $this->graphQlQuery($this->getCartQuery($maskedQuoteId));
        $items = $afterCouponCart['cart']['items'];
        $giftItem = $this->findItemBySku($items, 'fg_gift4');
        $this->assertNotNull($giftItem, 'Gift should appear after coupon is applied');
        $this->assertEquals(30, $giftItem['prices']['discounts'][0]['amount']['value']);
        $this->assertEquals(60, $afterCouponCart['cart']['prices']['grand_total']['value']);

        $orderNumber = $this->placeGuestOrder($maskedQuoteId);
        // Order grand total includes flatrate shipping ($5/item × 2 items = $10)
        $this->assertOrderTotals($orderNumber, 70, 90, 30, 'fg_gift4');
    }

    /**
     * Quantity-based condition: gift is added only when cart item qty >= 2.
     * Add qty=1 (no gift), update to qty=2 (gift appears), place order, verify totals.
     */
    #[
        Config('carriers/flatrate/active', '1', 'store', 'default'),
        Config('payment/checkmo/active', '1', 'store', 'default'),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying5', 'price' => 40],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift5', 'price' => 15],
            as: 'giftProduct'
        ),
        DataFixture(
            ProductConditionFixture::class,
            ['attribute' => 'quote_item_qty', 'operator' => '>=', 'value' => '2'],
            as: 'qtyCondition'
        ),
        DataFixture(
            ProductFoundFixture::class,
            ['conditions' => ['$qtyCondition$']],
            as: 'foundCondition'
        ),
        DataFixture(SalesRuleFixture::class, [
            'simple_action' => 'free_gift',
            'gift_sku' => 'fg_gift5',
            'gift_qty' => 1,
            'discount_amount' => 0,
            'stop_rules_processing' => false,
            'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
            'conditions' => ['$foundCondition$'],
        ], as: 'rule'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftAddedOnlyWhenQtyConditionMet(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        // Add with qty=1 — condition (qty >= 2) not met, no gift
        $cartResponse = $this->addProductToCart($maskedQuoteId, 'fg_qualifying5', 1);
        $cart = $cartResponse['addProductsToCart']['cart'];
        $this->assertCount(1, $cart['items'], 'With qty=1 the condition is not met, no gift');
        $this->assertEmpty($cart['prices']['discounts'] ?? [], 'No discount with qty=1');

        // Add another unit — total qty becomes 2, condition now met, gift added
        $cartResponse2 = $this->addProductToCart($maskedQuoteId, 'fg_qualifying5', 1);
        $cart2 = $cartResponse2['addProductsToCart']['cart'];

        $qualifyingItem = $this->findItemBySku($cart2['items'], 'fg_qualifying5');
        $this->assertNotNull($qualifyingItem, 'Qualifying product should be in the cart');
        $this->assertEquals(2, $qualifyingItem['quantity'], 'Qualifying qty should be 2');

        $giftItem = $this->findItemBySku($cart2['items'], 'fg_gift5');
        $this->assertNotNull($giftItem, 'Gift should appear when qty condition (>=2) is met');

        // Query the cart fresh to ensure collectTotals runs with gift already persisted
        $freshCartResponse = $this->graphQlQuery($this->getCartQuery($maskedQuoteId));
        $freshCart = $freshCartResponse['cart'];
        $freshGift = $this->findItemBySku($freshCart['items'], 'fg_gift5');
        $this->assertNotNull($freshGift, 'Gift should be in fresh cart query');
        $this->assertNotEmpty($freshGift['prices']['discounts'], 'Gift should have discount');
        $this->assertEquals(15, $freshGift['prices']['discounts'][0]['amount']['value']);
        $this->assertEquals(80, $freshCart['prices']['grand_total']['value'], 'Grand total = 2x$40 qualifying');

        $orderNumber = $this->placeGuestOrder($maskedQuoteId);
        // Order grand total includes flatrate shipping ($5/item × 3 items = $15)
        $this->assertOrderTotals($orderNumber, 95, 95, 15, 'fg_gift5');
    }

    // ─── Helper: add product via mutation ────────────────────────────────

    private function addProductToCart(string $cartId, string $sku, int $qty): array
    {
        $query = <<<QUERY
        mutation {
            addProductsToCart(
                cartId: "{$cartId}"
                cartItems: [{ sku: "{$sku}", quantity: {$qty} }]
            ) {
                cart {
                    {$this->getCartFields()}
                }
            }
        }
        QUERY;

        return $this->graphQlMutation($query);
    }

    // ─── Helper: update cart item via mutation ────────────────────────────

    private function updateCartItem(string $cartId, string $itemUid, int $qty): array
    {
        $query = <<<MUTATION
        mutation {
            updateCartItems(
                input: {
                    cart_id: "{$cartId}"
                    cart_items: [{ cart_item_uid: "{$itemUid}", quantity: {$qty} }]
                }
            ) {
                cart {
                    {$this->getCartFields()}
                }
            }
        }
        MUTATION;

        return $this->graphQlMutation($query);
    }

    // ─── Helper: complete guest checkout and return order number ─────────

    private function placeGuestOrder(string $cartId): string
    {
        $this->graphQlMutation($this->getSetGuestEmailMutation($cartId));
        $this->graphQlMutation($this->getSetBillingAddressMutation($cartId));
        $this->graphQlMutation($this->getSetShippingAddressMutation($cartId));
        $this->graphQlMutation($this->getSetShippingMethodMutation($cartId));
        $this->graphQlMutation($this->getSetPaymentMethodMutation($cartId));

        $response = $this->graphQlMutation($this->getPlaceOrderMutation($cartId));

        return $response['placeOrder']['order']['order_number'];
    }

    // ─── Helper: assert order totals via guestOrder query ────────────────

    private function assertOrderTotals(
        string $orderNumber,
        float $expectedGrandTotal,
        float $expectedSubtotal,
        float $expectedGiftDiscount,
        string $giftSku
    ): void {
        $order = $this->getGuestOrder($orderNumber);

        $this->assertEquals(
            $expectedGrandTotal,
            $order['total']['grand_total']['value'],
            'Order grand total mismatch'
        );
        $this->assertEquals(
            $expectedSubtotal,
            $order['total']['subtotal']['value'],
            'Order subtotal mismatch'
        );

        $orderDiscounts = $order['total']['discounts'] ?? [];
        $this->assertNotEmpty($orderDiscounts, 'Order should have discounts');
        $totalDiscount = array_sum(array_column(array_column($orderDiscounts, 'amount'), 'value'));
        $this->assertEquals($expectedGiftDiscount, $totalDiscount, 'Order discount total mismatch');

        $giftOrderItem = null;
        foreach ($order['items'] as $item) {
            if ($item['product_sku'] === $giftSku) {
                $giftOrderItem = $item;
                break;
            }
        }
        $this->assertNotNull($giftOrderItem, "Gift SKU {$giftSku} should be in the order");
        $itemDiscounts = $giftOrderItem['discounts'] ?? [];
        $this->assertNotEmpty($itemDiscounts, 'Gift order item should have a discount');
        $this->assertEquals(
            $expectedGiftDiscount,
            $itemDiscounts[0]['amount']['value'],
            'Gift order item discount mismatch'
        );
    }

    private function getGuestOrder(string $orderNumber): array
    {
        $query = <<<QUERY
        {
            guestOrder(input: {
                number: "{$orderNumber}"
                email: "%s"
                lastname: "%s"
            }) {
                number
                total {
                    grand_total { value currency }
                    subtotal { value currency }
                    discounts { amount { value currency } label }
                    total_tax { value currency }
                }
                items {
                    product_sku
                    product_name
                    quantity_ordered
                    discounts { amount { value currency } label }
                }
            }
        }
        QUERY;

        $response = $this->graphQlQuery(
            sprintf($query, self::GUEST_EMAIL, self::BILLING_LASTNAME)
        );

        return $response['guestOrder'];
    }

    // ─── Helper: find item by SKU ────────────────────────────────────────

    private function findItemBySku(array $items, string $sku): ?array
    {
        foreach ($items as $item) {
            if ($item['product']['sku'] === $sku) {
                return $item;
            }
        }
        return null;
    }

    // ─── Shared cart response fields ─────────────────────────────────────

    private function getCartFields(): string
    {
        return <<<FIELDS
        items {
            uid
            quantity
            product { sku name }
            prices {
                price { value currency }
                row_total { value }
                discounts { label amount { value currency } }
            }
        }
        prices {
            grand_total { value currency }
            subtotal_excluding_tax { value currency }
            discounts { label amount { value currency } applied_to }
        }
        FIELDS;
    }

    // ─── Cart query ──────────────────────────────────────────────────────

    private function getCartQuery(string $cartId): string
    {
        return <<<QUERY
        {
            cart(cart_id: "{$cartId}") {
                {$this->getCartFields()}
            }
        }
        QUERY;
    }

    // ─── Checkout mutations ──────────────────────────────────────────────

    private function getApplyCouponMutation(string $cartId, string $couponCode): string
    {
        return <<<MUTATION
        mutation {
            applyCouponToCart(input: { cart_id: "{$cartId}", coupon_code: "{$couponCode}" }) {
                cart { applied_coupons { code } }
            }
        }
        MUTATION;
    }

    private function getSetGuestEmailMutation(string $cartId): string
    {
        $email = self::GUEST_EMAIL;
        return <<<MUTATION
        mutation {
            setGuestEmailOnCart(input: { cart_id: "{$cartId}", email: "{$email}" }) {
                cart { email }
            }
        }
        MUTATION;
    }

    private function getSetBillingAddressMutation(string $cartId): string
    {
        $lastname = self::BILLING_LASTNAME;
        return <<<MUTATION
        mutation {
            setBillingAddressOnCart(input: {
                cart_id: "{$cartId}"
                billing_address: {
                    address: {
                        firstname: "John"
                        lastname: "{$lastname}"
                        street: ["123 Main St"]
                        city: "Austin"
                        region: "TX"
                        postcode: "78701"
                        country_code: "US"
                        telephone: "5125551234"
                    }
                }
            }) {
                cart { billing_address { firstname } }
            }
        }
        MUTATION;
    }

    private function getSetShippingAddressMutation(string $cartId): string
    {
        $lastname = self::BILLING_LASTNAME;
        return <<<MUTATION
        mutation {
            setShippingAddressesOnCart(input: {
                cart_id: "{$cartId}"
                shipping_addresses: [{
                    address: {
                        firstname: "John"
                        lastname: "{$lastname}"
                        street: ["123 Main St"]
                        city: "Austin"
                        region: "TX"
                        postcode: "78701"
                        country_code: "US"
                        telephone: "5125551234"
                    }
                }]
            }) {
                cart {
                    shipping_addresses {
                        available_shipping_methods { carrier_code method_code }
                    }
                }
            }
        }
        MUTATION;
    }

    private function getSetShippingMethodMutation(string $cartId): string
    {
        return <<<MUTATION
        mutation {
            setShippingMethodsOnCart(input: {
                cart_id: "{$cartId}"
                shipping_methods: [{ carrier_code: "flatrate", method_code: "flatrate" }]
            }) {
                cart {
                    shipping_addresses { selected_shipping_method { carrier_code method_code } }
                }
            }
        }
        MUTATION;
    }

    private function getSetPaymentMethodMutation(string $cartId): string
    {
        return <<<MUTATION
        mutation {
            setPaymentMethodOnCart(input: {
                cart_id: "{$cartId}"
                payment_method: { code: "checkmo" }
            }) {
                cart { selected_payment_method { code } }
            }
        }
        MUTATION;
    }

    private function getPlaceOrderMutation(string $cartId): string
    {
        return <<<MUTATION
        mutation {
            placeOrder(input: { cart_id: "{$cartId}" }) {
                order { order_number }
            }
        }
        MUTATION;
    }
}
