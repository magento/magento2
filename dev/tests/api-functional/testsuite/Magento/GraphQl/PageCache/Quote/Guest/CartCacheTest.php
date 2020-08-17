<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\Quote\Guest;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test cart queries are not cached
 *
 * @magentoApiDataFixture Magento/Catalog/_files/products.php
 */
class CartCacheTest extends GraphQlAbstract
{
    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->markTestSkipped(
            'This test will stay skipped until DEVOPS-4924 is resolved'
        );
    }

    public function testCartIsNotCached()
    {
        $qty = 2;
        $sku = 'simple';
        $cartId = $this->createEmptyCart();
        $this->addSimpleProductToCart($cartId, $qty, $sku);

        $getCartQuery = $this->getCartQuery($cartId);
        $responseMiss = $this->graphQlQueryWithResponseHeaders($getCartQuery);
        $this->assertArrayHasKey('cart', $responseMiss['body']);
        $this->assertArrayHasKey('items', $responseMiss['body']['cart']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);

        /** Cache debug header value is still a MISS for any subsequent request */
        $responseMissNext = $this->graphQlQueryWithResponseHeaders($getCartQuery);
        $this->assertEquals('MISS', $responseMissNext['headers']['X-Magento-Cache-Debug']);
    }

    /**
     * Create a guest cart which generates a maskedQuoteId
     *
     * @return string
     */
    private function createEmptyCart(): string
    {
        $query =
            <<<QUERY
        mutation
            {
               createEmptyCart
            }
QUERY;

        $response = $this->graphQlMutation($query);
        $maskedQuoteId = $response['createEmptyCart'];
        return $maskedQuoteId;
    }

    /**
     * Add simple product to the cart using the maskedQuoteId
     *
     * @param string $maskedCartId
     * @param int $qty
     * @param string $sku
     */
    private function addSimpleProductToCart(string $maskedCartId, int $qty, string $sku): void
    {
        $addProductToCartQuery =
            <<<QUERY
        mutation {  
        addSimpleProductsToCart(
          input: {
            cart_id: "{$maskedCartId}"
            cart_items: [
              {
                data: {
                  qty: $qty
                  sku: "$sku"
                }
              }
            ]
          }
        ) {
          cart {
            items {
              qty
              product {
                sku
              }
            }
          }
        }
        }
QUERY;
        $response = $this->graphQlMutation($addProductToCartQuery);
        self::assertArrayHasKey('cart', $response['addSimpleProductsToCart']);
    }

    /**
     * Get cart query string
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getCartQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedQuoteId}") {
    items {
      id
      qty
      product {
        sku
      }
    }
  }
}
QUERY;
    }
}
