<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\GraphQl\OrderCancellation;

use Exception;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Customer\Test\Fixture\Customer;
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
use Magento\SalesGraphQl\Model\Order\Token;

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
class CancelGuestOrderTest extends GraphQlAbstract
{
    /**
     * @return void
     * @throws Exception
     */
    public function testAttemptToCancelOrderWhenMissingToken()
    {
        $query = <<<MUTATION
        mutation {
            requestGuestOrderCancel(
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
MUTATION;
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('Field GuestOrderCancelInput.token of required type String! was not provided.');
        $this->graphQlMutation($query);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAttemptToCancelOrderWhenMissingReason()
    {
        $query = <<<MUTATION
        mutation {
            requestGuestOrderCancel(
              input: {
                token: "TestToken"
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
        $this->expectExceptionMessage("Field GuestOrderCancelInput.reason of required type String! was not provided.");
        $this->graphQlMutation($query);
    }
    /**
     * @return void
     * @throws Exception
     */
    public function testAttemptToCancelOrderWithInvalidToken()
    {
        $query = <<<MUTATION
        mutation {
            requestGuestOrderCancel(
              input: {
                token: "TestToken"
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
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('We couldn\'t locate an order with the information provided.');
        $this->graphQlMutation($query);
    }

    /**
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
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
        $query = $this->getMutation($order);

        $this->assertEquals(
            [
                'requestGuestOrderCancel' => [
                    'errorV2' => [
                        'message' => 'Order cancellation is not enabled for requested store.'
                    ],
                    'order' => null
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
     * @throws Exception
     *
     * @dataProvider orderStatusProvider
     */
    #[
        Config('sales/cancellation/enabled', 1)
    ]
    public function testAttemptToCancelOrderWithSomeStatuses(string $status, string $expectedStatus)
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $order->setStatus($status);
        $order->setState($status);

        /** @var OrderRepositoryInterface $orderRepo */
        $orderRepo = Bootstrap::getObjectManager()->create(OrderRepository::class);
        $orderRepo->save($order);

        $query = $this->getMutation($order);
        $this->assertEquals(
            [
                'requestGuestOrderCancel' => [
                    'errorV2' => [
                        'message' => 'Order already closed, complete, cancelled or on hold'
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
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
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
    public function testAttemptToCancelOrderWithOfflinePaymentFullyInvoicedFullyShipped()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getMutation($order);
        $this->assertEquals(
            [
                'requestGuestOrderCancel' => [
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
     * @throws Exception
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
    public function testAttemptToCancelOrderWithOfflinePaymentFullyInvoicedPartiallyShipped()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getMutation($order);
        $this->assertEquals(
            [
                'requestGuestOrderCancel' => [
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
     * @throws Exception
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
    public function testAttemptToCancelOrderWithOfflinePaymentFullyInvoicedFullyRefunded()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getMutation($order);
        $this->assertEquals(
            [
                'requestGuestOrderCancel' => [
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

    /**
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
        Config('sales/cancellation/enabled', 1)
    ]
    public function testCancelOrderWithOutAnyAmountPaid()
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $query = $this->getMutation($order);
        $this->assertEquals(
            [
                'requestGuestOrderCancel' => [
                    'errorV2' => null,
                    'order' => [
                        'status' => 'Pending'
                    ]
                ]
            ],
            $this->graphQlMutation($query)
        );

        $comments = $order->getStatusHistories();
        $comment = array_pop($comments);
        $this->assertEquals("Order cancellation confirmation key was sent via email.", $comment->getComment());
    }

    /**
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
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
        Config('sales/cancellation/enabled', 1)
    ]
    public function testOrderCancellationForLoggedInCustomerUsingToken()
    {
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('Please login to view the order.');

        /** @var OrderInterface $order */
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $this->graphQlMutation($this->getMutation($order));
    }

    /**
     * Get request guest order cancellation by token mutation
     *
     * @param OrderInterface $order
     * @return string
     */
    private function getMutation(OrderInterface $order): string
    {
        return <<<MUTATION
        mutation {
            requestGuestOrderCancel(
              input: {
                token: "{$this->getOrderToken($order)}",
                reason: "Other"
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
     * Get token from order
     *
     * @param OrderInterface $order
     * @return string
     */
    private function getOrderToken(OrderInterface $order): string
    {
        return Bootstrap::getObjectManager()->create(Token::class)->encrypt(
            $order->getIncrementId(),
            $order->getBillingAddress()->getEmail(),
            $order->getBillingAddress()->getLastname()
        );
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
