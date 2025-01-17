<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\OrderCancellation;

use Exception;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\OrderCancellation\Model\GetConfirmationKey;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\GuestCart;
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
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;

/**
 * Test coverage for cancel order mutation for guest order
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    DataFixture(Store::class),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(GuestCart::class, as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
    Config('sales/cancellation/enabled', 1)
]
class ConfirmCancelGuestOrderTest extends GraphQlAbstract
{
    /**
     * @var GetConfirmationKey
     */
    private $confirmationKey;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->confirmationKey = Bootstrap::getObjectManager()->get(GetConfirmationKey::class);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAttemptToConfirmCancelOrderWhenMissingOrderId()
    {
        $query = <<<MUTATION
        mutation {
            confirmCancelOrder(
              input: {
                confirmation_key: "4f8d1e2a6c7e5b4f9a2d3e0f1c5a747d"
              }
            ){
                error
                order {
                    status
                }
            }
          }
MUTATION;
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage("Field ConfirmCancelOrderInput.order_id of required type ID! was not provided.");
        $this->graphQlMutation($query);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAttemptToConfirmCancelNonExistingOrder()
    {
        $query = <<<MUTATION
        mutation {
            confirmCancelOrder(
              input: {
                order_id: 99999999,
                confirmation_key: "4f8d1e2a6c7e5b4f9a2d3e0f1c5a747d"
              }
            ){
                error
                order {
                    status
                }
            }
          }
MUTATION;

        $this->assertEquals(
            [
                'confirmCancelOrder' => [
                    'error' => 'The entity that was requested doesn\'t exist. Verify the entity and try again.',
                    'order' => null
                ]
            ],
            $this->graphQlMutation($query)
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAttemptToConfirmCancelOrderWhenMissingKey()
    {
        $query = <<<MUTATION
        mutation {
            confirmCancelOrder(
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
MUTATION;
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(
            "Field ConfirmCancelOrderInput.confirmation_key of required type String! was not provided."
        );
        $this->graphQlMutation($query);
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        Config('sales/cancellation/enabled', 0)
    ]
    public function testAttemptToConfirmCancelOrderWhenCancellationFeatureDisabled()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');

        $query = $this->getConfirmCancelOrderMutation($order);
        $this->assertEquals(
            [
                'confirmCancelOrder' => [
                    'errorV2' => [
                        'message' => 'Order cancellation is not enabled for requested store.',
                    ],
                    'order' => null
                ]
            ],
            $this->graphQlMutation($query)
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(Store::class),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$']),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToConfirmCancelOrderWithOfflinePaymentFullyInvoicedFullyShipped()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getConfirmCancelOrderMutation($order);
        $this->assertEquals(
            [
                'confirmCancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order already closed, complete, cancelled or on hold'
                        ],
                        'order' => [
                            'status' => 'Complete'
                        ]
                    ]
            ],
            $this->graphQlMutation($query)
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(Store::class),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
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
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
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
    public function testAttemptToConfirmCancelOrderWithOfflinePaymentFullyInvoicedPartiallyShipped()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getConfirmCancelOrderMutation($order);
        $this->assertEquals(
            [
                'confirmCancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order with one or more items shipped cannot be cancelled'
                        ],
                        'order' => [
                            'status' => 'Processing'
                        ]
                    ]
            ],
            $this->graphQlMutation($query)
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     */
    #[
        DataFixture(Store::class),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(CreditmemoFixture::class, ['order_id' => '$order.id$'], 'creditmemo'),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToConfirmCancelOrderWithOfflinePaymentFullyInvoicedFullyRefunded()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getConfirmCancelOrderMutation($order);
        $this->assertEquals(
            [
                'confirmCancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order already closed, complete, cancelled or on hold'
                        ],
                        'order' => [
                            'status' => 'Closed'
                        ]
                    ]
            ],
            $this->graphQlMutation($query)
        );
    }

    #[
        DataFixture(Store::class),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToConfirmCancelOrderForWhichConfirmationKeyNotGenerated()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getConfirmCancelOrderMutation($order);
        $this->assertEquals(
            [
                'confirmCancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'The order cancellation could not be confirmed.'
                        ],
                        'order' => null
                    ]
            ],
            $this->graphQlMutation($query)
        );
    }

    #[
        DataFixture(Store::class),
        DataFixture(Customer::class, as: 'customer'),
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
    public function testAttemptToConfirmCancelCustomerOrder()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getConfirmCancelOrderMutation($order);
        $this->assertEquals(
            [
                'confirmCancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Current user is not authorized to cancel this order'
                        ],
                        'order' => null
                    ]
            ],
            $this->graphQlMutation($query)
        );
    }

    #[
        DataFixture(Store::class),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToConfirmCancelOrderWithInvalidConfirmationKey()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $this->confirmationKey->execute($order, 'Simple reason');

        $query = $this->getConfirmCancelOrderMutation($order);
        $this->assertEquals(
            [
                'confirmCancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'The order cancellation could not be confirmed.'
                        ],
                        'order' => null
                    ]
            ],
            $this->graphQlMutation($query)
        );
    }

    #[
        DataFixture(Store::class),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testConfirmCancelOrderWithOutAnyAmountPaid()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $confirmationKey = $this->confirmationKey->execute($order, 'Cancel sample reason');

        $query = <<<MUTATION
        mutation {
            confirmCancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                confirmation_key: "{$confirmationKey}"
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
        $this->assertEquals(
            [
                'confirmCancelOrder' =>
                    [
                        'errorV2' => null,
                        'order' => [
                            'status' => 'Canceled'
                        ]
                    ]
            ],
            $this->graphQlMutation($query)
        );
    }

    /**
     * @param string $status
     * @param string $expectedStatus
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     *
     * @dataProvider orderStatusProvider
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToConfirmCancelOrderWithSomeStatuses(string $status, string $expectedStatus)
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $order->setStatus($status);
        $order->setState($status);

        /** @var OrderRepositoryInterface $orderRepo */
        $orderRepo = Bootstrap::getObjectManager()->get(OrderRepository::class);
        $orderRepo->save($order);

        $query = $this->getConfirmCancelOrderMutation($order);
        $this->assertEquals(
            [
                'confirmCancelOrder' =>
                    [
                        'errorV2' => [
                            'message' => 'Order already closed, complete, cancelled or on hold',
                        ],
                        'order' => [
                            'status' => $expectedStatus
                        ]
                    ]
            ],
            $this->graphQlMutation($query)
        );
    }

    /**
     * Get confirm cancel order mutation
     *
     * @param OrderInterface $order
     * @return string
     */
    private function getConfirmCancelOrderMutation(OrderInterface $order): string
    {
        return  <<<MUTATION
        mutation {
            confirmCancelOrder(
              input: {
                order_id: "{$order->getEntityId()}"
                confirmation_key: "4f8d1e2a6c7e5b4f9a2d3e0f1c5a747d"
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
