<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\Quote\Guest;

use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;

/**
 * Test cart queries are not cached
 *
 * @magentoApiDataFixture Magento/Catalog/_files/products.php
 */
class CartCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     */
    public function testCartIsNotCached()
    {
        $quantity = 2;
        $sku = 'simple';
        $cartId = $this->createEmptyCart();
        $this->addSimpleProductToCart($cartId, $quantity, $sku);

        $getCartQuery = $this->getCartQuery($cartId);
        $responseMiss = $this->graphQlQueryWithResponseHeaders($getCartQuery);
        $this->assertArrayHasKey('cart', $responseMiss['body']);
        $this->assertArrayHasKey('items', $responseMiss['body']['cart']);

        // Obtain the X-Magento-Cache-Id from the response which will be used as the cache key
        $response = $this->graphQlQueryWithResponseHeaders($getCartQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($getCartQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache MISS the second time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($getCartQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
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
     * @param float $quantity
     * @param string $sku
     */
    private function addSimpleProductToCart(string $maskedCartId, float $quantity, string $sku): void
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
                  quantity: $quantity
                  sku: "$sku"
                }
              }
            ]
          }
        ) {
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
      quantity
      product {
        sku
      }
    }
  }
}
QUERY;
    }
}
