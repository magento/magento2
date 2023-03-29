<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Directory;

use Magento\Directory\Model\Currency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Store\Model\Group;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\TestFramework\App\ApiMutableScopeConfig;
use Magento\TestFramework\Config\Model\ConfigStorage;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test currency query cache
 */
class CurrencyCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ApiMutableScopeConfig
     */
    private $config;

    /**
     * @var ConfigStorage
     */
    private $configStorage;

    /**
     * @var array
     */
    private $origConfigs = [];

    /**
     * @var array
     */
    private $notExistingOrigConfigs = [];

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->configStorage = $this->objectManager->get(ConfigStorage::class);
        $this->config = $this->objectManager->get(ApiMutableScopeConfig::class);
    }

    /**
     * currency query is cached
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      test - base - main_website_store
     *
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture default/currency/options/default USD
     * @magentoConfigFixture default/currency/options/allow USD
     * @magentoConfigFixture test_store currency/options/base USD
     * @magentoConfigFixture test_store currency/options/default CNY
     * @magentoConfigFixture test_store currency/options/allow CNY,USD
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     */
    public function testGetCurrency()
    {
        $query = $this->getQuery();

        // Query default store currency
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('currency', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['currency'];
        $this->assertEquals('USD', $defaultStoreResponseResult['base_currency_code']);
        $this->assertEquals('USD', $defaultStoreResponseResult['default_display_currency_code']);
        $this->assertEquals(['USD'], $defaultStoreResponseResult['available_currency_codes']);
        $this->assertEquals('USD', $defaultStoreResponseResult['exchange_rates'][0]['currency_to']);
        $this->assertEquals(1, $defaultStoreResponseResult['exchange_rates'][0]['rate']);
        // Verify we obtain a cache HIT at the 2nd time
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('currency', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['currency'];
        $this->assertEquals('USD', $defaultStoreResponseHitResult['base_currency_code']);
        $this->assertEquals('USD', $defaultStoreResponseHitResult['default_display_currency_code']);
        $this->assertEquals(['USD'], $defaultStoreResponseHitResult['available_currency_codes']);
        $this->assertEquals('USD', $defaultStoreResponseHitResult['exchange_rates'][0]['currency_to']);
        $this->assertEquals(1, $defaultStoreResponseHitResult['exchange_rates'][0]['rate']);

        // Query test store currency
        $testStoreCode = 'test';
        $responseTestStore = $this->graphQlQueryWithResponseHeaders($query, [], '', ['Store' => $testStoreCode]);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseTestStore['headers']);
        $testStoreCacheId = $responseTestStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($testStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $testStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('currency', $testStoreResponse['body']);
        $testStoreResponseResult = $testStoreResponse['body']['currency'];
        $this->assertEquals('USD', $testStoreResponseResult['base_currency_code']);
        $this->assertEquals('CNY', $testStoreResponseResult['default_display_currency_code']);
        $this->assertEquals(['CNY','USD'], $testStoreResponseResult['available_currency_codes']);
        // Verify we obtain a cache HIT at the 2nd time
        $testStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('currency', $testStoreResponseHit['body']);
        $testStoreResponseHitResult = $testStoreResponse['body']['currency'];
        $this->assertEquals('USD', $testStoreResponseHitResult['base_currency_code']);
        $this->assertEquals('CNY', $testStoreResponseHitResult['default_display_currency_code']);
        $this->assertEquals(['CNY','USD'], $testStoreResponseHitResult['available_currency_codes']);
    }

    /**
     * Store scoped currency change triggers purging only the cache of the changed store.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      test - base - main_website_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default/currency/options/allow USD
     */
    public function testCachePurgedWithStoreScopeCurrencyConfigChange(): void
    {
        $query = $this->getQuery();

        // Query default store currency
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('currency', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['currency'];
        $this->assertEquals(['USD'], $defaultStoreResponseResult['available_currency_codes']);

        // Query second store currency
        $secondStoreCode = 'test';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders($query, [], '', ['Store' => $secondStoreCode]);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $secondStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('currency', $secondStoreResponse['body']);
        $secondStoreResponseResult = $secondStoreResponse['body']['currency'];
        $this->assertEquals(['USD'], $secondStoreResponseResult['available_currency_codes']);

        // Change second store allowed currency
        $this->setConfig('currency/options/allow', 'CNY,USD', ScopeInterface::SCOPE_STORE, $secondStoreCode);

        // Query default store currency after second store currency config is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('currency', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['currency'];
        $this->assertEquals(['USD'], $defaultStoreResponseHitResult['available_currency_codes']);

        // Query second store currency after second store currency config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('currency', $secondStoreResponseMiss['body']);
        $secondStoreResponseMissResult = $secondStoreResponseMiss['body']['currency'];
        $this->assertEquals(['CNY','USD'], $secondStoreResponseMissResult['available_currency_codes']);
        // Verify we obtain a cache HIT at the 3rd time
        $secondStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('currency', $secondStoreResponseHit['body']);
        $secondStoreResponseHitResult = $secondStoreResponseHit['body']['currency'];
        $this->assertEquals(['CNY','USD'], $secondStoreResponseHitResult['available_currency_codes']);
    }

    /**
     * Website scope currency config change triggers purging only the cache of the stores
     * associated with the changed website.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoConfigFixture default/currency/options/allow USD
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithWebsiteScopeCurrencyConfigChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query default store currency
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store currency
        $secondStoreCode = 'second_store_view';
        $responseThirdStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $secondStoreCacheId = $responseThirdStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $secondStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(['USD'], $secondStoreResponse['body']['currency']['available_currency_codes']);

        // Query third store currency
        $thirdStoreCode = 'third_store_view';
        $responseThirdStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $thirdStoreCacheId = $responseThirdStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $thirdStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals(['USD'], $thirdStoreResponse['body']['currency']['available_currency_codes']);

        // Change second website allowed currency
        $this->setConfig('currency/options/allow', 'CNY,USD', ScopeInterface::SCOPE_WEBSITES, 'second');

        // Query default store currency after the currency config of the second website is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store currency after the currency config of its associated second website is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(
            ['CNY','USD'],
            $secondStoreResponseMiss['body']['currency']['available_currency_codes']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store currency after the currency config of its associated second website is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $thirdStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals(
            ['CNY','USD'],
            $thirdStoreResponseMiss['body']['currency']['available_currency_codes']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
    }

    /**
     * Default scope currency config change triggers purging the cache of all stores.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - third - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoConfigFixture default/currency/options/allow USD
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithDefaultScopeCurrencyConfigChange(): void
    {
        $query = $this->getQuery();

        // Query default store currency
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store currency
        $secondStoreCode = 'second_store_view';
        $responseThirdStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $secondStoreCacheId = $responseThirdStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $secondStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(['USD'], $secondStoreResponse['body']['currency']['available_currency_codes']);

        // Query third store config
        $thirdStoreCode = 'third_store_view';
        $responseThirdStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $thirdStoreCacheId = $responseThirdStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $thirdStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals(['USD'], $thirdStoreResponse['body']['currency']['available_currency_codes']);

        // Change default allowed currency
        $this->setConfig('currency/options/allow', 'CNY,USD', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

        // Query default store currency after the default currency config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $defaultStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertEquals(['CNY','USD'], $defaultStoreResponseMiss['body']['currency']['available_currency_codes']);
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store currency after the default currency config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(['CNY','USD'], $secondStoreResponseMiss['body']['currency']['available_currency_codes']);
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store currency after the default currency config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $thirdStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals(['CNY','USD'], $thirdStoreResponseMiss['body']['currency']['available_currency_codes']);
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
    }

    /**
     * Exchange rate change triggers purging the cache of all stores.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - third - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoConfigFixture default/currency/options/allow CNY,USD
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithExchangeRateChange(): void
    {
        $query = $this->getQuery();

        // Query default store currency
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store currency
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $secondStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals('CNY', $secondStoreResponse['body']['currency']['exchange_rates'][0]['currency_to']);
        $this->assertEquals(7, $secondStoreResponse['body']['currency']['exchange_rates'][0]['rate']);

        // Query third store config
        $thirdStoreCode = 'third_store_view';
        $responseThirdStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $thirdStoreCacheId = $responseThirdStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $thirdStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals('CNY', $thirdStoreResponse['body']['currency']['exchange_rates'][0]['currency_to']);
        $this->assertEquals(7, $thirdStoreResponse['body']['currency']['exchange_rates'][0]['rate']);

        // Change USD to CNY exchange rate
        $newUsdToCnyRate = '6.5000';
        $currencyModel = $this->objectManager->create(Currency::class);
        $currencyModel->saveRates(['USD' => ['CNY' => $newUsdToCnyRate]]);

        // Query default store currency after the exchange rate is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $defaultStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertEquals(
            'CNY',
            $defaultStoreResponseMiss['body']['currency']['exchange_rates'][0]['currency_to']
        );
        $this->assertEquals(
            $newUsdToCnyRate,
            $defaultStoreResponseMiss['body']['currency']['exchange_rates'][0]['rate']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store currency after the exchange rate is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(
            'CNY',
            $secondStoreResponseMiss['body']['currency']['exchange_rates'][0]['currency_to']
        );
        $this->assertEquals(
            $newUsdToCnyRate,
            $secondStoreResponseMiss['body']['currency']['exchange_rates'][0]['rate']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store currency after the exchange rate is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $thirdStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals('CNY', $thirdStoreResponseMiss['body']['currency']['exchange_rates'][0]['currency_to']);
        $this->assertEquals($newUsdToCnyRate, $thirdStoreResponseMiss['body']['currency']['exchange_rates'][0]['rate']);
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
    }

    private function changeToTwoWebsitesThreeStoreGroupsThreeStores()
    {
        /** @var $website2 \Magento\Store\Model\Website */
        $website2 = $this->objectManager->create(Website::class);
        $website2Id = $website2->load('second', 'code')->getId();

        // Change third store to the same website of second store
        /** @var Store $store3 */
        $store3 = $this->objectManager->create(Store::class);
        $store3->load('third_store_view', 'code');
        $store3GroupId = $store3->getStoreGroupId();
        /** @var Group $store3Group */
        $store3Group = $this->objectManager->create(Group::class);
        $store3Group->load($store3GroupId)->setWebsiteId($website2Id)->save();
        $store3->setWebsiteId($website2Id)->save();
    }

    /**
     * Get query
     *
     * @return string
     */
    private function getQuery(): string
    {
        $query = <<<QUERY
query {
    currency {
        base_currency_code
        base_currency_symbol
        default_display_currency_code
        default_display_currency_symbol
        available_currency_codes
        exchange_rates {
            currency_to
            rate
        }
    }
}
QUERY;
        return $query;
    }

    protected function tearDown(): void
    {
        $this->restoreConfig();
        parent::tearDown();
    }

    /**
     * Set configuration
     *
     * @param string $path
     * @param string $value
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return void
     */
    private function setConfig(
        string  $path,
        string  $value,
        string  $scopeType,
        ?string $scopeCode = null
    ): void {
        if ($this->configStorage->checkIsRecordExist($path, $scopeType, $scopeCode)) {
            $this->origConfigs[] = [
                'path' => $path,
                'value' => $this->configStorage->getValueFromDb($path, $scopeType, $scopeCode),
                'scopeType' => $scopeType,
                'scopeCode' => $scopeCode
            ];
        } else {
            $this->notExistingOrigConfigs[] = [
                'path' => $path,
                'scopeType' => $scopeType,
                'scopeCode' => $scopeCode
            ];
        }
        $this->config->setValue($path, $value, $scopeType, $scopeCode);
    }

    private function restoreConfig()
    {
        foreach ($this->origConfigs as $origConfig) {
            $this->config->setValue(
                $origConfig['path'],
                $origConfig['value'],
                $origConfig['scopeType'],
                $origConfig['scopeCode']
            );
        }
        $this->origConfigs = [];

        foreach ($this->notExistingOrigConfigs as $notExistingOrigConfig) {
            $this->configStorage->deleteConfigFromDb(
                $notExistingOrigConfig['path'],
                $notExistingOrigConfig['scopeType'],
                $notExistingOrigConfig['scopeCode']
            );
        }
        $this->notExistingOrigConfigs = [];
    }
}
