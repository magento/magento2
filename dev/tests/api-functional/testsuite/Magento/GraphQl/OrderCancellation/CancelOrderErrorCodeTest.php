<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\OrderCancellation;

use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\Store\Test\Fixture\Store;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;

/**
 * Test coverage for cancel order mutation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    DataFixture(Store::class),
    DataFixture(
        Customer::class,
        [
            'email' => 'customer@example.com',
            'password' => 'password'
        ],
        'customer'
    ),
    DataFixture(
        Customer::class,
        [
            'email' => 'customer_b@example.com',
            'password' => 'password'
        ],
        'customer_b'
    ),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice')
]
class CancelOrderErrorCodeTest extends GraphQlAbstract
{
    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testCancelNonExistingOrder()
    {
        /**
         * @var CustomerInterface $customer
         */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $nonExistingOrderId = '99999';
        $query = $this->getQuery($nonExistingOrderId);
        $customerToken = $this->getHeaders($customer->getEmail());
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $customerToken
        );
        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'order' => null,
                        'errorV2' => [
                            'code' => 'ORDER_NOT_FOUND',
                            'message' => "The entity that was requested doesn't exist. Verify the entity and try again."
                        ]
                    ]
            ],
            $response
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testCancelOrderUnauthorizedCustomer()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        /**
         * @var CustomerInterface $customer
         */
        $customer = DataFixtureStorageManager::getStorage()->get('customer_b');
        $query = $this->getQuery($order->getEntityId());
        $customerToken = $this->getHeaders($customer->getEmail());
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $customerToken
        );

        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'order' => null,
                        'errorV2' => [
                            'code' => 'UNAUTHORISED',
                            'message' => "Current user is not authorized to cancel this order"
                        ]
                    ]
            ],
            $response
        );
    }

    /**
     * @dataProvider orderStatusProvider
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToCancelOrderWithSomeStatuses(
        string $status,
        string $expectedStatus
    ) {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        /**
         * @var CustomerInterface $customer
         */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $order->setStatus($status);
        $order->setState($status);

        /** @var OrderRepositoryInterface $orderRepo */
        $orderRepo = Bootstrap::getObjectManager()->get(OrderRepository::class);
        $orderRepo->save($order);

        $query = $this->getQuery($order->getEntityId());
        $customerToken = $this->getHeaders($customer->getEmail());

        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $customerToken
        );
        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'order' => [
                            'status' => $expectedStatus
                        ],
                        'errorV2' => [
                            'code' => 'INVALID_ORDER_STATUS',
                            'message' => "Order already closed, complete, cancelled or on hold"
                        ]
                    ]
            ],
            $response
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(Store::class),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 3
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(
            ShipmentFixture::class,
            [
                'order_id' => '$order.id$',
                'items' => [['product_id' => '$product.id$', 'qty' => 1]]
            ]
        ),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToCancelOrderWithOfflinePaymentFullyInvoicedPartiallyShipped()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');

        /**
         * @var CustomerInterface $customer
         */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $query = $this->getQuery($order->getEntityId());
        $customerToken = $this->getHeaders($customer->getEmail());

        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $customerToken
        );
        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'order' => [
                            'status' => 'Processing'
                        ],
                        'errorV2' => [
                            'code' => 'PARTIAL_ORDER_ITEM_SHIPPED',
                            'message' => "Order with one or more items shipped cannot be cancelled"
                        ]
                    ]
            ],
            $response
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(Store::class),
        DataFixture(
            Customer::class,
            [
                'email' => 'customer@example.com',
                'password' => 'password'
            ],
            'customer'
        ),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$product.id$',
                'qty' => 3
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        Config('sales/cancellation/enabled', 0)
    ]
    public function testCancelOrderWithDisabledCancellation()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');

        /**
         * @var CustomerInterface $customer
         */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $query = $this->getQuery($order->getEntityId());
        $customerToken = $this->getHeaders($customer->getEmail());

        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $customerToken
        );

        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'order' => null,
                        'errorV2' => [
                            'code' => 'ORDER_CANCELLATION_DISABLED',
                            'message' => "Order cancellation is not enabled for requested store."
                        ]
                    ]
            ],
            $response
        );
    }

    /**
     * @param string $email
     * @return array
     */
    private function getHeaders(string $email): array
    {
        return Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($email);
    }

    /**
     * @param string $orderId
     * @return string
     */
    private function getQuery(string $orderId): string
    {
        return <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$orderId}"
                reason: "Cancel sample reason"
              }
            ){
                order {
                    status
                }
                errorV2 {
                    code
                    message
               }
            }
          }
QUERY;
    }

    /**
     * @return array[]
     */
    public static function orderStatusProvider(): array
    {
        return [
            'On Hold status' => [
                Order::STATE_HOLDED,
                'On Hold'
            ],
            'Canceled status' => [
                Order::STATE_CANCELED,
                'Canceled'
            ],
            'Closed status' => [
                Order::STATE_CLOSED,
                'Closed'
            ],
            'Complete status' => [
                Order::STATE_COMPLETE,
                'Complete'
            ]
        ];
    }
}
