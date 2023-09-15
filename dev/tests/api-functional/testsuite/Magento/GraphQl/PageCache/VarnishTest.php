<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;

use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;

/**
 * Test that caching works properly for Varnish when using the X-Magento-Cache-Id
 */
class VarnishTest extends GraphQLPageCacheAbstract
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
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
    }

    /**
     * Test that changing the Store header returns different cache results.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testCacheResultForGuestWithStoreHeader()
    {
        $productSku = 'simple2';
        $query = $this->getProductQuery($productSku);

        // Verify caching works as expected without a Store header
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $defaultStoreCacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]);
        // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]);

        // Obtain a new X-Magento-Cache-Id using after updating the Store header
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
        $this->assertCacheMissAndReturnResponse($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
            'Store' => 'fixture_second_store'
        ]);

        // Verify we obtain a cache HIT the second time around with the Store header
        $this->assertCacheHitAndReturnResponse($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
            'Store' => 'fixture_second_store'
        ]);

        // Verify we still obtain a cache HIT for the default store
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]);
    }

    /**
     * Test that changing the Content-Currency header returns different cache results.
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_currencies.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testCacheResultForGuestWithCurrencyHeader()
    {
        $productSku = 'simple_product';
        $query = $this->getProductQuery($productSku);

        // Verify caching works as expected without a Content-Currency header
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $defaultCurrencyCacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]
        );
        // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]);

        // Obtain a new X-Magento-Cache-Id using after updating the Content-Currency header
        $secondCurrencyResponse = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            [
                CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId,
                'Content-Currency' => 'EUR'
            ]
        );
        $secondCurrencyCacheId = $secondCurrencyResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we obtain a cache MISS the first time we search by this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondCurrencyCacheId,
            'Content-Currency' => 'EUR'
        ]);

        // Verify we obtain a cache HIT the second time around with the changed currency header
        $this->assertCacheHitAndReturnResponse($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $secondCurrencyCacheId,
            'Content-Currency' => 'EUR'
        ]);

        // Verify we still obtain a cache HIT for the default currency ( no Content-Currency header)
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]);
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
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId]);
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId]);

        // Obtain a new X-Magento-Cache-Id using after updating the request with Store header
        $responseWithStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            [
                CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId,
                'Store' => 'fixture_second_store'
            ]
        );
        $storeCacheId = $responseWithStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // Verify we still get a cache MISS since the cache id in the request doesn't match the cache id from response
        $this->assertCacheMissAndReturnResponse($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId,
            'Store' => 'fixture_second_store'
        ]);

        // Verify we get a cache MISS first time with the updated cache id
        $this->assertCacheMissAndReturnResponse($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $storeCacheId,
            'Store' => 'fixture_second_store'
        ]);

        // Verify we obtain a cache HIT second time around with the updated cache id
        $this->assertCacheHitAndReturnResponse($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $storeCacheId,
            'Store' => 'fixture_second_store'
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
        $this->assertCacheMissAndReturnResponse($query, [
            CacheIdCalculator::CACHE_ID_HEADER => $cacheIdCustomer,
            'Authorization' => 'Bearer ' . $customerToken
        ]);

        // Verify we obtain cache HIT second time using the same X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [
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
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdCustomer]);
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdCustomer]);
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
