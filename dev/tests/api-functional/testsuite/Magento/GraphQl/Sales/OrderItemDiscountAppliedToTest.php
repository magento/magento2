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
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\ApplyCoupon as ApplyCouponFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Test\Fixture\Rule as SalesRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test customer orders GraphQL query with coupon codes
 */
class OrderItemDiscountAppliedToTest extends GraphQlAbstract
{
    private const COUPON_CODE = 'COUPON-CODE-2025';
    private const DISCOUNT_AMOUNT = 10;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test that applied_to field returns correct value for item-level discounts
     *
     * @throws Exception
     */
    #[
        DataFixture(
            SalesRuleFixture::class,
            [
                'name' => 'Test Sales Rule with Coupon',
                'is_active' => 1,
                'coupon_type' => SalesRule::COUPON_TYPE_SPECIFIC,
                'coupon_code' => self::COUPON_CODE,
                'discount_amount' => self::DISCOUNT_AMOUNT,
                'simple_action' => 'by_percent',
                'stop_rules_processing' => false,
                'is_advanced' => 1
            ],
            as: 'sales_rule'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100.00,
                'sku' => 'test-product-coupon'
            ],
            as: 'product'
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        ),
        DataFixture(
            ApplyCouponFixture::class,
            [
                'cart_id' => '$cart.id$',
                'coupon_codes' => [self::COUPON_CODE]
            ]
        ),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order')
    ]
    public function testOrderItemDiscountAppliedToFieldForItemDiscount()
    {
        $customer = $this->fixtures->get('customer');
        $currentEmail = $customer->getEmail();
        $currentPassword = 'password';

        // Generate customer token
        $generateToken = $this->generateCustomerTokenMutation($currentEmail, $currentPassword);
        $tokenResponse = $this->graphQlMutation($generateToken);
        $customerToken = $tokenResponse['generateCustomerToken']['token'];

        // Query customer orders with detailed item information including discounts and coupons
        $query = $this->getCustomerOrdersWithCouponQuery();
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken)
        );

        // Validate response structure
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);

        $order = $response['customer']['orders']['items'][0];

        // Validate order has items
        $this->assertArrayHasKey('items', $order);
        $this->assertNotEmpty($order['items']);

        $orderItem = $order['items'][0];

        // Validate order item structure
        $this->assertArrayHasKey('product_sku', $orderItem);
        $this->assertEquals('test-product-coupon', $orderItem['product_sku']);
        $this->assertArrayHasKey('discounts', $orderItem);
        $this->assertNotEmpty($orderItem['discounts']);
        $discount = $orderItem['discounts'][0];
        $this->assertArrayHasKey('applied_to', $discount);
        $this->assertEquals('ITEM', $discount['applied_to']);
    }
    
    /**
     * Test that applied_to field returns correct value for shipping discounts
     *
     * @throws Exception
     */
    #[
        DataFixture(
            SalesRuleFixture::class,
            [
                'name' => 'Test Shipping Discount Rule',
                'is_active' => 1,
                'coupon_type' => SalesRule::COUPON_TYPE_NO_COUPON,
                'discount_amount' => self::DISCOUNT_AMOUNT,
                'simple_action' => 'by_percent',
                'apply_to_shipping' => 1,
                'stop_rules_processing' => false,
                'is_advanced' => 1
            ],
            as: 'sales_rule'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'price' => 100.00,
                'sku' => 'test-product-coupon'
            ],
            as: 'product'
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 1
            ]
        ),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order')
    ]
    public function testOrderItemDiscountAppliedToFieldForShippingDiscount()
    {
        $customer = $this->fixtures->get('customer');
        $currentEmail = $customer->getEmail();
        $currentPassword = 'password';

        // Generate customer token
        $generateToken = $this->generateCustomerTokenMutation($currentEmail, $currentPassword);
        $tokenResponse = $this->graphQlMutation($generateToken);
        $customerToken = $tokenResponse['generateCustomerToken']['token'];

        // Query customer orders with detailed item information including discounts and coupons
        $query = $this->getCustomerOrdersWithCouponQuery();
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken)
        );

        // Validate response structure
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);

        $order = $response['customer']['orders']['items'][0];

        // Validate order has items
        $this->assertArrayHasKey('items', $order);
        $this->assertNotEmpty($order['items']);

        $orderItem = $order['items'][0];

        // Validate order item structure
        $this->assertArrayHasKey('product_sku', $orderItem);
        $this->assertEquals('test-product-coupon', $orderItem['product_sku']);
        $this->assertArrayHasKey('discounts', $orderItem);
        $this->assertNotEmpty($orderItem['discounts']);
        $discount = $orderItem['discounts'][0];

        $this->assertArrayHasKey('applied_to', $discount);
        $this->assertEquals('SHIPPING', $discount['applied_to']);
    }

    /**
     * Get GraphQL query for customer orders with coupon information
     *
     * @return string
     */
    private function getCustomerOrdersWithCouponQuery(): string
    {
        return <<<QUERY
query {
    customer {
        firstname
        lastname
        orders {
            items {
                order_number
                status
                order_date
                items {
                    product_sku
                    product_name
                    quantity_ordered
                    discounts {
                        applied_to
                    }
                }
            }
            page_info {
                current_page
                page_size
                total_pages
            }
            total_count
        }
    }
}
QUERY;
    }

    /**
     * Generate customer token mutation
     *
     * @param string $email
     * @param string $password
     * @return string
     */
    private function generateCustomerTokenMutation(string $email, string $password): string
    {
        return <<<MUTATION
mutation {
    generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;
    }

    /**
     * Get customer authorization headers
     *
     * @param string $token
     * @return array
     */
    private function getCustomerHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer ' . $token,
            'Store' => 'default'
        ];
    }
}
