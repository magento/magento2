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
    protected function setUp(): void
    {
        $this->markTestSkipped("Tests are skipped until vcl files are merged into mainline");
    }
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
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
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
        $secondStoreResponse = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            [
            CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId,
            'Store' => 'fixture_second_store'
        ]
        );
        $secondStoreCacheId = $secondStoreResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search by this X-Magento-Cache-Id
        $this->assertCacheMiss($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
            'Store' => 'fixture_second_store'
        ]);

        // Verify we obtain a cache HIT the second time around with the STORE header
        $this->assertCacheHit($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
            'Store' => 'fixture_second_store'
        ]);

        // Verify we still obtain a cache HIT for the default store
        $this->assertCacheHit($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]);
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_currencies.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testCacheResultForGuestWithCurrencyHeader()
    {
        $productSku = 'simple_product';
        $query = $this->getProductQuery($productSku);

        // Verify caching works as expected without a currency header
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $defaultCurrencyCacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertCacheMiss($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]);
        $this->assertCacheHit($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]);

        // Obtain a new X-Magento-Cache-Id using after updating the CONTENT-CURRENCY header
        $secondCurrencyResponse = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            [
            CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId,
            'content-currency' => 'EUR'
        ]
        );
        $secondCurrencyCacheId = $secondCurrencyResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search by this X-Magento-Cache-Id
        $this->assertCacheMiss($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondCurrencyCacheId,
            'content-currency' => 'EUR'
        ]);

        // Verify we obtain a cache HIT the second time around with the changed currency header
        $this->assertCacheHit($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondCurrencyCacheId,
            'content-currency' => 'EUR'
        ]);

        // Verify we still obtain a cache HIT for the default currency ( no content-currency header)
        $this->assertCacheHit($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]);
    }

    /**
     * Test that a request with a cache id which differs from the one returned by the response is not cacheable.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testCacheResultForGuestWithOutdatedCacheId()
    {
        $productSku = 'simple_product';
        $query = $this->getProductQuery($productSku);

        // Verify caching with no headers in the request
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $defaultCacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertCacheMiss($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId]);
        $this->assertCacheHit($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId]);

        // Obtain a new X-Magento-Cache-Id using after updating the request with STORE header
        $responseWithStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            [
                CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId,
                'STORE' => 'fixture_second_store'
            ]
        );
        $storeCacheId = $responseWithStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS since we use the old cache id
        $this->assertCacheMiss($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId,
            'STORE' => 'fixture_second_store'
        ]);

        // Verify we obtain cache MISS again since the cache id in the request doesn't match the cache id from response
        $this->assertCacheMiss($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId,
            'STORE' => 'fixture_second_store'
        ]);

        // Verify we get a cache MISS first time with the updated cache id
        $this->assertCacheMiss($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $storeCacheId,
            'STORE' => 'fixture_second_store'
        ]);

        // Verify we  obtain a cache HIT second time around with the updated cache id
        $this->assertCacheHit($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $storeCacheId,
            'STORE' => 'fixture_second_store'
        ]);
    }

    /**
     * Test that we obtain cache MISS/HIT when expected for a customer.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testCacheResultForCustomer()
    {
        $productSku = 'simple_product';
        $query = $this->getProductQuery($productSku);

        $email = 'customer@example.com';
        $password = 'password';
        $generateToken = $this->generateCustomerToken($email, $password);
        $tokenResponse = $this->graphQlMutationWithResponseHeaders($generateToken);

        // Obtain the X-Magento-Cache-id from the response and authorization token - customer logs in
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $tokenResponse['headers']);
        $cacheIdCustomer = $tokenResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $customerToken = $tokenResponse['body']['generateCustomerToken']['token'];

        // Verify we obtain cache MISS the first time we search by this X-Magento-Cache-Id
        $this->assertCacheMiss($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $cacheIdCustomer,
            'Authorization' => 'Bearer ' . $customerToken
        ]);

        // Verify we obtain cache HIT second time using the same X-Magento-Cache-Id
        $this->assertCacheHit($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $cacheIdCustomer,
            'Authorization' => 'Bearer ' . $customerToken
        ]);
        $revokeTokenQuery = $this->revokeCustomerToken();

        // Verify that once customer logs out, X-Magento-Cache-Id will be that of an unregistered user
        $revokeTokenResponse = $this->graphQlMutationWithResponseHeaders(
            $revokeTokenQuery,
            [],
            '',
            [
                CacheIdCalculator::CACHE_ID_HEADER => $cacheIdCustomer,
                'Authorization' => 'Bearer ' . $customerToken
            ]
        );

        $cacheIdGuest = $revokeTokenResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($cacheIdCustomer, $cacheIdGuest);

        //Verify that omitting the Auth token doesn't send cached content for a logged-in customer
        $this->assertCacheMiss($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdCustomer]);
        $this->assertCacheMiss($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdCustomer]);
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

    /**
     * @param string $email
     * @param string $password
     * @return string
     */
    private function generateCustomerToken(string $email, string $password) : string
    {
        return <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;
    }

    /**
     * @return string
     */
    private function revokeCustomerToken() : string
    {
        return <<<MUTATION
mutation {
	revokeCustomerToken
	{ result }
}
MUTATION;
    }
}
