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
use Magento\TestFramework\ObjectManager;
use Magento\UrlRewrite\Model\UrlFinderInterface;

/**
 * Test caching works for url resolver.
 */
class UrlResolverCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * Tests that X-Magento-tags and cache debug headers are correct for product urlResolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCacheTagsForProducts()
    {
        $productSku = 'p002';
        $urlKey = 'p002.html';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $urlResolverQuery = $this->getUrlResolverQuery($urlKey);
        $responseMiss = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);

        print_r("Debug value UrlResolverCacheTest testCacheTagsForProducts\n");
        $json_response = json_encode($responseMiss, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");
        print_r("Debug value End of testCacheTagsForProducts\n");

        $this->assertArrayHasKey('X-Magento-Tags', $responseMiss['headers']);
        $actualTags = explode(',', $responseMiss['headers']['X-Magento-Tags']);
        $expectedTags = ["cat_p", "cat_p_{$product->getId()}", "FPC"];
        $this->assertEquals($expectedTags, $actualTags);

        // Obtain the X-Magento-Cache-Id from the response which will be used as the cache key
        $response = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($urlResolverQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $responseHit = $this->assertCacheHitAndReturnResponse(
            $urlResolverQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

        //cached data should be correct
        $this->assertNotEmpty($responseHit['body']);
        $this->assertArrayNotHasKey('errors', $responseHit['body']);
        $this->assertEquals('PRODUCT', $responseHit['body']['urlResolver']['type']);
    }
    /**
     * Tests that X-Magento-tags and cache debug headers are correct for category urlResolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/CatalogUrlRewrite/_files/product_with_category.php
     */
    public function testCacheTagsForCategory()
    {
        $categoryUrlKey = 'cat-1.html';
        $productSku = 'p002';
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $storeId = $product->getStoreId();

        /** @var  UrlFinderInterface $urlFinder */
        $urlFinder = Bootstrap::getObjectManager()->get(UrlFinderInterface::class);
        $actualUrls = $urlFinder->findOneByData(
            [
                'request_path' => $categoryUrlKey,
                'store_id' => $storeId
            ]
        );
        $categoryId = $actualUrls->getEntityId();
        $query = $this->getUrlResolverQuery($categoryUrlKey);
        $responseMiss = $this->graphQlQueryWithResponseHeaders($query);

        print_r("Debug value UrlResolverCacheTest testCacheTagsForCategory\n");
        $json_response = json_encode($responseMiss, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");

        $this->assertArrayHasKey('X-Magento-Tags', $responseMiss['headers']);
        $actualTags = explode(',', $responseMiss['headers']['X-Magento-Tags']);
        $expectedTags = ["cat_c", "cat_c_{$categoryId}", "FPC"];
        $this->assertEquals($expectedTags, $actualTags);

        //cache-debug should be a MISS on first request
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);

        //cache-debug should be a HIT on second request
        $responseHit = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHit['headers']);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);

        //verify cached data is correct
        $this->assertNotEmpty($responseHit['body']);
        $this->assertArrayNotHasKey('errors', $responseHit['body']);
        $this->assertEquals('CATEGORY', $responseHit['body']['urlResolver']['type']);
    }
    /**
     * Test that X-Magento-Tags Cache debug headers are correct for cms page url resolver
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/pages.php
     */
    public function testUrlResolverCachingForCMSPage()
    {
        /** @var \Magento\Cms\Model\Page $page */
        $page = Bootstrap::getObjectManager()->get(\Magento\Cms\Model\Page::class);
        $page->load('page100');
        $cmsPageId = $page->getId();
        $requestPath = $page->getIdentifier();

        $query = $this->getUrlResolverQuery($requestPath);
        $responseMiss = $this->graphQlQueryWithResponseHeaders($query);

        print_r("Debug value UrlResolverCacheTest testUrlResolverCachingForCMSPage\n");
        $json_response = json_encode($responseMiss, JSON_PRETTY_PRINT);
        print_r($json_response);
        print_r("\n end \n");

        $this->assertArrayHasKey('X-Magento-Tags', $responseMiss['headers']);
        $actualTags = explode(',', $responseMiss['headers']['X-Magento-Tags']);
        $expectedTags = ["cms_p", "cms_p_{$cmsPageId}", "FPC"];
        $this->assertEquals($expectedTags, $actualTags);

        //cache-debug should be a MISS on first request
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);

        //cache-debug should be a HIT on second request
        $responseHit = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);

        //verify cached data is correct
        $this->assertNotEmpty($responseHit['body']);
        $this->assertArrayNotHasKey('errors', $responseHit['body']);
        $this->assertEquals('CMS_PAGE', $responseHit['body']['urlResolver']['type']);
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
        $responseMiss = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        //cache-debug should be a MISS on first request
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);

        //cache-debug should be a HIT on second request
        $urlResolverQuery = $this->getUrlResolverQuery($urlKey);
        $responseHit = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        /** @var Product $product */
        $product = $productRepository->get($productSku, false, null, true);
        $product->setUrlKey('p002-new.html')->save();

        //cache-debug should be a MISS after updating the url key and accessing the same requestPath or urlKey
        $urlResolverQuery = $this->getUrlResolverQuery($urlKey);
        $responseMiss = $this->graphQlQueryWithResponseHeaders($urlResolverQuery);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);
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
