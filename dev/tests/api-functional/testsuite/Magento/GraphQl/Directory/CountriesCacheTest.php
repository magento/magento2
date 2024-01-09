<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Directory;

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
 * Test Countries query cache
 */
class CountriesCacheTest extends GraphQLPageCacheAbstract
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
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      test - base - main_website_store
     *
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoConfigFixture default/general/locale/code en_US
     * @magentoConfigFixture default/general/country/allow US
     * @magentoConfigFixture test_store general/locale/code en_US
     * @magentoConfigFixture test_store general/country/allow US,DE
     */
    public function testGetCountries()
    {
        // Query default store countries
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($this->getQuery());
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery(),
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('countries', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['countries'];
        $this->assertCount(1, $defaultStoreResponseResult);
        $this->assertEquals('US', $defaultStoreResponseResult[0]['id']);
        // Verify we obtain a cache HIT at the 2nd time
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery(),
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('countries', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['countries'];
        $this->assertCount(1, $defaultStoreResponseHitResult);
        $this->assertEquals('US', $defaultStoreResponseHitResult[0]['id']);

        // Query test store countries
        $testStoreCode = 'test';
        $responseTestStore = $this->graphQlQueryWithResponseHeaders(
            $this->getQuery(),
            [],
            '',
            ['Store' => $testStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseTestStore['headers']);
        $testStoreCacheId = $responseTestStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($testStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $testStoreResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery(),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('countries', $testStoreResponse['body']);
        $testStoreResponseResult = $testStoreResponse['body']['countries'];
        $this->assertCount(2, $testStoreResponseResult);
        // Verify we obtain a cache HIT at the 2nd time
        $testStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery(),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('countries', $testStoreResponseHit['body']);
        $testStoreResponseHitResult = $testStoreResponseHit['body']['countries'];
        $this->assertCount(2, $testStoreResponseHitResult);
    }

    /**
     * Store scoped country config change triggers purging only the cache of the changed store.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      test - base - main_website_store
     *
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoConfigFixture default/general/locale/code en_US
     * @magentoConfigFixture default/general/country/allow US
     * @magentoConfigFixture test_store general/locale/code en_US
     * @magentoConfigFixture test_store general/country/allow US,DE
     */
    public function testCachePurgedWithStoreScopeCountryConfigChange()
    {
        // Query default store countries
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($this->getQuery());
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery(),
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('countries', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['countries'];
        $this->assertCount(1, $defaultStoreResponseResult);
        $this->assertEquals('US', $defaultStoreResponseResult[0]['id']);

        // Query test store countries
        $testStoreCode = 'test';
        $responseTestStore = $this->graphQlQueryWithResponseHeaders(
            $this->getQuery(),
            [],
            '',
            ['Store' => $testStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseTestStore['headers']);
        $testStoreCacheId = $responseTestStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($testStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $testStoreResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery(),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('countries', $testStoreResponse['body']);
        $testStoreResponseResult = $testStoreResponse['body']['countries'];
        $this->assertCount(2, $testStoreResponseResult);

        // Change test store allowed country
        $this->setConfig('general/country/allow', 'DE', ScopeInterface::SCOPE_STORE, $testStoreCode);

        // Query default store countries after test store country config is changed
        // Verify we obtain a cache HIT at the 2nd time
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery(),
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('countries', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['countries'];
        $this->assertCount(1, $defaultStoreResponseHitResult);
        $this->assertEquals('US', $defaultStoreResponseHitResult[0]['id']);

        // Query test store countries after test store country config is changed
        // Verify we obtain a cache MISS at the 2nd time
        $testStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $this->getQuery(),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('countries', $testStoreResponseMiss['body']);
        $testStoreResponseMissResult = $testStoreResponseMiss['body']['countries'];
        $this->assertCount(1, $testStoreResponseMissResult);
        // Verify we obtain a cache HIT at the 3rd time
        $testStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery(),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('countries', $testStoreResponseHit['body']);
        $testStoreResponseHitResult = $testStoreResponseHit['body']['countries'];
        $this->assertCount(1, $testStoreResponseHitResult);
    }

    /**
     * Website scope country config change triggers purging only the cache of the stores
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
     * @magentoConfigFixture default/general/locale/code en_US
     * @magentoConfigFixture default/general/country/allow US
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithWebsiteScopeCountryConfigChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query default store countries
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store countries
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
        $this->assertCount(1, $secondStoreResponse['body']['countries']);

        // Query third store countries
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
        $this->assertCount(1, $thirdStoreResponse['body']['countries']);

        // Change second website allowed country
        $this->setConfig('general/country/allow', 'US,DE', ScopeInterface::SCOPE_WEBSITES, 'second');

        // Query default store countries after the country config of the second website is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store countries after the country config of its associated second website is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertCount(2, $secondStoreResponseMiss['body']['countries']);
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store countries after the country config of its associated second website is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $thirdStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertCount(2, $thirdStoreResponseMiss['body']['countries']);
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
     * Default scope country config change triggers purging the cache of all stores.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - third - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @magentoConfigFixture default/general/locale/code en_US
     * @magentoConfigFixture default/general/country/allow US
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithDefaultScopeCountryConfigChange(): void
    {
        $query = $this->getQuery();

        // Query default store countries
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store countries
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
        $this->assertCount(1, $secondStoreResponse['body']['countries']);

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
        $this->assertCount(1, $thirdStoreResponse['body']['countries']);

        // Change default allowed country
        $this->setConfig('general/country/allow', 'US,DE', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

        // Query default store countries after the default country config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $defaultStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertCount(2, $defaultStoreResponseMiss['body']['countries']);
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store countries after the default country config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertCount(2, $secondStoreResponseMiss['body']['countries']);
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store countries after the default country config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $thirdStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertCount(2, $thirdStoreResponseMiss['body']['countries']);
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
        return <<<QUERY
query {
    countries {
        id
        two_letter_abbreviation
        three_letter_abbreviation
        full_name_locale
        full_name_english
        available_regions {
            id
            code
            name
        }
    }
}
QUERY;
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
