<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Integration\Api\CustomerTokenServiceInterface;

/**
 * Test for merging customer carts
 */
class MergeCartsTest extends GraphQlAbstract
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
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    protected function tearDown()
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, '1', 'customer_id');
        $this->quoteResource->delete($quote);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testMergeGuestWithCustomerCart()
    {
        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_quote', 'reserved_order_id');

        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );

        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $query = $this->getCartMergeMutation($guestQuoteMaskedId, $customerQuoteMaskedId);
        $mergeResponse = $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        self::assertArrayHasKey('mergeCarts', $mergeResponse);
        $cartResponse = $mergeResponse['mergeCarts'];
        self::assertArrayHasKey('items', $cartResponse);
        self::assertCount(2, $cartResponse['items']);
        $cartResponse = $this->graphQlMutation(
            $this->getCartQuery($customerQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );

        self::assertArrayHasKey('cart', $cartResponse);
        self::assertArrayHasKey('items', $cartResponse['cart']);
        self::assertCount(2, $cartResponse['cart']['items']);
        $item1 = $cartResponse['cart']['items'][0];
        self::assertArrayHasKey('quantity', $item1);
        self::assertEquals(2, $item1['quantity']);
        $item2 = $cartResponse['cart']['items'][1];
        self::assertArrayHasKey('quantity', $item2);
        self::assertEquals(1, $item2['quantity']);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @expectedException \Exception
     * @expectedExceptionMessage Current user does not have an active cart.
     */
    public function testGuestCartExpiryAfterMerge()
    {
        $customerQuote = $this->quoteFactory->create();
        $this->quoteResource->load($customerQuote, 'test_quote', 'reserved_order_id');

        $guestQuote = $this->quoteFactory->create();
        $this->quoteResource->load(
            $guestQuote,
            'test_order_with_virtual_product_without_address',
            'reserved_order_id'
        );

        $customerQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$customerQuote->getId());
        $guestQuoteMaskedId = $this->quoteIdToMaskedId->execute((int)$guestQuote->getId());

        $query = $this->getCartMergeMutation($guestQuoteMaskedId, $customerQuoteMaskedId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
        $this->graphQlMutation(
            $this->getCartQuery($guestQuoteMaskedId),
            [],
            '',
            $this->getHeaderMap()
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @expectedException \Exception
     * @expectedExceptionMessage The current user cannot perform operations on cart
     */
    public function testMergeTwoCustomerCarts()
    {
        $firstQuote = $this->quoteFactory->create();
        $this->quoteResource->load($firstQuote, 'test_quote', 'reserved_order_id');
        $firstMaskedId = $this->quoteIdToMaskedId->execute((int)$firstQuote->getId());

        $createCartResponse = $this->graphQlMutation(
            $this->getCreateEmptyCartMutation(),
            [],
            '',
            $this->getHeaderMap('customer_two@example.com')
        );
        self::assertArrayHasKey('createEmptyCart', $createCartResponse);
        $secondMaskedId = $createCartResponse['createEmptyCart'];
        $this->addSimpleProductToCart($secondMaskedId, $this->getHeaderMap());

        $query = $this->getCartMergeMutation($firstMaskedId, $secondMaskedId);
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * Add simple product to cart
     *
     * @param string $maskedId
     * @param array $headerMap
     * @throws \Exception
     */
    private function addSimpleProductToCart(string $maskedId, array $headerMap): void
    {
        $result = $this->graphQlMutation($this->getAddProductToCartMutation($maskedId), [], '', $headerMap);
        self::assertArrayHasKey('addSimpleProductsToCart', $result);
        self::assertArrayHasKey('cart', $result['addSimpleProductsToCart']);
        self::assertArrayHasKey('items', $result['addSimpleProductsToCart']['cart']);
        self::assertArrayHasKey(0, $result['addSimpleProductsToCart']['cart']['items']);
        self::assertArrayHasKey('quantity', $result['addSimpleProductsToCart']['cart']['items'][0]);
        self::assertEquals(1, $result['addSimpleProductsToCart']['cart']['items'][0]['quantity']);
        self::assertArrayHasKey('product', $result['addSimpleProductsToCart']['cart']['items'][0]);
        self::assertArrayHasKey('sku', $result['addSimpleProductsToCart']['cart']['items'][0]['product']);
        self::assertEquals('simple_product', $result['addSimpleProductsToCart']['cart']['items'][0]['product']['sku']);
    }

    /**
     * Create the mergeCart mutation
     *
     * @param string $guestQuoteMaskedId
     * @param string $customerQuoteMaskedId
     * @return string
     */
    private function getCartMergeMutation(string $guestQuoteMaskedId, string $customerQuoteMaskedId): string
    {
        return <<<QUERY
mutation {
  mergeCarts(
    source_cart_id: "{$guestQuoteMaskedId}"
    destination_cart_id: "{$customerQuoteMaskedId}"
  ){
  items {
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * Get cart query
     *
     * @param string $maskedId
     * @return string
     */
    private function getCartQuery(string $maskedId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedId}") {
    items {
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }

    /**
     * Get create empty cart mutation
     *
     * @return string
     */
    private function getCreateEmptyCartMutation(): string
    {
        return <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
    }

    /**
     * Get add product to cart mutation
     *
     * @param string $maskedId
     * @return string
     */
    private function getAddProductToCartMutation(string $maskedId): string
    {
        return <<<QUERY
mutation {
  addSimpleProductsToCart(input: {
    cart_id: "{$maskedId}"
    cart_items: {
      data: {
        quantity: 1
        sku: "simple_product"
      }
    }
  }) {
    cart {
      items {
        quantity
        product {
          sku
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
