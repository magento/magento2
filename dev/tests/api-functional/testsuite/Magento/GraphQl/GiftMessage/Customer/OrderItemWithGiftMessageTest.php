<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftMessage\Customer;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AuthenticationException;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class OrderItemWithGiftMessageTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(
            CustomerCartFixture::class,
            [
                'customer_id' => '$customer.id$',
                'message_id' => '$message.id$',
            ],
            as: 'cart'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        Config('sales/gift_options/allow_order', 1),
        Config('sales/gift_options/allow_items', 1),
    ]
    public function testOrderWithGiftMessage(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        /** @var MessageInterface $message */
        $message = $this->fixtures->get('message');
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        $this->graphQlMutation(
            $this->updateGiftMessageMutation(
                [
                    'message_from' => $message->getSender(),
                    'message_to' => $message->getRecipient(),
                    'message' => $message->getMessage()
                ],
                $maskedQuoteId,
                $this->getItemId(
                    (int)$this->fixtures->get('cart')->getId(),
                    (int)$this->fixtures->get('product')->getId()
                )
            ),
            [],
            '',
            $customerAuthHeaders
        );

        $orderResponse = $this->graphQlMutation(
            $this->getPlaceOrderMutation($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        self::assertEquals(
            [
                'customer' => [
                    'orders' => [
                        'items' => [
                            [
                                'items' => [
                                    [
                                        'gift_message' => [
                                            'from' => $message->getSender(),
                                            'to' => $message->getRecipient(),
                                            'message' => $message->getMessage()
                                        ]
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
                $customerAuthHeaders
            )
        );

        //Revert order as it is created through mutations and not fixtures
        $this->revertOrder($orderResponse['placeOrder']['orderV2']['number']);
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        Config('sales/gift_options/allow_order', 1),
        Config('sales/gift_options/allow_items', 1),
    ]
    public function testOrderWithoutGiftMessage(): void
    {
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        $orderResponse = $this->graphQlMutation(
            $this->getPlaceOrderMutation($this->fixtures->get('quoteIdMask')->getMaskedId()),
            [],
            '',
            $customerAuthHeaders
        );

        self::assertEquals(
            [
                'customer' => [
                    'orders' => [
                        'items' => [
                            [
                                'items' => [
                                    [
                                        'gift_message' => null
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
                $customerAuthHeaders
            )
        );

        //Revert order as it is created through mutations and not fixtures
        $this->revertOrder($orderResponse['placeOrder']['orderV2']['number']);
    }

    /**
     * Get item id from quote_id and product_id
     *
     * @param int $cartId
     * @param int $productId
     * @return int
     */
    private function getItemId(int $cartId, int $productId): int
    {
        $connection = $this->resourceConnection->getConnection();

        return (int)$connection->fetchOne(
            $connection->select()
                ->from($this->resourceConnection->getTableName('quote_item'))
                ->reset('columns')
                ->columns('item_id')
                ->where('quote_id = ?', $cartId)
                ->where('product_id = ?', $productId)
        );
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
        return [
            'Authorization' => 'Bearer ' . $this->customerTokenService->createCustomerAccessToken($email, 'password')
        ];
    }

    /**
     * Get update gift message mutation
     *
     * @param array $giftData
     * @param string $maskedQuoteId
     * @param int $itemId
     * @return string
     */
    private function updateGiftMessageMutation(array $giftData, string $maskedQuoteId, int $itemId): string
    {
        return <<<MUTATION
            mutation {
              updateCartItems(
                input: {
                  cart_id: "$maskedQuoteId",
                  cart_items: [
                    {
                     cart_item_id: $itemId
                      quantity: 1
                     gift_message: {
                        from: "{$giftData['message_from']}"
                        to: "{$giftData['message_to']}"
                        message: "{$giftData['message']}"
                      }
                    }
                  ]
                }
              ) {
                cart {
                  items {
                    id
                      ... on SimpleCartItem {
                      gift_message {
                        from
                        to
                        message
                      }
                    }
                 }
                }
              }
            }
        MUTATION;
    }

    /**
     * Get guest order by token query
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
            query {
              customer {
                orders {
                  items {
                    items {
                        gift_message {
                            from
                            to
                            message
                        }
                    }
                  }
                }
              }
            }
        QUERY;
    }

    /**
     * Get place order mutation with orderV2 data
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getPlaceOrderMutation(string $maskedQuoteId): string
    {
        return <<<MUTATION
            mutation {
              placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
                orderV2 {
                    token
                    number
                }
              }
            }
        MUTATION;
    }

    /**
     * Delete Orders from sales_order and sales_order_grid table
     *
     * @param string $orderNumber
     * @return void
     */
    private function revertOrder(string $orderNumber): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->delete(
            $this->resourceConnection->getTableName('sales_order'),
            ['increment_id = ?' => $orderNumber]
        );
        $connection->delete(
            $this->resourceConnection->getTableName('sales_order_grid'),
            ['increment_id = ?' => $orderNumber]
        );
    }
}
