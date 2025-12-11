<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftMessage;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Downloadable\Test\Fixture\DownloadableProduct as DownloadableProductFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting gift message with cart item query
 */
class CartItemWithGiftMessageTest extends GraphQlAbstract
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
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $this->quoteIdToMaskedQuoteId= Bootstrap::getObjectManager()->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    #[
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(ProductFixture::class, ['type_id' => 'virtual'], as: 'product'),
        DataFixture(GuestCart::class, ['message_id' => '$message.id$'], as: 'quote'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        Config('sales/gift_options/allow_order', 1),
        Config('sales/gift_options/allow_items', 1)
    ]
    public function testCartQueryWithVirtualItem(): void
    {
        $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int)$this->fixtures->get('quote')->getId());
        $this->updateGiftMessageForCartItems($maskedQuoteId);

        self::assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            '0' => [
                                'product' => [
                                    'gift_message_available' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery($this->getCartItemsGraphQlQuery($maskedQuoteId))
        );
    }

    #[
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(DownloadableProductFixture::class, [
            'price' => 100,
            'type_id' => 'downloadable',
            'links_purchased_separately' => 0,
            'downloadable_product_links' => [
                [
                    'title' => 'Example 1',
                    'price' => 0.00,
                    'link_type' => 'url'
                ],
                [
                    'title' => 'Example 2',
                    'price' => 0.00,
                    'link_type' => 'url'
                ]
            ]
        ], as: 'product'),
        DataFixture(GuestCart::class, ['message_id' => '$message.id$'], as: 'quote'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        Config('sales/gift_options/allow_order', 1),
        Config('sales/gift_options/allow_items', 1)
    ]
    public function testCartQueryWithDownloadableItem(): void
    {
        $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int)$this->fixtures->get('quote')->getId());
        $this->updateGiftMessageForCartItems($maskedQuoteId);

        self::assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            '0' => [
                                'product' => [
                                    'gift_message_available' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery($this->getCartItemsGraphQlQuery($maskedQuoteId))
        );
    }

    #[
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(
            ProductFixture::class,
            ['type_id' => 'simple', 'weight' => 10, 'gift_message_available' => 2],
            as: 'product'
        ),
        DataFixture(GuestCart::class, ['message_id' => '$message.id$'], as: 'quote'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        Config('sales/gift_options/allow_order', 1),
        Config('sales/gift_options/allow_items', 1)
    ]
    public function testCartQueryWithSimpleItemWhenStoreConfigEnabled(): void
    {
        $this->updateCartItemWithGiftMessage();
    }

    #[
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(
            ProductFixture::class,
            ['type_id' => 'simple', 'weight' => 10, 'gift_message_available' => 2],
            as: 'product'
        ),
        DataFixture(GuestCart::class, ['message_id' => '$message.id$'], as: 'quote'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        Config('sales/gift_options/allow_order', 1),
        Config('sales/gift_options/allow_items', 0)
    ]
    public function testCartQueryWithSimpleItemWhenStoreConfigDisabled(): void
    {
        $this->updateCartItemWithoutGiftMessage();
    }

    #[
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(
            ProductFixture::class,
            ['type_id' => 'simple', 'weight' => 10, 'gift_message_available' => 0],
            as: 'product'
        ),
        DataFixture(GuestCart::class, ['message_id' => '$message.id$'], as: 'quote'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        Config('sales/gift_options/allow_order', 1),
        Config('sales/gift_options/allow_items', 0)
    ]
    public function testCartQueryWithSimpleItemWhenAllConfigDisabled(): void
    {
        $this->updateCartItemWithoutGiftMessage();
    }

    #[
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(
            ProductFixture::class,
            ['type_id' => 'simple', 'weight' => 10, 'gift_message_available' => 1],
            as: 'product'
        ),
        DataFixture(GuestCart::class, ['message_id' => '$message.id$'], as: 'quote'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        Config('sales/gift_options/allow_order', 1),
        Config('sales/gift_options/allow_items', 0)
    ]
    public function testCartQueryWithSimpleItemWhenProductConfigEnabled(): void
    {
        $this->updateCartItemWithGiftMessage();
    }

    /**
     * Get cart items query with gift message
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartItemsGraphQlQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
        {
          cart(cart_id: "$maskedQuoteId") {
            itemsV2 {
              items {
                product {
                  gift_message_available
                }
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
        QUERY;
    }

    /**
     * Update gift message mutation
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
                      gift_message: {
                        to: "{$giftData['message_to']}"
                        from: "{$giftData['message_from']}"
                        message: "{$giftData['message']}"
                      }
                      quantity: 2
                    }
                  ]
                }
              ) {
                cart {
                  items {
                    id
                  }
                }
              }
            }
        MUTATION;
    }

    /**
     * Update gift message for cart items
     *
     * @param string $maskedQuoteId
     * @return void
     * @throws Exception
     */
    private function updateGiftMessageForCartItems(string $maskedQuoteId): void
    {
        $giftMessage = $this->fixtures->get('message');

        $this->graphQlMutation(
            $this->updateGiftMessageMutation(
                [
                    'message_to' => $giftMessage->getRecipient(),
                    'message_from' => $giftMessage->getSender(),
                    'message' => $giftMessage->getMessage(),
                ],
                $maskedQuoteId,
                $this->getItemId(
                    (int)$this->fixtures->get('quote')->getId(),
                    (int)$this->fixtures->get('product')->getId()
                )
            )
        );
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
     * Update Cart Item with gift message - for simple and physical gift card
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function updateCartItemWithGiftMessage(): void
    {
        $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int)$this->fixtures->get('quote')->getId());
        $this->updateGiftMessageForCartItems($maskedQuoteId);

        self::assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            '0' => [
                                'product' => [
                                    'gift_message_available' => true
                                ],
                                'gift_message' => [
                                    'from' => 'Romeo',
                                    'to' => 'Mercutio',
                                    'message' => 'Fixture Test message.'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery($this->getCartItemsGraphQlQuery($maskedQuoteId))
        );
    }

    /**
     * Update Cart Item without gift message
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function updateCartItemWithoutGiftMessage(): void
    {
        $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int)$this->fixtures->get('quote')->getId());
        $this->updateGiftMessageForCartItems($maskedQuoteId);

        self::assertEquals(
            [
                'cart' => [
                    'itemsV2' => [
                        'items' => [
                            '0' => [
                                'product' => [
                                    'gift_message_available' => false
                                ],
                                'gift_message' => null
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery($this->getCartItemsGraphQlQuery($maskedQuoteId))
        );
    }
}
