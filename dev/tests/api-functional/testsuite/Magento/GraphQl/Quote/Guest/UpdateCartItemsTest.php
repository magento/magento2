<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for updating/removing shopping cart items
 */
class UpdateCartItemsTest extends GraphQlAbstract
{
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
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testUpdateCartItemQuantity()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_with_simple_product_without_address', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $itemId = (int)$quote->getItemByProduct($this->productRepository->get('simple'))->getId();
        $quantity = 2;

        $query = $this->getQuery($maskedQuoteId, $itemId, $quantity);
        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('cart', $response['updateCartItems']);

        $responseCart = $response['updateCartItems']['cart'];
        $item = current($responseCart['items']);

        $this->assertEquals($itemId, $item['id']);
        $this->assertEquals($quantity, $item['quantity']);

        //Check that update is correctly reflected in cart
        $cartQuery = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($cartQuery);

        $this->assertArrayHasKey('cart', $response);

        $responseCart = $response['cart'];
        $item = current($responseCart['items']);

        $this->assertEquals($quantity, $item['quantity']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testRemoveCartItemIfQuantityIsZero()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_with_simple_product_without_address', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $itemId = (int)$quote->getItemByProduct($this->productRepository->get('simple'))->getId();
        $quantity = 0;

        $query = $this->getQuery($maskedQuoteId, $itemId, $quantity);
        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('updateCartItems', $response);
        $this->assertArrayHasKey('cart', $response['updateCartItems']);

        $responseCart = $response['updateCartItems']['cart'];
        $this->assertCount(0, $responseCart['items']);

        //Check that update is correctly reflected in cart
        $cartQuery = $this->getCartQuery($maskedQuoteId);
        $response = $this->graphQlQuery($cartQuery);

        $this->assertArrayHasKey('cart', $response);

        $responseCart = $response['cart'];
        $this->assertCount(0, $responseCart['items']);
    }

    /**
     */
    public function testUpdateItemInNonExistentCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a cart with ID "non_existent_masked_id"');

        $query = $this->getQuery('non_existent_masked_id', 1, 2);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testUpdateNonExistentItem()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_with_simple_product_without_address', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $notExistentItemId = 999;

        $query = $this->getQuery($maskedQuoteId, $notExistentItemId, 2);
        $response = $this->graphQlMutation($query);

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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testUpdateItemIfItemIsNotBelongToCart()
    {
        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_order_with_simple_product_without_address', 'reserved_order_id');
        $firstQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());

        $secondQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $secondQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );
        $secondQuoteItemId = (int)$secondQuote
            ->getItemByProduct($this->productRepository->get('virtual-product'))
            ->getId();

        $query = $this->getQuery($firstQuoteMaskedId, $secondQuoteItemId, 2);
        $response = $this->graphQlMutation($query);

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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testUpdateItemFromCustomerCart()
    {
        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_order_1', 'reserved_order_id');
        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());
        $customerQuoteItemId = (int)$customerQuote->getItemByProduct($this->productRepository->get('simple'))->getId();

        $this->expectExceptionMessage("The current user cannot perform operations on cart \"$customerQuoteMaskedId\"");

        $query = $this->getQuery($customerQuoteMaskedId, $customerQuoteItemId, 2);
        $this->graphQlMutation($query);
    }

    /**
     * @param string $input
     * @param string $message
     * @param string $errorCode
     * @dataProvider dataProviderUpdateWithMissedRequiredParameters
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testUpdateWithMissedItemRequiredParameters(string $input, string $message, string $errorCode)
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_order_with_simple_product_without_address', 'reserved_order_id');
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
        $response = $this->graphQlMutation($query);

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
    cart_items: [
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
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId)
    {
        return <<<QUERY
query {
  cart(cart_id: "{$maskedQuoteId}"){
    items{
      product{
        name
      }
      quantity
    }
  }
}
QUERY;
    }

    /**
     * @magentoConfigFixture default_store sales/gift_options/allow_items 0
     * @magentoApiDataFixture Magento/GiftMessage/_files/guest/quote_with_item_message.php
     * @throws Exception
     */
    public function testUpdateGiftMessageCartForItemNotAllow()
    {
        $messageTo = "";
        $messageFrom = "";
        $message = "";
        $query = $this->getUpdateGiftMessageQuery($messageTo, $messageFrom, $message);
        foreach ($this->graphQlMutation($query)['updateCartItems']['cart']['items'] as $item) {
            self::assertNull($item['gift_message']);
        }
    }

    /**
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @magentoApiDataFixture Magento/GiftMessage/_files/guest/quote_with_item_message.php
     * @throws Exception
     */
    public function testUpdateGiftMessageCartForItem()
    {
        $messageTo = "Alex";
        $messageFrom = "Mike";
        $message = "Best regards";
        $query = $this->getUpdateGiftMessageQuery($messageTo, $messageFrom, $message);
        foreach ($this->graphQlMutation($query)['updateCartItems']['cart']['items'] as $item) {
            self::assertArrayHasKey('gift_message', $item);
            self::assertSame('Alex', $item['gift_message']['to']);
            self::assertSame('Mike', $item['gift_message']['from']);
            self::assertSame('Best regards', $item['gift_message']['message']);
        }
        $messageTo = "";
        $messageFrom = "";
        $message = "";
        $query = $this->getUpdateGiftMessageQuery($messageTo, $messageFrom, $message);
        foreach ($this->graphQlMutation($query)['updateCartItems']['cart']['items'] as $item) {
            self::assertArrayHasKey('gift_message', $item);
            self::assertSame(null, $item['gift_message']);
        }
    }

    #[
        DataFixture(ProductFixture::class, as: 'configProd1'),
        DataFixture(ProductFixture::class, as: 'configProd2'),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$configProd1$', '$configProd2$']],
            'configurableProduct'
        ),
        DataFixture(ProductFixture::class, as: 'simpleProduct'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configurableProduct.id$',
                'child_product_id' => '$configProd1.id$',
                'qty' => 10
            ],
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$simpleProduct.id$',
                'qty' => 10
            ]
        ),
        //We are reducing the stock of confProd1 to 6, which is less than the quantity (10) in cart
        DataFixture(ProductStockFixture::class, ['prod_id' => '$configProd1.id$', 'prod_qty' => 6]),
        DataFixture(QuoteMaskFixture::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    /**
     * Test updateCartItems GQL error when removing other products with insufficient configurable product in cart
     *
     * configProd1 & simpleProduct is added to cart with quantity 10.
     * configProd1 stock is reduced to 6. So requested qty of configProd1 is not available now.
     * updateCartItems mutation is used to remove simpleProduct from cart
     */
    public function testRemoveCartItemIfOtherProductStockIsReduced(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $simpleProdSku = $this->fixtures->get('simpleProduct')->getSku();
        $simpleProdId = $this->getQuoteItemIdBySku($simpleProdSku);
        $quantity = 0.0;

        /*
         * Set simple product quantity to 0.
         * This will remove simple product from cart
         */
        $query = $this->getQuery($maskedQuoteId, $simpleProdId, $quantity);
        $mutationResponse = $this->graphQlMutation($query);
        $this->assertArrayHasKey('updateCartItems', $mutationResponse);
        $this->assertArrayHasKey('cart', $mutationResponse['updateCartItems']);
        $this->assertArrayHasKey('items', $mutationResponse['updateCartItems']['cart']);
        $mutationResponseCartItems = $mutationResponse['updateCartItems']['cart']['items'];
        $this->assertCount(1, $mutationResponseCartItems);
        //Check that update is correctly reflected in cart
        $cartQuery = $this->getCartQuery($maskedQuoteId);
        $cartResponse = $this->graphQlQuery($cartQuery);
        $cartResponseItems = $cartResponse['cart']['items'];
        $this->assertCount(1, $cartResponseItems);
    }

    /**
     * Returns quote item id by product's SKU
     *
     * @param string $sku
     * @return int
     * @throws NoSuchEntityException
     */
    private function getQuoteItemIdBySku(string $sku): int
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get($sku);
        $this->quoteResource->load($quote, 'test_quote', 'reserved_order_id');
        /** @var Item $quoteItem */
        $quoteItem = $quote->getItemByProduct($product);

        return (int)$quoteItem->getId();
    }

    private function getUpdateGiftMessageQuery(string $messageTo, string $messageFrom, string $message)
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, 'test_guest_order_with_gift_message', 'reserved_order_id');
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$quote->getId());
        $itemId = (int)$quote->getItemByProduct($this->productRepository->get('simple'))->getId();

        return <<<QUERY
mutation {
  updateCartItems(
    input: {
      cart_id: "$maskedQuoteId",
      cart_items: [
        {
         cart_item_id: $itemId
          quantity: 3
         gift_message: {
            to: "$messageTo"
            from: "$messageFrom"
            message: "$message"
          }
        }
      ]
    }
  ) {
    cart {
      items {
        id
        product {
         name
        }
        quantity
          ... on SimpleCartItem {
          gift_message {
            to
            from
            message
          }
        }
     }
    }
  }
}
QUERY;
    }
}
