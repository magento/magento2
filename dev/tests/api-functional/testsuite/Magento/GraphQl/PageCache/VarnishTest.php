<?php
/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;

use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test that caching works properly for Varnish when using the X-Magento-Cache-Id
 */
class VarnishTest extends GraphQlAbstract
{
    /**
     * Test that we obtain cache MISS/HIT when expected for a guest.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testCacheResultForGuest()
    {
        $productSku='simple2';
        $query = $this->getProductQuery($productSku);

        // Obtain the X-Magento-Cache-Id from the response which will be used as the cache key
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMiss($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHit($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Test that changing the STORE header returns different cache results.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testCacheResultForGuestWithStoreHeader()
    {
        $productSku = 'simple2';
        $query = $this->getProductQuery($productSku);

        // Verify caching works as expected without a STORE header
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $defaultStoreCacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertCacheMiss($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]);
        $this->assertCacheHit($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]);

        // Obtain a new X-Magento-Cache-Id using after updating the STORE header
        $secondStoreResponse = $this->graphQlQueryWithResponseHeaders($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId,
            'STORE' => 'fixture_second_store'
        ]);
        $secondStoreCacheId = $secondStoreResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search by this X-Magento-Cache-Id
        $this->assertCacheMiss($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
            'STORE' => 'fixture_second_store'
        ]);

        // Verify we obtain a cache HIT the second time around with the STORE header
        $this->assertCacheHit($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
            'STORE' => 'fixture_second_store'
        ]);

        // Verify we still obtain a cache HIT for the default store
        $this->assertCacheHit($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]);
    }

    /**
     * Test that changing the CONTENT-CURRENCY header returns different cache results.
     */
    public function testCacheResultForGuestWithCurrencyHeader()
    {
        // obtain cache id
        // cache miss
        // cache hit
        // set CONTENT-CURRENCY header
        // obtain new cache id and set it on the request
        // cache miss
        // cache hit
        // remove CONTENT-CURRENCY header and use original cache id
        // cache hit
    }

    /**
     * Test that a request with a cache id which differs from the one returned by the response is not cacheable.
     */
    public function testCacheResultForGuestWithOutdatedCacheId()
    {
        // obtain cache id
        // cache miss
        // cache hit
        // set STORE header
        // obtain new cache id, but continue using old cache id
        // cache miss
        // cache miss (since supplied cache id does not match cache id from response)
        // update header with new cache id
        // cache miss
        // cache hit
    }

    /**
     * Test that we obtain cache MISS/HIT when expected for a customer.
     */
    public function testCacheResultForCustomer()
    {
        // generateCustomerToken
        // obtain auth token
        // obtain cache id
        // cache miss
        // cache hit
    }

    /**
     * Test that omitting the Auth token does not send cached content for a logged-in customer.
     */
    public function testCacheResultForCustomerWithMissingAuthToken()
    {
        // generateCustomerToken
        // obtain auth token
        // obtain cache id
        // cache miss
        // cache hit
        // unset auth token
        // cache miss
    }

    /**
     * Assert that we obtain a cache MISS when sending the provided query & headers.
     *
     * @param string $query
     * @param array $headers
     */
    private function assertCacheMiss(string $query, array $headers)
    {
        $responseMiss = $this->graphQlQueryWithResponseHeaders($query, [], '', $headers);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);
    }

    /**
     * Assert that we obtain a cache HIT when sending the provided query & headers.
     *
     * @param string $query
     * @param array $headers
     */
    private function assertCacheHit(string $query, array $headers)
    {
        $responseHit = $this->graphQlQueryWithResponseHeaders($query, [], '', $headers);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHit['headers']);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);
    }

    /**
     * Get product query
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
}
