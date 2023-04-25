<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\ObjectManager;

/**
 * Test the cache works properly for products and categories
 */
class CacheTagTest extends GraphQLPageCacheAbstract
{
    /**
     * Test cache invalidation for products
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testCacheInvalidationForProducts()
    {
        $productSku='simple2';
        $query
            = <<<QUERY
 {
           products(filter: {sku: {eq: "{$productSku}"}})
           {
               items {
                   id
                   name
                   sku
               }
           }
       }
QUERY;
        // Cache should be a MISS when product is queried for first time
        // Obtain the X-Magento-Cache-Id from the response which will be used as the cache key
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $product->setPrice(15);
        $productRepository->save($product);

        $responseAfterUpdate = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseAfterUpdate['headers']);
        // Cache invalidation happens and cache header value is a MISS after product update
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Test cache is invalidated properly for categories
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/product_in_multiple_categories.php
     */
    public function testCacheInvalidationForCategoriesWithProduct()
    {
        $firstProductSku = 'simple333';
        $secondProductSku = 'simple444';

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $firstProduct */
        $firstProduct = $productRepository->get($firstProductSku, false, null, true);

        $product1Query = $this->getProductQuery($firstProductSku);
        $product2Query =$this->getProductQuery($secondProductSku);
        $categoryQuery = $this->getCategoryQuery();

        // cache-debug header value should be a MISS when category is loaded first time
        $responseMissOnCategoryQuery = $this->graphQlQueryWithResponseHeaders($categoryQuery);
        $cacheId = $responseMissOnCategoryQuery['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($categoryQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Cache-debug header should be a MISS for product 1 on first request
        $responseFirstProduct = $this->graphQlQueryWithResponseHeaders($product1Query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseFirstProduct['headers']);
        $cacheId = $responseFirstProduct['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($product1Query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Cache-debug header should be a MISS for product 2 during first load
        $responseMissSecondProduct = $this->graphQlQueryWithResponseHeaders($product2Query);
        $cacheId = $responseMissSecondProduct['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($product2Query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        $firstProduct->setPrice(20);
        $productRepository->save($firstProduct);

        // cache-debug header value should be MISS after  updating product1 and reloading the Category
        $responseMissCategoryAfterProductUpdate = $this->graphQlQueryWithResponseHeaders($categoryQuery);
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseMissCategoryAfterProductUpdate['headers']
        );
        $cacheId = $responseMissCategoryAfterProductUpdate['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($categoryQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // cache-debug should be a MISS for product 1 after it is updated - cache invalidation
        $responseMissFirstProductAfterProductUpdate = $this->graphQlQueryWithResponseHeaders($product1Query);
        $cacheId = $responseMissFirstProductAfterProductUpdate['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($product1Query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Cache-debug header responses for product 2 and should be a HIT for product 2
        $responseHitSecondProduct = $this->graphQlQueryWithResponseHeaders($product2Query);
        $cacheId = $responseHitSecondProduct['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($product2Query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Get Product query
     *
     * @param string $productSku
     * @return string
     */
    private function getProductQuery(string $productSku): string
    {
        $productQuery = <<<QUERY
       {
           products(filter: {sku: {eq: "{$productSku}"}})
           {
               items {
                   id
                   name
                   sku
               }
           }
       }
QUERY;
        return $productQuery;
    }

    /**
     * Get category query
     *
     * @return string
     */
    private function getCategoryQuery(): string
    {
        $categoryQueryString = <<<QUERY
query {
        category(id: 4) {
            id
            description
            name
            product_count
            products(pageSize: 10, currentPage: 1) {
                items {
                    id
                    name
                    url_key
                }
                total_count
            }
        }
    }
QUERY;
        return $categoryQueryString;
    }
}
