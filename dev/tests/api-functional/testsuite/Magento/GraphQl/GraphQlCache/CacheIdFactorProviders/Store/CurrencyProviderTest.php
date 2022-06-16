<?php
/*
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQl\GraphQlCache\CacheIdFactorProviders\Store;

use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class for currency CacheIdFactorProvider.
 */
class CurrencyProviderTest extends GraphQlAbstract
{
    /**
     * Tests that the cache id header changes based on the currency and remains consistent for the same currency.
     *
     * @magentoApiDataFixture Magento/Store/_files/multiple_currencies.php
     */
    public function testCacheIdHeaderWithCurrency()
    {
        $query = 'query { products( filter: {sku: {eq: "simple1"}}) { items { name }}}';

        $currency1Response = $this->graphQlQueryWithResponseHeaders($query, [], '', ['content-currency' => 'USD']);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $currency1Response['headers']);
        $currency1CacheId = $currency1Response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $currency1CacheId));

        $currency2Response = $this->graphQlQueryWithResponseHeaders($query, [], '', ['content-currency' => 'EUR']);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $currency2Response['headers']);
        $currency2CacheId = $currency2Response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertTrue((boolean)preg_match('/^[0-9a-f]{64}$/i', $currency2CacheId));

        // Assert that currency1 and currency2 return different cache ids
        $this->assertNotEquals($currency1CacheId, $currency2CacheId);

        // Assert that currency1 returns the same cache id as before
        $currency1Response = $this->graphQlQueryWithResponseHeaders($query, [], '', ['content-currency' => 'USD']);
        $this->assertEquals($currency1CacheId, $currency1Response['headers'][CacheIdCalculator::CACHE_ID_HEADER]);
    }
}
