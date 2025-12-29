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
class CustomerOrdersCouponTest extends GraphQlAbstract
{
    private const COUPON_CODE = 'TEST-COUPON-2025';
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
     * Test that customer orders GraphQL query returns coupon code correctly for order items with discounts
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
    public function testGetCustomerOrdersWithCouponCode()
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

        // Validate discounts array exists
        $this->assertArrayHasKey('discounts', $orderItem);
        $this->assertNotEmpty($orderItem['discounts']);

        $discount = $orderItem['discounts'][0];

        // Validate discount structure and values
        $this->assertArrayHasKey('amount', $discount);
        $this->assertArrayHasKey('value', $discount['amount']);
        $this->assertEquals(10, $discount['amount']['value']);

        // The key test: validate coupon code is returned without error
        $this->assertArrayHasKey('coupon', $discount);
        $this->assertArrayHasKey('code', $discount['coupon']);
        $this->assertEquals(self::COUPON_CODE, $discount['coupon']['code']);

        // Validate order status and other basic properties
        $this->assertArrayHasKey('order_number', $order);
        $this->assertArrayHasKey('status', $order);
    }

    /**
     * Test that the fix handles orders without coupons gracefully
     *
     * @throws Exception
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'price' => 50.00,
                'sku' => 'test-product-no-coupon'
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
    public function testGetCustomerOrdersWithoutCoupon()
    {
        $customer = $this->fixtures->get('customer');
        $currentEmail = $customer->getEmail();
        $currentPassword = 'password';

        // Generate customer token
        $generateToken = $this->generateCustomerTokenMutation($currentEmail, $currentPassword);
        $tokenResponse = $this->graphQlMutation($generateToken);
        $customerToken = $tokenResponse['generateCustomerToken']['token'];

        // Query customer orders
        $query = $this->getCustomerOrdersWithCouponQuery();
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerHeaders($customerToken)
        );

        // Validate that orders without coupons don't cause errors
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);

        $order = $response['customer']['orders']['items'][0];
        $this->assertArrayHasKey('items', $order);
        $this->assertNotEmpty($order['items']);

        $orderItem = $order['items'][0];
        $this->assertEquals('test-product-no-coupon', $orderItem['product_sku']);

        // Orders without discounts should have empty discounts array
        $this->assertArrayHasKey('discounts', $orderItem);
        // Discounts array may be empty for orders without coupons
        if (!empty($orderItem['discounts'])) {
            // If there are discounts, they should not have coupon codes
            foreach ($orderItem['discounts'] as $discount) {
                $this->assertArrayHasKey('coupon', $discount);
                $this->assertNull($discount['coupon']);
            }
        }
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
                        coupon {
                            code
                        }
                        amount {
                            value
                            currency
                        }
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
