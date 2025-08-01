<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SalesRule;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GetAppliedCartRulesTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var Uid
     */
    private $idEncoder;

    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->idEncoder = Bootstrap::getObjectManager()->get(Uid::class);
    }

    /**
     * Test to retrieve applied cart rules when promo/graphql/share_applied_cart_rule is enabled.
     */
    #[
        Config('promo/graphql/share_applied_cart_rule', true),
        Config('sales/multicoupon/maximum_number_of_coupons_per_order', '2'),
        DataFixture(SalesRuleFixture::class, [
            'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
            'coupon_code' => 'COUPON_1',
            'sort_order' => 10,
            'stop_rules_processing' => false
        ], as: 'rule1'),
        DataFixture(SalesRuleFixture::class, [
            'coupon_type' => SalesRule::COUPON_TYPE_NO_COUPON,
            'sort_order' => 20,
            'stop_rules_processing' => false
        ], as: 'rule2'),
        DataFixture(SalesRuleFixture::class, ['is_active' => 0, 'sort_order' => 30], as: 'rule3'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 10
        ]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testGetAppliedCartRules(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        $this->graphQlMutation($this->getApplyCouponMutation($maskedQuoteId, 'COUPON_1'));

        $this->assertEquals(
            $this->fetchAppliedSalesRules(),
            $this->graphQlQuery($this->getCartQuery($maskedQuoteId))
        );
    }

    /**
     * Test to retrieve applied cart rules when promo/graphql/share_applied_cart_rule is disabled.
     */
    #[
        Config('promo/graphql/share_applied_cart_rule', false),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$'
        ]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testGetAllCartRulesWhenConfigDisabled(): void
    {
        $this->assertEquals(
            [
                'cart' => [
                    'rules' => null
                ]
            ],
            $this->graphQlQuery($this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId()))
        );
    }

    /**
     * Test to retrieve applied cart rules when configuration is enabled but no cart rules are applied to the cart.
     */
    #[
        Config('promo/graphql/share_applied_cart_rule', true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$'
        ]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testGetAllCartRulesWhenConfigEnabledButRulesNotApplied(): void
    {
        $this->assertEquals(
            [
                'cart' => [
                    'rules' => []
                ]
            ],
            $this->graphQlQuery($this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId()))
        );
    }

    /**
     * Get applied cart rules
     *
     * @return array[]
     */
    private function fetchAppliedSalesRules(): array
    {
        return [
            'cart' => [
                'rules' => [
                    [
                        'uid' => $this->idEncoder->encode($this->fixtures->get('rule1')->getId())
                    ],
                    [
                        'uid' => $this->idEncoder->encode($this->fixtures->get('rule2')->getId())
                    ]
                ]
            ]
        ];
    }

    /**
     * Get apply coupon codes mutation
     *
     * @param string $cartId
     * @param string $couponCode
     * @return string
     */
    private function getApplyCouponMutation(string $cartId, string $couponCode): string
    {
        return <<<MUTATION
            mutation ApplyCouponToCart {
                applyCouponToCart(input: { cart_id: "{$cartId}", coupon_code: "{$couponCode}" }) {
                    cart {
                        id
                        applied_coupon {
                            code
                        }
                    }
                }
            }
        MUTATION;
    }

    /**
     * Get applied cart rules query
     *
     * @param string $cartId
     * @return string
     */
    private function getCartQuery(string $cartId): string
    {
        return <<<QUERY
            query Cart {
                cart(cart_id: "{$cartId}") {
                    rules {
                        uid
                    }
                }
            }
        QUERY;
    }
}
