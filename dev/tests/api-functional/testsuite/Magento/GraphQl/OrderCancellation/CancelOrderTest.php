<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\OrderCancellation;

use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Test\Fixture\Creditmemo as CreditmemoFixture;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\Store\Test\Fixture\Store;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixture;
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
    DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice')
]
class CancelOrderTest extends GraphQlAbstract
{
    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->idEncoder = Bootstrap::getObjectManager()->get(Uid::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToCancelOrderWhenMissingReason()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage("Field CancelOrderInput.reason of required type String! was not provided.");

        $this->graphQlMutation(
            <<<MUTATION
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
MUTATION,
            [],
            '',
            $this->getCustomerAuthHeaders()
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
        $this->assertEquals(
            [
                'cancelOrder' => [
                    'errorV2' => [
                        'message' => 'Order cancellation is not enabled for requested store.'
                    ],
                    'order' => null
                ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2(
                    $this->idEncoder->encode((string)$this->fixtures->get('order')->getEntityId()),
                    "Other"
                ),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
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
    public function testAttemptToCancelOrderWhenMissingOrderId()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage("Field CancelOrderInput.order_id of required type ID! was not provided.");

        $this->graphQlMutation(
            <<<MUTATION
        mutation {
            cancelOrder(
              input: {
                reason: "Other"
              }
            ){
                error
                order {
                    status
                }
            }
          }
MUTATION,
            [],
            '',
            $this->getCustomerAuthHeaders()
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
    public function testAttemptToCancelNonExistingOrder()
    {
        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'error' => "The entity that was requested doesn't exist. Verify the entity and try again.",
                        'order' => null
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutation("MTAwMDA="),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
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
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToCancelOrderFromAnotherCustomer()
    {
        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'error' => "Current user is not authorized to cancel this order",
                        'order' => null
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutation(
                    $this->idEncoder->encode((string)$this->fixtures->get('order')->getEntityId())
                ),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
        );
    }

    /**
     * @dataProvider orderStatusProvider
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToCancelOrderWithSomeStatuses(string $status, string $expectedStatus)
    {
        $order = $this->fixtures->get('order');

        $order->setStatus($status);
        $order->setState($status);

        /** @var OrderRepositoryInterface $orderRepo */
        $orderRepo = Bootstrap::getObjectManager()->get(OrderRepository::class);
        $orderRepo->save($order);

        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order already closed, complete, cancelled or on hold'
                        ],
                        'order' => [
                            'status' => $expectedStatus
                        ]
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2($this->idEncoder->encode((string)$order->getEntityId())),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
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
        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order already closed, complete, cancelled or on hold'
                        ],
                        'order' => [
                            'status' => 'Complete'
                        ]
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2(
                    $this->idEncoder->encode((string)$this->fixtures->get('order')->getEntityId())
                ),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
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
        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order with one or more items shipped cannot be cancelled'
                        ],
                        'order' => [
                            'status' => 'Processing'
                        ]
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2(
                    $this->idEncoder->encode((string)$this->fixtures->get('order')->getEntityId())
                ),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
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
        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order already closed, complete, cancelled or on hold'
                        ],
                        'order' => [
                            'status' => 'Closed'
                        ]
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2(
                    $this->idEncoder->encode((string)$this->fixtures->get('order')->getEntityId())
                ),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
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
        $order = $this->fixtures->get('order');

        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => null,
                        'order' => [
                            'status' => 'Canceled'
                        ]
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2($this->idEncoder->encode((string)$order->getEntityId())),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
        );

        $comments = $order->getStatusHistories();

        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation notification email was sent.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals('Other', $comment->getComment());
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
        $order = $this->fixtures->get('order');

        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => null,
                        'order' => [
                            'status' => 'Closed'
                        ]
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2($this->idEncoder->encode((string)$order->getEntityId())),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
        );

        $comments = $order->getStatusHistories();

        $comment = array_pop($comments);
        $this->assertEquals("We refunded $15.00 offline.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation notification email was sent.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals('Other', $comment->getComment());
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
        $order = $this->fixtures->get('order');

        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => null,
                        'order' => [
                            'status' => 'Closed'
                        ]
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2($this->idEncoder->encode((string)$order->getEntityId())),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
        );

        $comments = $order->getAllStatusHistory();

        $comment = array_pop($comments);
        $this->assertEquals("We refunded $25.00 offline.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals("We refunded $20.00 offline.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation notification email was sent.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals('Other', $comment->getComment());
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
        $order = $this->fixtures->get('order');

        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order cancellation reason is invalid.'
                        ],
                        'order' => null
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2(
                    $this->idEncoder->encode((string)$order->getEntityId()),
                    "<script>while(true){alert(666);}</script>"
                ),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
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
        $order = $this->fixtures->get('order');

        $this->assertEquals(
            [
                'cancelOrder' =>
                    [
                        'errorV2' => null,
                        'order' => [
                            'status' => 'Canceled'
                        ]
                    ]
            ],
            $this->graphQlMutation(
                $this->getCancelOrderMutationWithErrorV2($this->idEncoder->encode((string)$order->getEntityId())),
                [],
                '',
                $this->getCustomerAuthHeaders()
            )
        );

        $comments = $order->getStatusHistories();

        $comment = array_pop($comments);
        $this->assertEquals("We refunded $20.00 offline.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation notification email was sent.", $comment->getComment());

        $comment = array_pop($comments);
        $this->assertEquals('Other', $comment->getComment());
        $this->assertEquals('canceled', $comment->getStatus());
    }

    /**
     * Get cancel order mutation
     *
     * @param string $orderId
     * @return string
     */
    private function getCancelOrderMutation(string $orderId): string
    {
        return <<<MUTATION
        mutation {
            cancelOrder(
              input: {
                order_id: "{$orderId}"
                reason: "Other"
              }
            ){
                error
                order {
                    status
                }
            }
        }
MUTATION;
    }

    /**
     * Get cancel order mutation with errorV2
     *
     * @param string $orderId
     * @param string $reason
     * @return string
     */
    private function getCancelOrderMutationWithErrorV2(string $orderId, string $reason = "Other"): string
    {
        return <<<MUTATION
        mutation {
            cancelOrder(
              input: {
                order_id: "{$orderId}"
                reason: "{$reason}"
              }
            ){
                errorV2 {
                    message
                }
                order {
                    status
                }
            }
        }
MUTATION;
    }

    /**
     * Get customer auth headers
     *
     * @return string[]
     * @throws AuthenticationException|LocalizedException
     */
    private function getCustomerAuthHeaders(): array
    {
        return Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class)
            ->execute($this->fixtures->get('customer')->getEmail());
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
