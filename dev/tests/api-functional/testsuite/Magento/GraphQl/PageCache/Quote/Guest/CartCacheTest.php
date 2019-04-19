<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\Quote\Guest;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 *  End to end test which creates an empty cart and add product to the cart and load the cart.
 *  Validates that the cache-debug header is a MISS for any subsequent cart requests
 *
 * @magentoApiDataFixture Magento/Catalog/_files/products.php
 */
class CartCacheTest extends GraphQlAbstract
{
    /** @var  string */
    private $maskedQuoteId;

    protected function setUp()
    {
        $this->markTestSkipped(
            'This test will stay skipped until DEVOPS-4924 is resolved'
        );
    }

    /**
     * Tests that X-Magento-Tags are correct
     */
    public function testCartIsNotCached()
    {
        $qty = 2;
        $sku = 'simple';
        $cartId = $this->createEmptyCart();
        $this->addSimpleProductToCart($cartId, $qty, $sku);
        $getCartQuery = $this->checkCart($cartId);
        $response = $this->graphQlQuery($getCartQuery);
        self::assertArrayHasKey('cart', $response);
        self::assertArrayHasKey('items', $response['cart']);

        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($getCartQuery);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeaders);
        
        /** Cache debug header value is still a MISS for any subsequent request */
        $responseMissHeadersNext = $this->graphQlQueryForHttpHeaders($getCartQuery);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeadersNext);
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
        $this->maskedQuoteId = $response['createEmptyCart'];
        return $this->maskedQuoteId;
    }

    /**
     * Add simple product to the cart using the maskedQuoteId
     * @param $maskedCartId
     * @param $qty
     * @param $sku
     */
    private function addSimpleProductToCart($maskedCartId, $qty, $sku)
    {
        $addProductToCartQuery =
            <<<QUERY
        mutation {  
        addSimpleProductsToCart(
          input: {
            cart_id: "{$maskedCartId}"
            cartItems: [
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
     * Get Check Cart query
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function checkCart(string $maskedQuoteId): string
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
