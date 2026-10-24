<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftMessage;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AuthenticationException;
use Magento\GiftMessage\Test\Fixture\GiftMessage;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class RemoveGiftMessageFromCartTest extends GraphQlAbstract
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

    #[
        Config('sales/gift_options/allow_order', true),
        Config('sales/gift_options/allow_items', true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(
            ProductFixture::class,
            ['type_id' => 'simple', 'weight' => 10, 'gift_message_available' => 2],
            as: 'product'
        ),
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(
            CustomerCartFixture::class,
            [
                'customer_id' => '$customer.id$'
            ],
            as: 'quote'
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testCartQueryWithGiftMessageAfterRemovingCartItem(): void
    {
        $this->setGiftMessageOnCart();

        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        // Remove item from cart
        $this->graphQlMutation(
            $this->removeItemFromCartMutation($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        self::assertEquals(
            [
                'cart' => [
                    'gift_message' => null
                ]
            ],
            $this->graphQlQuery(
                $this->getCartGraphQlQuery($maskedQuoteId),
                [],
                '',
                $customerAuthHeaders
            )
        );
    }

    #[
        Config('sales/gift_options/allow_order', true),
        Config('sales/gift_options/allow_items', true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(
            ProductFixture::class,
            ['type_id' => 'simple', 'weight' => 10, 'gift_message_available' => 2],
            as: 'product'
        ),
        DataFixture(
            ProductFixture::class,
            ['type_id' => 'simple', 'weight' => 10, 'gift_message_available' => 2],
            as: 'product2'
        ),
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(
            CustomerCartFixture::class,
            [
                'customer_id' => '$customer.id$'
            ],
            as: 'quote'
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product2.id$'
            ]
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testGiftMessagePersistenceAfterRemovingOneCartItem(): void
    {
        $this->setGiftMessageOnCart();
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        // Remove item from cart
        $this->graphQlMutation(
            $this->removeItemFromCartMutation($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        self::assertEquals(
            [
                'cart' => [
                    'gift_message' => [
                        'from' => 'Romeo',
                        'to' => 'Mercutio',
                        'message' => 'Fixture Test message.'
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCartGraphQlQuery($maskedQuoteId),
                [],
                '',
                $customerAuthHeaders
            )
        );
    }

    #[
        Config('sales/gift_options/allow_order', true),
        Config('sales/gift_options/allow_items', true),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(
            ProductFixture::class,
            ['type_id' => 'simple', 'weight' => 10, 'gift_message_available' => 2],
            as: 'product'
        ),
        DataFixture(GiftMessage::class, as: 'message'),
        DataFixture(
            CustomerCartFixture::class,
            [
                'customer_id' => '$customer.id$'
            ],
            as: 'quote'
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$product.id$'
            ]
        ),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testCartQueryWithGiftMessageAfterClearCart(): void
    {
        $this->setGiftMessageOnCart();
        // Clear cart
        $this->clearCart();

        self::assertEquals(
            [
                'cart' => [
                    'gift_message' => null
                ]
            ],
            $this->graphQlQuery(
                $this->getCartGraphQlQuery($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Get cart query with gift message
     *
     * @param string $cartId
     * @return string
     */
    private function getCartGraphQlQuery(string $cartId): string
    {
        return <<<QUERY
            {
                cart(cart_id: "{$cartId}") {
                    gift_message {
                        from
                        to
                        message
                    }
                }
            }
        QUERY;
    }

    /**
     * Remove item from cart mutation
     *
     * @param string $cartId
     * @return string
     */
    private function removeItemFromCartMutation(string $cartId): string
    {
        $itemId = $this->getItemId(
            (int)$this->fixtures->get('quote')->getId(),
            (int)$this->fixtures->get('product')->getId()
        );

        return <<<MUTATION
            mutation removeItemFromCart {
                removeItemFromCart(
                    input: {
                        cart_id: "{$cartId}"
                        cart_item_id: "{$itemId}"
                    }
                ) {
                    cart {
                        id
                    }
                }
            }
        MUTATION;
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
     * Set gift message on cart
     *
     * @return void
     */
    public function setGiftMessageOnCart(): void
    {
        $quote = $this->fixtures->get('quote');
        $quote->setGiftMessageId($this->fixtures->get('message')->getId());
        $quote->save();
    }

    /**
     * Remove all items form cart
     *
     * @return void
     */
    public function clearCart(): void
    {
        $quote = $this->fixtures->get('quote');
        $quote->removeAllItems();
        $quote->save();
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
