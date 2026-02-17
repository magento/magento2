<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\Quote\Test\Fixture\ApplyCoupon as ApplyCouponFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class AvailablePaymentMethodsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        DataFixture(
            AddressConditionFixture::class,
            [
                'attribute' => 'total_qty',
                'operator' => '>=',
                'value' => 1
            ],
            'condition'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => 'Flat 100 percent off'],
                'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => 100,
                'coupon_code' => "SALE100",
                'conditions' => ['$condition$'],
                'uses_per_customer' => 1,
                'stop_rules_processing' => true,
                'simple_free_shipping' => 1
            ],
            as: 'rule'
        ),
        DataFixture(ProductFixture::class, ['price' => 100], as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        ),
        DataFixture(
            ApplyCouponFixture::class,
            [
                'cart_id' => '$quote.id$',
                'coupon_codes' => ["SALE100"]
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testAvailablePaymentMethodsWhenOrderTotalZero(): void
    {
        self::assertEquals(
            [
                'cart' => [
                    'available_payment_methods' => [
                        0 => [
                            'code' => 'free',
                            'title' => 'No Payment Information Required'
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    #[
        DataFixture(
            AddressConditionFixture::class,
            [
                'attribute' => 'total_qty',
                'operator' => '>=',
                'value' => 1
            ],
            'condition'
        ),
        DataFixture(
            SalesRuleFixture::class,
            [
                'store_labels' => [1 => 'Flat 50 percent off'],
                'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => 50,
                'coupon_code' => "SALE50",
                'conditions' => ['$condition$'],
                'uses_per_customer' => 1,
                'stop_rules_processing' => true,
                'simple_free_shipping' => 1
            ],
            as: 'rule'
        ),
        DataFixture(ProductFixture::class, ['price' => 110], as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        ),
        DataFixture(
            ApplyCouponFixture::class,
            [
                'cart_id' => '$quote.id$',
                'coupon_codes' => ["SALE50"]
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testAvailablePaymentMethodsWhenOrderTotalNonZero(): void
    {
        self::assertEquals(
            [
                'cart' => [
                    'available_payment_methods' => [
                        0 => [
                            'code' => 'checkmo',
                            'title' => 'Check / Money order',
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartQuery($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Get customer cart query with available_payment_methods fields
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
        {
            cart(cart_id: "{$maskedQuoteId}") {
                available_payment_methods{
                    code
                    title
                }
            }
        }
        QUERY;
    }

    /**
     * Returns the header with customer token for GQL Mutation
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
