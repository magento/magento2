<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\OrderCancellation;

use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Test\Fixture\Creditmemo as CreditmemoFixture;
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
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
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
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
    Config('sales/cancellation/enabled', 1)
]
class CancelOrderTest extends GraphQlAbstract
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->get(CartManagementInterface::class);
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testAttemptToCancelOrderWhenMissingReason()
    {
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: 9999999
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage("Field CancelOrderInput.reason of required type String! was not provided.");

        $this->graphQlMutation(
            $query,
            [],
            '',
            $customerToken
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        Config('sales/cancellation/enabled', 0)
    ]
    public function testAttemptToCancelOrderWhenCancellationFeatureDisabled()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');

        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: {$order->getEntityId()},
                reason: "Sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

        $this->assertEquals(
            [
                'cancelOrder' => [
                    'error' => 'Order cancellation is not enabled for requested store.',
                    'order' => null
                ]
            ],
            $this->graphQlMutation(
                $query,
                [],
                '',
                $customerToken
            )
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testAttemptToCancelOrderWhenMissingOrderId()
    {
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage("Field CancelOrderInput.order_id of required type ID! was not provided.");

        $this->graphQlMutation(
            $query,
            [],
            '',
            $customerToken
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    public function testAttemptToCancelNonExistingOrder()
    {
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: 99999999,
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => "The entity that was requested doesn't exist. Verify the entity and try again.",
                        'order' => null
                    ]
            ],
            $response
        );
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
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
                'email' => 'another@example.com',
                'password' => 'pa55w0rd'
            ],
            'another'
        ),
        DataFixture(CustomerCart::class, ['customer_id' => '$another.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice')
    ]
    public function testAttemptToCancelOrderFromAnotherCustomer()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');

        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Sample reason"
              }
            ){
              error
              order {
                status
              }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => "Current user is not authorized to cancel this order",
                        'order' => null
                    ]
            ],
            $response
        );
    }

    /**
     * @dataProvider orderStatusProvider
     */
    public function testAttemptToCancelOrderWithSomeStatuses(string $status, string $expectedStatus)
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $order->setStatus($status);
        $order->setState($status);

        /** @var OrderRepositoryInterface $orderRepo */
        $orderRepo = $this->objectManager->get(OrderRepository::class);
        $orderRepo->save($order);

        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => 'Order already closed, complete, cancelled or on hold',
                        'order' => [
                            'status' => $expectedStatus
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
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$']),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToCancelOrderWithOfflinePaymentFullyInvoicedFullyShipped()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => 'Order already closed, complete, cancelled or on hold',
                        'order' => [
                            'status' => 'Complete'
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
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => 'Order with one or more items shipped cannot be cancelled',
                        'order' => [
                            'status' => 'Processing'
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
                'product_id' => '$product.id$'
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(CreditmemoFixture::class, ['order_id' => '$order.id$'], 'creditmemo'),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToCancelOrderWithOfflinePaymentFullyInvoicedFullyRefunded()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => 'Order already closed, complete, cancelled or on hold',
                        'order' => [
                            'status' => 'Closed'
                        ]
                    ]
            ],
            $response
        );
    }

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
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testCancelOrderWithOutAnyAmountPaid()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => null,
                        'order' => [
                            'status' => 'Canceled'
                        ]
                    ]
            ],
            $response
        );

        $comments = $order->getStatusHistories();

        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation notification email was sent.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals('Cancel sample reason', $comment->getComment());
        $this->assertEquals('canceled', $comment->getStatus());
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testCancelOrderWithOfflinePaymentFullyInvoiced()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => null,
                        'order' => [
                            'status' => 'Closed'
                        ]
                    ]
            ],
            $response
        );

        $comments = $order->getStatusHistories();

        $comment = array_pop($comments);
        $this->assertEquals("We refunded $15.00 offline.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation notification email was sent.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals('Cancel sample reason', $comment->getComment());
        $this->assertEquals('closed', $comment->getStatus());
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
            CreditmemoFixture::class,
            [
                'order_id' => '$order.id$',
                'items' => [['qty' => 1, 'product_id' => '$product.id$']]
            ],
            'creditmemo'
        ),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testCancelOrderWithOfflinePaymentFullyInvoicedPartiallyRefunded()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => null,
                        'order' => [
                            'status' => 'Closed'
                        ]
                    ]
            ],
            $response
        );

        $comments = $order->getAllStatusHistory();

        $comment = array_pop($comments);
        $this->assertEquals("We refunded $25.00 offline.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals("We refunded $20.00 offline.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation notification email was sent.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals('Cancel sample reason', $comment->getComment());
        $this->assertEquals('closed', $comment->getStatus());
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testCancelOrderAttemptingXSSPassedThroughReasonField()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "<script>while(true){alert(666);}</script>"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => null,
                        'order' => [
                            'status' => 'Closed'
                        ]
                    ]
            ],
            $response
        );

        $comments = $order->getStatusHistories();
        $comment = reset($comments);
        $this->assertEquals('&lt;script&gt;while(true){alert(666);}&lt;/script&gt;', $comment->getComment());
        $this->assertEquals('closed', $comment->getStatus());
    }

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
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product1.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product2.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(
            InvoiceFixture::class,
            [
                'order_id' => '$order.id$',
                'items' => ['$product1.sku$']
            ],
            'invoice'
        ),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testCancelPartiallyInvoicedOrder()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = <<<QUERY
        mutation {
            cancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                reason: "Cancel sample reason"
              }
            ){
                error
                order {
                    status
                }
            }
          }
QUERY;
        $customerToken = $this->getHeaders();

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
                        'error' => null,
                        'order' => [
                            'status' => 'Canceled'
                        ]
                    ]
            ],
            $response
        );

        $comments = $order->getStatusHistories();

        $comment = array_pop($comments);
        $this->assertEquals("We refunded $20.00 offline.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation notification email was sent.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals('Cancel sample reason', $comment->getComment());
        $this->assertEquals('canceled', $comment->getStatus());
    }

    /**
     * @return string[]
     * @throws AuthenticationException|LocalizedException
     */
    private function getHeaders(): array
    {
        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        return Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($customer->getEmail());
    }

    /**
     * @return array[]
     */
    public function orderStatusProvider(): array
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
