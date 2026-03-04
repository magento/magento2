<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\Quote\Test\Fixture\ApplyCoupon as ApplyCouponFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class OrderTotalDiscountsCouponTest extends GraphQlAbstract
{
    private const PRODUCT_PRICE = 100;
    private const DISCOUNT_PERCENTAGE = 10;
    private const TOTAL_QTY = 2;
    private const DISCOUNT_LABEL = 'Flat 10 percent off';
    private const COUPON_CODE = 'SALE10';

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

    /**
     * Test graphql customer orders
     *
     * @return void
     * @throws AuthenticationException|LocalizedException
     * @throws Exception
     */
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
                'store_labels' => [1 => self::DISCOUNT_LABEL],
                'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
                'simple_action' => SalesRule::BY_PERCENT_ACTION,
                'discount_amount' => self::DISCOUNT_PERCENTAGE,
                'coupon_code' => self::COUPON_CODE,
                'conditions' => ['$condition$'],
                'uses_per_customer' => 1,
                'stop_rules_processing' => true
            ],
            as: 'rule'
        ),
        DataFixture(ProductFixture::class, ['price' => self::PRODUCT_PRICE], as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$',
                'qty' => self::TOTAL_QTY
            ]
        ),
        DataFixture(
            ApplyCouponFixture::class,
            [
                'cart_id' => '$quote.id$',
                'coupon_codes' => [self::COUPON_CODE]
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order'),
    ]
    public function testCustomerOrderWithDiscountCoupon(): void
    {
        self::assertEquals(
            [
                'customerOrders' => [
                    'items' => [
                        0 => [
                            'total' => [
                                'discounts' => [
                                    0 => [
                                        'coupon' => [
                                            'code' => self::COUPON_CODE
                                        ],
                                        'label' => self::DISCOUNT_LABEL,
                                        'amount' => [
                                            'value' => 20
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerOrdersQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Get customer orders query with total fields
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
{
    customerOrders {
        items {
            total {
                discounts {
                    coupon {
                        code
                    }
                    label
                    amount {
                        value
                    }
                }
            }
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
