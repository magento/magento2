<?php
/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\GraphQlCache\CacheIdFactorProviders\Store;

use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class for store CacheIdFactorProvider.
 */
class StoreProviderTest extends GraphQlAbstract
{
    /**
     * Tests that the cache id header changes based on the store and remains consistent for the same store.
     *
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     */
    public function testCacheIdHeaderWithStore()
    {
        $query = 'query { products( filter: {sku: {eq: "simple1"}}) { items { name }}}';

        $store1Response = $this->graphQlQueryWithResponseHeaders($query, [], '', ['store' => 'default']);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $store1Response['headers']);
        $store1CacheId = $store1Response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $store1CacheId));

        $store2Response = $this->graphQlQueryWithResponseHeaders($query, [], '', ['store' => 'fixture_second_store']);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $store2Response['headers']);
        $store2CacheId = $store2Response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $store2CacheId));

        // Assert that store1 and store2 return different cache ids
        $this->assertNotEquals($store1CacheId, $store2CacheId);

        // Assert that store1 returns the same cache id as before
        $store1Response = $this->graphQlQueryWithResponseHeaders($query, [], '', ['store' => 'default']);
        $this->assertEquals($store1CacheId, $store1Response['headers'][CacheIdCalculator::CACHE_ID_HEADER]);
    }
}
