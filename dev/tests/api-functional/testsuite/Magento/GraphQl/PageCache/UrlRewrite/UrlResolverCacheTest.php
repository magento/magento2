<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\UrlRewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test caching works for url resolver.
 */
class UrlResolverCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * Tests cache debug headers are correct for product urlResolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCacheTagsForProducts()
    {
        // Please refer this Magento/GraphQlCache/Controller/Catalog/ProductsCacheTest.php
        // cache-tags for 'X-Magento-Tags' coverage

        $urlKey = 'p002.html';
        $urlResolverQuery = $this->getUrlResolverQuery($urlKey);

        // Obtain the X-Magento-Cache-Id from the response which will be used as the cache key
        $response = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($urlResolverQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($urlResolverQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        //cached data should be correct
        $this->assertNotEmpty($response['body']);
        $this->assertArrayNotHasKey('errors', $response['body']);
        $this->assertEquals('PRODUCT', $response['body']['urlResolver']['type']);
    }

    /**
     * Tests cache debug headers are correct for category urlResolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCacheTagsForCategory()
    {
        // Please refer this Magento/GraphQlCache/Controller/Catalog/CategoryCacheTest.php
        // cache-tags for 'X-Magento-Tags' coverage

        $categoryUrlKey = 'cat-1.html';
        $query = $this->getUrlResolverQuery($categoryUrlKey);

        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        //verify cached data is correct
        $this->assertNotEmpty($response['body']);
        $this->assertArrayNotHasKey('errors', $response['body']);
        $this->assertEquals('CATEGORY', $response['body']['urlResolver']['type']);
    }

    /**
     * Test Cache debug headers are correct for cms page url resolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testUrlResolverCachingForCMSPage()
    {
        // Please refer this Magento/GraphQlCache/Controller/Cms/CmsPageCacheTest.php
        // cache-tags for 'X-Magento-Tags' coverage

        /** @var \Magento\Cms\Model\Page $page */
        $page = Bootstrap::getObjectManager()->get(\Magento\Cms\Model\Page::class);
        $page->load('page100');
        $requestPath = $page->getIdentifier();

        $query = $this->getUrlResolverQuery($requestPath);
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        //verify cached data is correct
        $this->assertNotEmpty($response['body']);
        $this->assertArrayNotHasKey('errors', $response['body']);
        $this->assertEquals('CMS_PAGE', $response['body']['urlResolver']['type']);
    }

    /**
     * Tests that cache is invalidated when url key is updated and access the original request path
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCacheIsInvalidatedForUrlResolver()
    {
        $productSku = 'p002';
        $urlKey = 'p002.html';
        $urlResolverQuery = $this->getUrlResolverQuery($urlKey);

        // Obtain the X-Magento-Cache-Id from the response which will be used as the cache key
        $response = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($urlResolverQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($urlResolverQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $product->setUrlKey('p002-new.html')->save();

        //cache-debug should be a MISS after updating the url key and accessing the same requestPath or urlKey
        $urlResolverQuery = $this->getUrlResolverQuery($urlKey);
        $response = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($urlResolverQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Get url resolver query
     *
     * @param $urlKey
     * @return string
     */
    private function getUrlResolverQuery(string $urlKey): string
    {
        $query = <<<QUERY
{
  urlResolver(url:"{$urlKey}")
  {
   id
   relative_url
   canonical_url
   type
  }
}
QUERY;
        return $query;
    }
}
