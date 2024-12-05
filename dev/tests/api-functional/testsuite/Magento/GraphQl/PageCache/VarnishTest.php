<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;

use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Store\Test\Fixture\Store;
use Magento\Directory\Model\Currency;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Catalog\Test\Fixture\Product;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\PageCache\Model\Config;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Test that caching works properly for Varnish when using the X-Magento-Cache-Id
 */
class VarnishTest extends GraphQLPageCacheAbstract
{
    /**
     * Test that we obtain cache MISS/HIT when expected for a guest.
     */
    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(Product::class, as: 'product')
    ]
    public function testCacheResultForGuest()
    {
        /** @var ProductInterface $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $query = $this->getProductQuery($product->getSku());

        // Obtain the X-Magento-Cache-Id from the response which will be used as the cache key
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);

        // If no product is returned, we do not test empty response
        if (!empty($response['body']['products']['items'])) {
            $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

            // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
            $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

            // Verify we obtain a cache HIT the second time around for this X-Magento-Cache-Id
            $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        }
    }

    /**
     * Test that changing the Store header returns different cache results.
     */
    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(Store::class, [
            'code' => 'fixture_second_store',
            'name' => 'fixture_second_store'
        ], 'fixture_second_store'),
        DataFixture(Product::class, as: 'product')
    ]
    public function testCacheResultForGuestWithStoreHeader()
    {
        /** @var ProductInterface $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $query = $this->getProductQuery($product->getSku());

        /** @var StoreInterface $store */
        $store = DataFixtureStorageManager::getStorage()->get('fixture_second_store');

        // Verify caching works as expected without a Store header
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $defaultStoreCacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // If no product is returned, we do not test empty response
        if (!empty($response['body']['products']['items'])) {
            // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
            $this->assertCacheMissAndReturnResponse(
                $query,
                [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
            );
            // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
            $this->assertCacheHitAndReturnResponse(
                $query,
                [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
            );

            // Obtain a new X-Magento-Cache-Id using after updating the Store header
            $secondStoreResponse = $this->graphQlQueryWithResponseHeaders(
                $query,
                [],
                '',
                [
                    CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId,
                    'Store' => $store->getName()
                ]
            );
            $secondStoreCacheId = $secondStoreResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

            // Verify we obtain a cache MISS the first time we search by this X-Magento-Cache-Id
            $this->assertCacheMissAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $store->getName()
            ]);

            // Verify we obtain a cache HIT the second time around with the Store header
            $this->assertCacheHitAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $store->getName()
            ]);

            // Verify we still obtain a cache HIT for the default store
            $this->assertCacheHitAndReturnResponse(
                $query,
                [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
            );
        }
    }

    /**
     * Test that changing the Content-Currency header returns different cache results.
     */
    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        ConfigFixture(Currency::XML_PATH_CURRENCY_ALLOW, 'EUR,USD'),
        DataFixture(Product::class, as: 'product')
    ]
    public function testCacheResultForGuestWithCurrencyHeader()
    {
        /** @var ProductInterface $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $query = $this->getProductQuery($product->getSku());

        // Verify caching works as expected without a Content-Currency header
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $defaultCurrencyCacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // If no product is returned, we do not test empty response
        if (!empty($response['body']['products']['items'])) {
            // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
            $this->assertCacheMissAndReturnResponse(
                $query,
                [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]
            );
            // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
            $this->assertCacheHitAndReturnResponse(
                $query,
                [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]
            );

            // Obtain a new X-Magento-Cache-Id using after updating the Content-Currency header
            $secondCurrencyResponse = $this->graphQlQueryWithResponseHeaders(
                $query,
                [],
                '',
                [
                    'Content-Currency' => 'USD'
                ]
            );
            $secondCurrencyCacheId = $secondCurrencyResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

            // Verify we obtain a cache MISS the first time we search by this X-Magento-Cache-Id
            $this->assertCacheMissAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $secondCurrencyCacheId,
                'Content-Currency' => 'USD'
            ]);

            // Verify we obtain a cache HIT the second time around with the changed currency header
            $this->assertCacheHitAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $secondCurrencyCacheId,
                'Content-Currency' => 'USD'
            ]);

            // Verify we still obtain a cache HIT for the default currency ( no Content-Currency header)
            $this->assertCacheHitAndReturnResponse(
                $query,
                [CacheIdCalculator::CACHE_ID_HEADER => $defaultCurrencyCacheId]
            );
        }
    }

    /**
     * Test that a request with a cache id which differs from the one returned by the response is not cacheable.
     */
    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(Store::class, [
            'code' => 'fixture_second_store',
            'name' => 'fixture_second_store'
        ], 'fixture_second_store'),
        DataFixture(Product::class, as: 'product')
    ]
    public function testCacheResultForGuestWithOutdatedCacheId()
    {
        /** @var ProductInterface $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $query = $this->getProductQuery($product->getSku());

        /** @var StoreInterface $store */
        $store = DataFixtureStorageManager::getStorage()->get('fixture_second_store');

        // Verify caching with no headers in the request
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $defaultCacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        // If no product is returned, we do not test empty response
        if (!empty($response['body']['products']['items'])) {
            $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId]);
            $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId]);

            // Obtain a new X-Magento-Cache-Id using after updating the request with Store header
            $responseWithStore = $this->graphQlQueryWithResponseHeaders(
                $query,
                [],
                '',
                [
                    CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId,
                    'Store' => $store->getName()
                ]
            );
            $storeCacheId = $responseWithStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];

            // Verify we still get a cache MISS since the cache id in the request
            // doesn't match the cache id from response
            $this->assertCacheMissAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $defaultCacheId,
                'Store' => $store->getName()
            ]);

            // Verify we get a cache MISS first time with the updated cache id
            $this->assertCacheMissAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $storeCacheId,
                'Store' => $store->getName()
            ]);

            // Verify we obtain a cache HIT second time around with the updated cache id
            $this->assertCacheHitAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $storeCacheId,
                'Store' => $store->getName()
            ]);
        }
    }

    /**
     * Test that we obtain cache MISS/HIT when expected for a customer.
     */
    #[
        ConfigFixture(Config::XML_PAGECACHE_TYPE, Config::VARNISH),
        DataFixture(Product::class, as: 'product'),
        DataFixture(Customer::class, [
            'email' => 'customer@example.com',
            'password' => 'password'
        ], 'customer')
    ]
    public function testCacheResultForCustomer()
    {
        /** @var ProductInterface $product */
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $query = $this->getProductQuery($product->getSku());

        /** @var CustomerInterface $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $generateToken = $this->generateCustomerToken($customer->getEmail(), 'password');
        $tokenResponse = $this->graphQlMutationWithResponseHeaders($generateToken);

        // Verify cache is not generated for mutations
        $this->assertEquals('no-cache', $tokenResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $tokenResponse['headers']['Cache-Control']
        );
        $customerToken = $tokenResponse['body']['generateCustomerToken']['token'];

        // Obtain the X-Magento-Cache-Id from the response
        $productResponse = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            [
                'Authorization' => 'Bearer ' . $customerToken
            ]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $productResponse['headers']);

        // If no product is returned, we do not test empty response
        if (!empty($productResponse['body']['products']['items'])) {
            $cacheIdForProducts = $productResponse['headers'][CacheIdCalculator::CACHE_ID_HEADER];

            // Verify we obtain cache MISS the first time we search by this X-Magento-Cache-Id
            $this->assertCacheMissAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForProducts,
                'Authorization' => 'Bearer ' . $customerToken
            ]);

            // Verify we obtain cache HIT second time using the same X-Magento-Cache-Id
            $this->assertCacheHitAndReturnResponse($query, [
                CacheIdCalculator::CACHE_ID_HEADER => $cacheIdForProducts,
                'Authorization' => 'Bearer ' . $customerToken
            ]);
            $revokeTokenQuery = $this->revokeCustomerToken();

            // Verify that once customer logs out, X-Magento-Cache-Id will be that of an unregistered user
            $revokeTokenResponse = $this->graphQlMutationWithResponseHeaders(
                $revokeTokenQuery,
                [],
                '',
                ['Authorization' => 'Bearer ' . $customerToken]
            );

            //Verify cache is not generated for mutations
            $this->assertEquals('no-cache', $revokeTokenResponse['headers']['Pragma']);
            $this->assertEquals(
                'no-store, no-cache, must-revalidate, max-age=0',
                $revokeTokenResponse['headers']['Cache-Control']
            );
        }
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
    private function generateCustomerToken(string $email, string $password): string
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
    private function revokeCustomerToken(): string
    {
        return <<<MUTATION
mutation {
	revokeCustomerToken
	{ result }
}
MUTATION;
    }
}
