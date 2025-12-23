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
use Magento\Quote\Test\Fixture\ApplyCoupon as ApplyCouponFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class OrderTotalGrandTotalExclTaxTest extends GraphQlAbstract
{
    private const PRODUCT_PRICE = 100;
    private const TOTAL_QTY = 1;
    private const DISCOUNT_LABEL = 'Flat 10 percent off';
    private const COUPON_CODE = 'SALE%uniqid%';

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
        Config('tax/calculation/apply_after_discount', false, "store", "default"),
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
                'discount_amount' => 100,
                'coupon_code' => self::COUPON_CODE,
                'conditions' => ['$condition$'],
                'uses_per_customer' => 10,
                'apply_to_shipping' => true,
                'stop_rules_processing' => true
            ],
            as: 'rule'
        ),
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, as: 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(ProductFixture::class, [
            'price' => self::PRODUCT_PRICE,
            'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$']
        ], as: 'product'),
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
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testGrandTotalExclTaxWithTaxAppliedBeforeDiscount(): void
    {
        $this->assertOrderResponse();
    }

    #[
        Config('tax/calculation/apply_after_discount', true, "store", "default"),
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
                'discount_amount' => 100,
                'coupon_code' => self::COUPON_CODE,
                'conditions' => ['$condition$'],
                'uses_per_customer' => 10,
                'apply_to_shipping' => true,
                'stop_rules_processing' => true
            ],
            as: 'rule'
        ),
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, as: 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(ProductFixture::class, [
            'price' => self::PRODUCT_PRICE,
            'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$']
        ], as: 'product'),
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
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testGrandTotalExclTaxWithTaxAppliedAfterDiscount(): void
    {
        $this->assertOrderResponse();
    }

    #[
        Config('tax/calculation/apply_after_discount', false, "store", "default"),
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, as: 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(ProductFixture::class, [
            'price' => self::PRODUCT_PRICE,
            'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$']
        ], as: 'product'),
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
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testGrandTotalExclTaxWithoutDiscount(): void
    {
        $this->assertOrderResponse();
    }

    /**
     * Assert order response for grand total excluding tax
     *
     * @return void
     * @throws AuthenticationException|LocalizedException
     * @throws Exception
     */
    private function assertOrderResponse(): void
    {
        /** @var OrderInterface $order */
        $order = $this->fixtures->get('order');
        $this->assertEquals(
            [
                'customer' => [
                    'orders' => [
                        'items' => [
                            [
                                'number' => $order->getIncrementId(),
                                'total' => [
                                    'grand_total' => [
                                        'value' => $order->getGrandTotal(),
                                        'currency' => 'USD'
                                    ],
                                    'grand_total_excl_tax' => [
                                        'value' => (float)($order->getSubtotal()
                                            + $order->getShippingAmount()
                                            - abs((float)$order->getDiscountAmount())),
                                        'currency' => 'USD'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerOrdersQuery(
                    $order->getIncrementId()
                ),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Get customer orders query with total fields
     *
     * @param string $orderId
     * @return string
     */
    private function getCustomerOrdersQuery(string $orderId): string
    {
        return <<<QUERY
            query Customer {
                customer {
                    orders(filter: { number: { eq: "{$orderId}" } }) {
                        items {
                            number
                            total {
                                ...OrderSummary
                            }
                        }
                    }
                }
            }
            fragment OrderSummary on OrderTotal {
                grand_total {
                    value
                    currency
                }
                grand_total_excl_tax {
                    value
                    currency
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
