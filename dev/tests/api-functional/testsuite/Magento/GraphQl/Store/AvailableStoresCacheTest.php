<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

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
 * Test availableStores query cache
 */
class AvailableStoresCacheTest extends GraphQLPageCacheAbstract
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
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     */
    public function testAvailableStoreConfigs(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('availableStores', $defaultStoreResponse['body']);
        $this->assertCount(1, $defaultStoreResponse['body']['availableStores']);
        // Verify we obtain a cache HIT at the 2nd time
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('availableStores', $defaultStoreResponseHit['body']);
        $this->assertCount(1, $defaultStoreResponseHit['body']['availableStores']);

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
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
        $this->assertArrayHasKey('availableStores', $secondStoreResponse['body']);
        $this->assertCount(2, $secondStoreResponse['body']['availableStores']);
        // Verify we obtain a cache HIT at the 2nd time
        $secondStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('availableStores', $secondStoreResponseHit['body']);
        $this->assertCount(2, $secondStoreResponseHit['body']['availableStores']);

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $secondStoreCurrentStoreGroupResponse = $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('availableStores', $secondStoreCurrentStoreGroupResponse['body']);
        $this->assertCount(1, $secondStoreCurrentStoreGroupResponse['body']['availableStores']);
        // Verify we obtain a cache HIT at the 2nd time
        $secondStoreCurrentStoreGroupResponseHit = $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('availableStores', $secondStoreCurrentStoreGroupResponseHit['body']);
        $this->assertCount(1, $secondStoreCurrentStoreGroupResponseHit['body']['availableStores']);
    }

    /**
     * Store scoped config change triggers purging only the cache of the changed store.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithStoreScopeConfigChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change third store locale
        $localeConfigPath = 'general/locale/code';
        $newLocale = 'de_DE';
        $this->setConfig($localeConfigPath, $newLocale, ScopeInterface::SCOPE_STORE, 'third_store_view');

        // Query available stores of default store's website after 3rd store configuration is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after 3rd store configuration is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after 3rd store configuration is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change second store locale
        $localeConfigPath = 'general/locale/code';
        $newLocale = 'de_DE';
        $this->setConfig($localeConfigPath, $newLocale, ScopeInterface::SCOPE_STORE, $secondStoreCode);

        // Query available stores of default store's website after 2nd store configuration is changed
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after 2nd store configuration is changed
        // Verify we obtain a cache MISS at the 4th time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 5th time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after 2nd store configuration is changed
        // Verify we obtain a cache MISS at the 3rd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
    }

    /**
     * Website scope config change triggers purging only the cache of the stores associated with the changed website.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithWebsiteScopeConfigChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change second website locale
        $localeConfigPath = 'general/locale/code';
        $newLocale = 'de_DE';
        $this->setConfig($localeConfigPath, $newLocale, ScopeInterface::SCOPE_WEBSITES, 'second');

        // Query available stores of default store's website after second website configuration is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after second website configuration is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after second website configuration is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
    }

    /**
     * Default scope config change triggers purging the cache of all stores.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithDefaultScopeConfigChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change default locale
        $localeConfigPath = 'general/locale/code';
        $newLocale = 'de_DE';
        $this->setConfig($localeConfigPath, $newLocale, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

        // Query available stores of default store's website after default configuration is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after default configuration is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after default configuration is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
    }

    /**
     * Store change triggers purging only the cache of the changed store.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithStoreChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change third store name
        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $store->load('third_store_view', 'code');
        $thirdStoreName = 'Third Store View';
        $thirdStoreNewName = $thirdStoreName . ' 2';
        $store->setName($thirdStoreNewName);
        $store->save();

        // Query available stores of default store's website after 3rd store is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after 3rd store is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after 3rd store is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change second store name
        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $store->load($secondStoreCode, 'code');
        $secondStoreName = 'Second Store View';
        $secondStoreNewName = $secondStoreName . ' 2';
        $store->setName($secondStoreNewName);
        $store->save();

        // Query available stores of default store's website after 2nd store is changed
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after 2nd store group is changed
        // Verify we obtain a cache MISS at the 4th time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 5th time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after 2nd store is changed
        // Verify we obtain a cache MISS at the 3rd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
    }

    /**
     * Store group change triggers purging only the cache of the stores associated with the changed store group.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithStoreGroupChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change third store group name
        /** @var Group $storeGroup */
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->load('third_store', 'code');
        $thirdStoreGroupName = 'Third store group';
        $thirdStoreGroupNewName = $thirdStoreGroupName . ' 2';
        $storeGroup->setName($thirdStoreGroupNewName);
        $storeGroup->save();

        // Query available stores of default store's website after 3rd store group is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after 3rd store group is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after 3rd store group is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change second store group name
        /** @var Group $storeGroup */
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->load('second_store', 'code');
        $secondStoreGroupName = 'Second store group';
        $secondStoreGroupNewName = $secondStoreGroupName . ' 2';
        $storeGroup->setName($secondStoreGroupNewName);
        $storeGroup->save();

        // Query available stores of default store's website after 2nd store group is changed
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after 2nd store group is changed
        // Verify we obtain a cache MISS at the 4th time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 5th time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after 2nd store group is changed
        // Verify we obtain a cache MISS at the 3rd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
    }

    /**
     * Store website change triggers purging only the cache of the stores associated with the changed store website.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithWebsiteChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Change second store website name
        /** @var Website $website */
        $website = $this->objectManager->create(Website::class);
        $website->load('second', 'code');
        $secondStoreWebsiteName = 'Second Test Website';
        $secondStoreWebsiteNewName = $secondStoreWebsiteName . ' 2';
        $website->setName($secondStoreWebsiteNewName);
        $website->save();

        // Query available stores of default store's website after second website is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after second website is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after second website is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
    }

    /**
     * Store group switches from one website to another website triggers purging the cache of the stores
     * associated with both websites.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedAfterStoreGroupSwitchedWebsite(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of default store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseDefaultStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders($currentStoreGroupQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStoreCurrentStoreGroup['headers']);
        $defaultStoreCurrentStoreGroupCacheId =
            $responseDefaultStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of third store's website and any store groups of the website
        $thirdStoreCode = 'third_store_view';
        $responseThirdStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseThirdStore['headers']);
        $thirdStoreCacheId = $responseThirdStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($thirdStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );

        // Query available stores of third store's website and store group
        $responseThirdStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseThirdStoreCurrentStoreGroup['headers']
        );
        $thirdStoreCurrentStoreGroupCacheId =
            $responseThirdStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($thirdStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCurrentStoreGroupCacheId,
                'Store' => $thirdStoreCode
            ]
        );

        // Second store group switches from second website to base website
        /** @var Website $website */
        $website = $this->objectManager->create(Website::class);
        $website->load('base', 'code');
        /** @var Group $storeGroup */
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->load('second_store', 'code');
        $storeGroup->setWebsiteId($website->getId());
        $storeGroup->save();

        // Query available stores of default store's website
        // after second store group switched from second website to base website
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of default store's website and store group
        // after second store group switched from second website to base website
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after second store group switched from second website to base website
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after second store group switched from second website to base website
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of third store's website (second website) and any store groups of the website
        // after second store group switched from second website to base website
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );

        // Query available stores of third store's website (second website) and store group
        // after second store group switched from second website to base website
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCurrentStoreGroupCacheId,
                'Store' => $thirdStoreCode
            ]
        );
    }

    /**
     * Store switches from one store group to another store group triggers purging the cache of the stores
     * associated with both store groups.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedAfterStoreSwitchedStoreGroup(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of default store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseDefaultStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders($currentStoreGroupQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStoreCurrentStoreGroup['headers']);
        $defaultStoreCurrentStoreGroupCacheId =
            $responseDefaultStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of third store's website and any store groups of the website
        $thirdStoreCode = 'third_store_view';
        $responseThirdStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseThirdStore['headers']);
        $thirdStoreCacheId = $responseThirdStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($thirdStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );

        // Query available stores of third store's website and store group
        $responseThirdStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseThirdStoreCurrentStoreGroup['headers']
        );
        $thirdStoreCurrentStoreGroupCacheId =
            $responseThirdStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($thirdStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCurrentStoreGroupCacheId,
                'Store' => $thirdStoreCode
            ]
        );

        // Second store switches from second store group to main_website_store store group
        /** @var Group $storeGroup */
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->load('main_website_store', 'code');
        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $store->load($secondStoreCode, 'code');
        $store->setStoreGroupId($storeGroup->getId());
        $store->setWebsiteId($storeGroup->getWebsiteId());
        $store->save();

        // Query available stores of default store's website
        // after second store switched from second store group to main_website_store store group
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of default store's website and store group
        // after second store switched from second store group to main_website_store store group
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after second store switched from second store group to main_website_store store group
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after second store switched from second store group to main_website_store store group
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of third store's website (second website) and any store groups of the website
        // after second store switched from second store group to main_website_store store group
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );

        // Query available stores of third store's website (second website) and store group
        // after second store switched from second store group to main_website_store store group
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCurrentStoreGroupCacheId,
                'Store' => $thirdStoreCode
            ]
        );
    }

    /**
     * Creating new store with new website and new store group will not purge the cache of the other stores that are not
     * associated with the new website and new store group
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCacheNotPurgedWithNewStoreWithNewStoreGroupNewWebsite(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Create new website
        $website = $this->objectManager->create(Website::class);
        $website->setData([
            'code' => 'new',
            'name' => 'New Test Website',
            'is_default' => '0',
        ]);
        $website->save();

        // Query available stores of default store's website after new website is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new website is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new website is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Create new store group
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->setCode('new_store')
            ->setName('New store group')
            ->setWebsite($website);
        $storeGroup->save();

        // Query available stores of default store's website after new store group is created
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new store group is created
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new store group is created
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Create new store with new store group and new website
        $store = $this->objectManager->create(Store::class);
        $store->setData([
            'code' => 'new_store_view',
            'website_id' => $website->getId(),
            'group_id' => $storeGroup->getId(),
            'name' => 'new Store View',
            'sort_order' => 10,
            'is_active' => 1,
        ]);
        $store->save();

        // Query available stores of default store's website
        // after new store with new website and new store group is created
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new store with new website and new store group is created
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new store with new website and new store group is created
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // remove new store, new store group, new website
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $store->delete();
        $storeGroup->delete();
        $website->delete();
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Creating new store with new website and second store group will not purge the cache of the other stores that are
     * not associated with the new website
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCacheNotPurgedWithNewStoreWithSecondStoreGroupNewWebsite(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Create new website
        $website = $this->objectManager->create(Website::class);
        $website->setData([
            'code' => 'new',
            'name' => 'New Test Website',
            'is_default' => '0',
        ]);
        $website->save();

        // Get second store group
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->load('second_store', 'code');

        // Create new store with second store group and new website
        $store = $this->objectManager->create(Store::class);
        $store->setData([
            'code' => 'new_store_view',
            'website_id' => $website->getId(),
            'group_id' => $storeGroup->getId(),
            'name' => 'new Store View',
            'sort_order' => 10,
            'is_active' => 1,
        ]);
        $store->save();

        // Query available stores of default store's website
        // after new store with new website and second store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new store with new website and second store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new store with new website and seond store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // remove new store, new website
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $store->delete();
        $website->delete();
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Creating new store with second website and new store group will only purge the cache of availableStores for
     * all stores of second website
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithNewStoreWithNewStoreGroupSecondWebsite(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Get second website
        $website = $this->objectManager->create(Website::class);
        $website->load('second', 'code');

        // Create new store group
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->setCode('new_store')
            ->setName('New store group')
            ->setWebsite($website);
        $storeGroup->save();

        // Create new store with new store group and second website
        $store = $this->objectManager->create(Store::class);
        $store->setData([
            'code' => 'new_store_view',
            'website_id' => $website->getId(),
            'group_id' => $storeGroup->getId(),
            'name' => 'new Store View',
            'sort_order' => 10,
            'is_active' => 1,
        ]);
        $store->save();

        // Query available stores of default store's website
        // after new store with second website and new store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new store with second website and new store group is created
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new store with second website and new store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // remove new store, new store group
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $store->delete();
        $storeGroup->delete();
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Creating new inactive store with second website and new store group will not purge the cache of availableStores
     * for all stores of second website, will purge the cache of availableStores for all stores of second website when
     * the new store is activated
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCacheNotPurgedWithNewInactiveStoreWithNewStoreGroupSecondWebsitePurgedWhenActivated(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Get second website
        $website = $this->objectManager->create(Website::class);
        $website->load('second', 'code');

        // Create new store group
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->setCode('new_store')
            ->setName('New store group')
            ->setWebsite($website);
        $storeGroup->save();

        // Create new inactive store with new store group and second website
        $store = $this->objectManager->create(Store::class);
        $store->setData([
            'code' => 'new_store_view',
            'website_id' => $website->getId(),
            'group_id' => $storeGroup->getId(),
            'name' => 'new Store View',
            'sort_order' => 10,
            'is_active' => 0,
        ]);
        $store->save();

        // Query available stores of default store's website
        // after new inactive store with second website and new store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new inactive store with second website and new store group is created
        // Verify we obtain a cache Hit at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new inactive store with second website and new store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Activate the store
        $store->setIsActive(1);
        $store->save();

        // Query available stores of default store's website after the store is activated
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after the store is activated
        // Verify we obtain a cache MISS at the 3rd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after the store is activated
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // remove new store, new store group
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $store->delete();
        $storeGroup->delete();
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Creating new store with second website and second store group will only purge the cache of availableStores for
     * all stores of second website or second website with second store group
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithNewStoreWithSecondStoreGroupSecondWebsite(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Get second website
        $website = $this->objectManager->create(Website::class);
        $website->load('second', 'code');

        // Get second store group
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->load('second_store', 'code');

        // Create new store with second store group and second website
        $store = $this->objectManager->create(Store::class);
        $store->setData([
            'code' => 'new_store_view',
            'website_id' => $website->getId(),
            'group_id' => $storeGroup->getId(),
            'name' => 'new Store View',
            'sort_order' => 10,
            'is_active' => 1,
        ]);
        $store->save();

        // Query available stores of default store's website
        // after new store with second website and second store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new store with second website and second store group is created
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new store with second website and second store group is created
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // remove new store
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $store->delete();
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Creating new inactive store with second website and second store group will not purge the cache of
     * availableStores for all stores of second website or second website with second store group, will purge the
     * cache of availableStores for all stores of second website or second website with second store group
     * after the store is activated
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCacheNotPurgedWithNewInactiveStoreWithSecondStoreGroupSecondWebsitePurgedAfterActivated(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Get second website
        $website = $this->objectManager->create(Website::class);
        $website->load('second', 'code');

        // Get second store group
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->load('second_store', 'code');

        // Create new inactive store with second store group and second website
        $store = $this->objectManager->create(Store::class);
        $store->setData([
            'code' => 'new_store_view',
            'website_id' => $website->getId(),
            'group_id' => $storeGroup->getId(),
            'name' => 'new Store View',
            'sort_order' => 10,
            'is_active' => 0,
        ]);
        $store->save();

        // Query available stores of default store's website
        // after new inactive store with second website and second store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new inactive store with second website and second store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new inactive store with second website and second store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Activate the store
        $store->setIsActive(1);
        $store->save();

        // Query available stores of default store's website after the store is activated
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after the store is activated
        // Verify we obtain a cache MISS at the 3rd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after the store is activated
        // Verify we obtain a cache MISS at the 3rd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );
        // Verify we obtain a cache HIT at the 4th time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // remove new store
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $store->delete();
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Creating new store with one store group website will purge the cache of availableStores
     * no matter for current store group or not
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - second - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithNewStoreCreatedInOneStoreGroupWebsite(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query available stores of default store's website
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of default store's website and store group
        $currentStoreGroupQuery = $this->getQuery('true');
        $responseDefaultStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders($currentStoreGroupQuery);
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseDefaultStoreCurrentStoreGroup['headers']
        );
        $defaultStoreCurrentStoreGroupCacheId =
            $responseDefaultStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );

        // Query available stores of second store's website and any store groups of the website
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseSecondStore['headers']);
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website and store group
        $responseSecondStoreCurrentStoreGroup = $this->graphQlQueryWithResponseHeaders(
            $currentStoreGroupQuery,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $this->assertArrayHasKey(
            CacheIdCalculator::CACHE_ID_HEADER,
            $responseSecondStoreCurrentStoreGroup['headers']
        );
        $secondStoreCurrentStoreGroupCacheId =
            $responseSecondStoreCurrentStoreGroup['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($secondStoreCurrentStoreGroupCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Get base website
        $website = $this->objectManager->create(Website::class);
        $website->load('base', 'code');

        // Create new store group
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->setCode('new_store')
            ->setName('New store group')
            ->setWebsite($website);
        $storeGroup->save();

        // Create new store with new store group and base website
        $store = $this->objectManager->create(Store::class);
        $store->setData([
            'code' => 'new_store_view',
            'website_id' => $website->getId(),
            'group_id' => $storeGroup->getId(),
            'name' => 'new Store View',
            'sort_order' => 10,
            'is_active' => 1,
        ]);
        $store->save();

        // Query available stores of default store's website
        // after new store with default website and new store group is created
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query available stores of default store's website and store group
        // after new store with base website and new store group is created
        // Verify we obtain a cache MISS at the 2nd time
        $this->assertCacheMissAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCurrentStoreGroupCacheId]
        );

        // Query available stores of second store's website (second website) and any store groups of the website
        // after new store with base website and new store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query available stores of second store's website (second website) and store group
        // after new store with base website and new store group is created
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $currentStoreGroupQuery,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCurrentStoreGroupCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // remove new store
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $store->delete();
        $storeGroup->delete();
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
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
     * @param string $useCurrentGroup
     * @return string
     */
    private function getQuery(string $useCurrentGroup = ''): string
    {
        $useCurrentGroupArg = $useCurrentGroup === '' ? '' : '(useCurrentGroup:' . $useCurrentGroup . ')';
        return <<<QUERY
{
  availableStores{$useCurrentGroupArg} {
    id,
    code,
    store_code,
    store_name,
    store_sort_order,
    is_default_store,
    store_group_code,
    store_group_name,
    is_default_store_group,
    website_id,
    website_code,
    website_name,
    locale,
    base_currency_code,
    default_display_currency_code,
    timezone,
    weight_unit,
    base_url,
    base_link_url,
    base_static_url,
    base_media_url,
    secure_base_url,
    secure_base_link_url,
    secure_base_static_url,
    secure_base_media_url,
    store_name
    use_store_in_url
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
