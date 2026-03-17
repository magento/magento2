<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SalesRule;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQL tests for the Free Gift cart price rule action
 */
class FreeGiftTest extends GraphQlAbstract
{
    private DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()
            ->get(DataFixtureStorageManager::class)
            ->getStorage();
    }

    /**
     * Verify that a free gift product is auto-added to the cart and discounted to $0
     * when a qualifying product is present and a free_gift rule is active.
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying1', 'url_key' => 'fg-qualifying1', 'price' => 50],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift1', 'url_key' => 'fg-gift1', 'price' => 25],
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
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$qualifyingProduct.id$',
            'qty' => 1,
        ]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftAutoAddedAndDiscounted(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $response = $this->graphQlQuery($this->getCartQuery($maskedQuoteId));

        $items = $response['cart']['items'];
        $this->assertCount(2, $items, 'Cart should contain the qualifying product and the auto-added gift');

        $giftItem = $this->findItemBySku($items, 'fg_gift1');
        $qualifyingItem = $this->findItemBySku($items, 'fg_qualifying1');

        $this->assertNotNull($giftItem, 'Gift product should be present in the cart');
        $this->assertNotNull($qualifyingItem, 'Qualifying product should be present in the cart');

        $this->assertEquals(1, $giftItem['quantity']);
        $this->assertEquals(25, $giftItem['prices']['price']['value']);

        $giftDiscounts = $giftItem['prices']['discounts'];
        $this->assertNotEmpty($giftDiscounts, 'Gift item should have a discount applied');
        $this->assertEquals(25, $giftDiscounts[0]['amount']['value'], 'Discount should equal the full item price');

        $qualifyingDiscounts = $qualifyingItem['prices']['discounts'] ?? [];
        $this->assertEmpty($qualifyingDiscounts, 'Qualifying product should not receive a discount');

        $cartDiscounts = $response['cart']['prices']['discounts'];
        $this->assertNotEmpty($cartDiscounts, 'Cart should have a discount total');
        $this->assertEquals(25, $cartDiscounts[0]['amount']['value']);

        $grandTotal = $response['cart']['prices']['grand_total']['value'];
        $this->assertEquals(50, $grandTotal, 'Grand total should only include the qualifying product price');
    }

    /**
     * Verify that the gift_qty cap is respected: only the configured qty is discounted to $0.
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying2', 'url_key' => 'fg-qualifying2', 'price' => 80],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift2', 'url_key' => 'fg-gift2', 'price' => 20],
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
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$qualifyingProduct.id$',
            'qty' => 1,
        ]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftQtyCap(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $response = $this->graphQlQuery($this->getCartQuery($maskedQuoteId));

        $items = $response['cart']['items'];
        $giftItem = $this->findItemBySku($items, 'fg_gift2');
        $this->assertNotNull($giftItem, 'Gift product should be present in the cart');
        $this->assertEquals(2, $giftItem['quantity'], 'Gift quantity should match gift_qty setting');

        $giftDiscounts = $giftItem['prices']['discounts'];
        $this->assertNotEmpty($giftDiscounts);
        $this->assertEquals(40, $giftDiscounts[0]['amount']['value'], 'Discount should cover 2 units at $20 each');

        $grandTotal = $response['cart']['prices']['grand_total']['value'];
        $this->assertEquals(80, $grandTotal, 'Grand total should only include the qualifying product price');
    }

    /**
     * Verify that a coupon-gated free gift rule does NOT auto-add the gift
     * until the coupon is applied.
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying3', 'url_key' => 'fg-qualifying3', 'price' => 30],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift3', 'url_key' => 'fg-gift3', 'price' => 15],
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
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$qualifyingProduct.id$',
            'qty' => 1,
        ]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftNotAddedWithoutCoupon(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $response = $this->graphQlQuery($this->getCartQuery($maskedQuoteId));

        $items = $response['cart']['items'];
        $this->assertCount(1, $items, 'Cart should only contain the original product, no gift');
        $this->assertEquals(
            'fg_qualifying3',
            $items[0]['product']['sku'],
            'Only the qualifying product should be in the cart'
        );

        $cartDiscounts = $response['cart']['prices']['discounts'] ?? [];
        $this->assertEmpty($cartDiscounts, 'No discount should be applied');
    }

    /**
     * Verify that when a coupon-based free gift rule is activated via applyCouponToCart,
     * the gift product appears and is discounted on the next cart query.
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_qualifying4', 'url_key' => 'fg-qualifying4', 'price' => 60],
            as: 'qualifyingProduct'
        ),
        DataFixture(
            ProductFixture::class,
            ['sku' => 'fg_gift4', 'url_key' => 'fg-gift4', 'price' => 30],
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
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$qualifyingProduct.id$',
            'qty' => 1,
        ]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testFreeGiftAddedAfterCouponApplied(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        $responseBefore = $this->graphQlQuery($this->getCartQuery($maskedQuoteId));
        $this->assertCount(1, $responseBefore['cart']['items'], 'Before coupon: only qualifying product');

        $this->graphQlMutation($this->getApplyCouponMutation($maskedQuoteId, 'FREE_GIFT_CODE'));

        $responseAfter = $this->graphQlQuery($this->getCartQuery($maskedQuoteId));
        $items = $responseAfter['cart']['items'];
        $giftItem = $this->findItemBySku($items, 'fg_gift4');
        $this->assertNotNull($giftItem, 'Gift product should be in the cart after coupon applied');
        $this->assertEquals(30, $giftItem['prices']['discounts'][0]['amount']['value']);

        $grandTotal = $responseAfter['cart']['prices']['grand_total']['value'];
        $this->assertEquals(60, $grandTotal, 'Grand total should only include the qualifying product');
    }

    private function findItemBySku(array $items, string $sku): ?array
    {
        foreach ($items as $item) {
            if ($item['product']['sku'] === $sku) {
                return $item;
            }
        }
        return null;
    }

    private function getCartQuery(string $cartId): string
    {
        return <<<QUERY
        query {
            cart(cart_id: "{$cartId}") {
                items {
                    quantity
                    product {
                        sku
                        name
                    }
                    prices {
                        price {
                            value
                            currency
                        }
                        row_total {
                            value
                        }
                        discounts {
                            label
                            amount {
                                value
                                currency
                            }
                        }
                    }
                }
                prices {
                    grand_total {
                        value
                        currency
                    }
                    discounts {
                        label
                        amount {
                            value
                            currency
                        }
                        applied_to
                    }
                }
            }
        }
        QUERY;
    }

    private function getApplyCouponMutation(string $cartId, string $couponCode): string
    {
        return <<<MUTATION
        mutation {
            applyCouponToCart(input: { cart_id: "{$cartId}", coupon_code: "{$couponCode}" }) {
                cart {
                    applied_coupon {
                        code
                    }
                }
            }
        }
        MUTATION;
    }
}
