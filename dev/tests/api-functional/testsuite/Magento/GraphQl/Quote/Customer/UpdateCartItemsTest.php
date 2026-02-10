<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for updating shopping cart items
 */
class UpdateCartItemsTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testUpdateCartItemQuantity()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $itemId = (int)$quote->getItemByProduct($this->productRepository->get('simple'))->getId();
        $quantity = 2;

        $query = $this->getQuery($maskedQuoteId, $itemId, $quantity);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('cart', $response['updateCartItems']);

        $responseCart = $response['updateCartItems']['cart'];
        $item = current($responseCart['items']);

        $this->assertEquals($itemId, $item['id']);
        $this->assertEquals($quantity, $item['quantity']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testRemoveCartItemIfQuantityIsZero()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $itemId = (int)$quote->getItemByProduct($this->productRepository->get('simple'))->getId();
        $quantity = 0;

        $query = $this->getQuery($maskedQuoteId, $itemId, $quantity);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('cart', $response['updateCartItems']);

        $responseCart = $response['updateCartItems']['cart'];
        $this->assertCount(0, $responseCart['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateItemInNonExistentCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a cart with ID "non_existent_masked_id"');

        $query = $this->getQuery('non_existent_masked_id', 1, 2);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testUpdateNonExistentItem()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $notExistentItemId = 999;

        $query = $this->getQuery($maskedQuoteId, $notExistentItemId, 2);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('errors', $response['updateCartItems']);

        $responseError = $response['updateCartItems']['errors'][0];
        $this->assertEquals(
            "Could not find cart item with id: {$notExistentItemId}.",
            $responseError['message']
        );
        $this->assertEquals('COULD_NOT_FIND_CART_ITEM', $responseError['code']);
    }

    /**
     *  Test and check update with not enough quantity exception and successful update
     *
     * @param int $updateQuantity
     * @param bool $expectError
     * @param string|null $expectedErrorCode
     * @dataProvider dataProviderUpdateCartItemQuantity
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 100]
        ),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testUpdateCartItemsWithDifferentQuantity(
        int $updateQuantity,
        bool $expectError,
        ?string $expectedErrorCode = null
    ) {
        $productSku = $this->fixtures->get('product')->getSku();
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $query = $this->getCartQuery($maskedQuoteId);
        $cartResponse = $this->graphQlQuery($query, [], '', $this->getHeaderMap());
        $this->assertArrayHasKey('cart', $cartResponse);
        $this->assertArrayHasKey('itemsV2', $cartResponse['cart']);
        $items = $cartResponse['cart']['itemsV2']['items'];
        $itemId = $items[0]['uid'];
        $this->assertNotEmpty($itemId);
        $updateCartItemsMutation = $this->updateCartItemsMutation(
            $maskedQuoteId,
            $itemId,
            $updateQuantity
        );
        $updatedCartResponse = $this->graphQlMutation(
            $updateCartItemsMutation,
            [],
            '',
            $this->getHeaderMap()
        );
        if ($expectError) {
            $this->assertArrayHasKey('errors', $updatedCartResponse['updateCartItems']);
            $this->assertNotEmpty($updatedCartResponse['updateCartItems']['errors']);
            $responseError = $updatedCartResponse['updateCartItems']['errors'][0];
            $this->assertEquals($expectedErrorCode, $responseError['code']);
            $this->assertStringContainsString(
                "Could not update the product with SKU {$productSku}: Not enough items for sale",
                $responseError['message']
            );
        } else {
            $this->assertEmpty($updatedCartResponse['updateCartItems']['errors']);
            $this->assertArrayHasKey('cart', $updatedCartResponse['updateCartItems']);
            $this->assertArrayHasKey('itemsV2', $updatedCartResponse['updateCartItems']['cart']);
            $updatedItems = $updatedCartResponse['updateCartItems']['cart']['itemsV2']['items'];
            $this->assertNotEmpty($updatedItems);
            $this->assertEquals($updateQuantity, $updatedItems[0]['quantity']);
        }
    }

    /**
     * Data provider for testUpdateWithNotEnoughQuantityException
     *
     * @return array
     */
    public static function dataProviderUpdateCartItemQuantity(): array
    {
        return [
            'not_enough_quantity' => [
                'updateQuantity' => 1000,
                'expectError' => true,
                'expectedErrorCode' => 'INSUFFICIENT_STOCK',
            ],
            'enough_quantity' => [
                'updateQuantity' => 50,
                'expectError' => false,
                'expectedErrorCode' => null,
            ],
        ];
    }

    /**
     * Generates GraphQl query for retrieving cart items prices [original_item_price & original_row_total]
     *
     * @param string $customer_cart_id
     * @return string
     */
    private function getCartQuery(string $customer_cart_id): string
    {
        return <<<QUERY
        {
          cart(cart_id: "$customer_cart_id") {
            itemsV2 {
              total_count
              items {
                uid
                product {
                  name
                  sku
                }
                quantity
              }
            }
          }
        }
        QUERY;
    }

    /**
     * Generate GraphQL mutation for updating product to cart
     *
     * @param string $cartId
     * @param string $cartItemId
     * @param int $qty
     * @return string
     */
    private function updateCartItemsMutation(string $cartId, string $cartItemId, int $qty = 1): string
    {
        return <<<MUTATION
        mutation{
        updateCartItems(
            input: {
              cart_id: "{$cartId}",
              cart_items: [
                {
                  cart_item_uid: "{$cartItemId}"
                  quantity: {$qty}
                }
              ]
            }
          ) {
              cart {
                  itemsV2 {
                    items {
                      product {
                        name
                      }
                      quantity
                    }
                  }
                  prices {
                    grand_total{
                      value
                      currency
                    }
                  }
              }
              errors {
                  code
                  message
              }

            }
        }
        MUTATION;
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testUpdateItemIfItemIsNotBelongToCart()
    {
        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_order_1', 'reserved_order_id');
        $firstQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());

        $secondQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $secondQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $secondQuote->setCustomerId(1);
        $this->quoteResource->save($secondQuote);
        $secondQuoteItemId = (int)$secondQuote
            ->getItemByProduct($this->productRepository->get('virtual-product'))
            ->getId();

        $query = $this->getQuery($firstQuoteMaskedId, $secondQuoteItemId, 2);
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('errors', $response['updateCartItems']);

        $responseError = $response['updateCartItems']['errors'][0];
        $this->assertEquals(
            "Could not find cart item with id: {$secondQuoteItemId}.",
            $responseError['message']
        );
        $this->assertEquals('COULD_NOT_FIND_CART_ITEM', $responseError['code']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testUpdateItemInGuestCart()
    {
        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());
        $guestQuoteItemId = (int)$guestQuote
            ->getItemByProduct($this->productRepository->get('virtual-product'))
            ->getId();

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$guestQuoteMaskedId\""
        );

        $query = $this->getQuery($guestQuoteMaskedId, $guestQuoteItemId, 2);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testUpdateItemInAnotherCustomerCart()
    {
        $anotherCustomerQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $anotherCustomerQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $anotherCustomerQuote->setCustomerId(2);
        $this->quoteResource->save($anotherCustomerQuote);

        $anotherCustomerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$anotherCustomerQuote->getId());
        $anotherCustomerQuoteItemId = (int)$anotherCustomerQuote
            ->getItemByProduct($this->productRepository->get('virtual-product'))
            ->getId();

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$anotherCustomerQuoteMaskedId\""
        );

        $query = $this->getQuery($anotherCustomerQuoteMaskedId, $anotherCustomerQuoteItemId, 2);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @param string $input
     * @param string $message
     * @param string $errorCode
     * @dataProvider dataProviderUpdateWithMissedRequiredParameters
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @throws NoSuchEntityException
     */
    public function testUpdateWithMissedItemRequiredParameters(string $input, string $message, string $errorCode)
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_1', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());

        $query = <<<QUERY
mutation {
  updateCartItems(input: {
    cart_id: "{$maskedQuoteId}"
    {$input}
  }) {
    cart {
      items {
        id
        quantity
      }
    }
    errors {
      message
      code
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('errors', $response['updateCartItems']);

        $responseError = $response['updateCartItems']['errors'][0];
        $this->assertEquals($message, $responseError['message']);
        $this->assertEquals($errorCode, $responseError['code']);
    }

    /**
     * @return array
     */
    public static function dataProviderUpdateWithMissedRequiredParameters(): array
    {
        return [
            'missed_cart_item_qty' => [
                'cart_items: [{ cart_item_id: 1 }]',
                'Required parameter "quantity" for "cart_items" is missing.',
                'REQUIRED_PARAMETER_MISSING'
            ],
        ];
    }

    /**
     * @param string $maskedQuoteId
     * @param int $itemId
     * @param float $quantity
     * @return string
     */
    private function getQuery(string $maskedQuoteId, int $itemId, float $quantity): string
    {
        return <<<QUERY
mutation {
  updateCartItems(input: {
    cart_id: "{$maskedQuoteId}"
    cart_items:[
      {
        cart_item_id: {$itemId}
        quantity: {$quantity}
      }
    ]
  }) {
    cart {
      items {
        id
        quantity
      }
    }
    errors {
      message
      code
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
