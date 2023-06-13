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
 * Test cache works properly for url resolver.
 */
class UrlResolverCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * Tests cache works properly for product urlResolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testUrlResolverCachingForProducts()
    {
        $urlKey = 'p002.html';
        $urlResolverQuery = $this->getUrlResolverQuery($urlKey);

        // Obtain the X-Magento-Cache-Id from the response
        $response = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheIdForProducts = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse(
            $urlResolverQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForProducts]
        );
        // Verify we obtain a cache HIT the second time
        $cachedResponse = $this->assertCacheHitAndReturnResponse(
            $urlResolverQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForProducts]
        );

        //cached data should be correct
        $this->assertNotEmpty($cachedResponse['body']);
        $this->assertArrayNotHasKey('errors', $cachedResponse['body']);
        $this->assertEquals('PRODUCT', $cachedResponse['body']['urlResolver']['type']);
    }

    /**
     * Tests cache invalidation for category urlResolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testUrlResolverCachingForCategory()
    {
        $categoryUrlKey = 'cat-1.html';
        $query = $this->getUrlResolverQuery($categoryUrlKey);

        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheIdForCategory = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForCategory]
        );
        // Verify we obtain a cache HIT the second time
        $cachedResponse = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForCategory]
        );

        //verify cached data is correct
        $this->assertNotEmpty($cachedResponse['body']);
        $this->assertArrayNotHasKey('errors', $cachedResponse['body']);
        $this->assertEquals('CATEGORY', $cachedResponse['body']['urlResolver']['type']);
    }

    /**
     * Test cache invalidation for cms page url resolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testUrlResolverCachingForCMSPage()
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = Bootstrap::getObjectManager()->get(\Magento\Cms\Model\Page::class);
        $page->load('page100');
        $requestPath = $page->getIdentifier();

        $query = $this->getUrlResolverQuery($requestPath);
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheIdForCmsPage = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForCmsPage]
        );
        // Verify we obtain a cache HIT the second time
        $cachedResponse = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForCmsPage]
        );

        //verify cached data is correct
        $this->assertNotEmpty($cachedResponse['body']);
        $this->assertArrayNotHasKey('errors', $cachedResponse['body']);
        $this->assertEquals('CMS_PAGE', $cachedResponse['body']['urlResolver']['type']);
    }

    /**
     * Tests that cache is invalidated when url key is updated and
     * access the original request path
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCacheIsInvalidatedForUrlResolver()
    {
        $productSku = 'p002';
        $urlKey = 'p002.html';
        $urlResolverQuery = $this->getUrlResolverQuery($urlKey);

        // Obtain the X-Magento-Cache-Id from the response
        $response = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheIdForUrlResolver = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse(
            $urlResolverQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForUrlResolver]
        );
        // Verify we obtain a cache HIT the second time
        $this->assertCacheHitAndReturnResponse(
            $urlResolverQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForUrlResolver]
        );

        //Updating the product url key
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $product->setUrlKey('p002-new.html')->save();

        // Verify we obtain a cache MISS the third time after product url key is updated
        $this->assertCacheMissAndReturnResponse(
            $urlResolverQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForUrlResolver]
        );
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
