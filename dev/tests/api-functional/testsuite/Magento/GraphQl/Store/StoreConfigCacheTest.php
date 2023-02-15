<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\TestFramework\App\ApiMutableScopeConfig;
use Magento\TestFramework\Config\Model\ConfigStorage;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test storeConfig query cache
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreConfigCacheTest extends GraphQLPageCacheAbstract
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
     * @var StoreConfigInterface
     */
    private $defaultStoreConfig;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->configStorage = $this->objectManager->get(ConfigStorage::class);
        $this->config = $this->objectManager->get(ApiMutableScopeConfig::class);

        /** @var  StoreConfigManagerInterface $storeConfigManager */
        $storeConfigManager = $this->objectManager->get(StoreConfigManagerInterface::class);
        /** @var StoreResolverInterface $storeResolver */
        $storeResolver = $this->objectManager->get(StoreResolverInterface::class);
        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $defaultStoreId = $storeResolver->getCurrentStoreId();
        $store = $storeRepository->getById($defaultStoreId);
        $defaultStoreCode = $store->getCode();
        /** @var StoreConfigInterface $storeConfig */
        $this->defaultStoreConfig = current($storeConfigManager->getStoreConfigs([$defaultStoreCode]));
    }

    /**
     * storeConfig query is cached.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      test - base - main_website_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @throws NoSuchEntityException
     */
    public function testGetStoreConfig(): void
    {
        $defaultStoreId = $this->defaultStoreConfig->getId();
        $defaultStoreCode = $this->defaultStoreConfig->getCode();
        $defaultLocale = $this->defaultStoreConfig->getLocale();
        $query = $this->getQuery();

        // Query default store config
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseResult['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseResult['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseResult['locale']);
        // Verify we obtain a cache HIT at the 2nd time
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseHitResult['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseHitResult['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseHitResult['locale']);

        // Query test store config
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
        $this->assertArrayHasKey('storeConfig', $testStoreResponse['body']);
        $testStoreResponseResult = $testStoreResponse['body']['storeConfig'];
        $this->assertEquals($testStoreCode, $testStoreResponseResult['code']);
        $this->assertEquals($defaultLocale, $testStoreResponseResult['locale']);
        // Verify we obtain a cache HIT at the 2nd time
        $testStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('storeConfig', $testStoreResponseHit['body']);
        $testStoreResponseHitResult = $testStoreResponseHit['body']['storeConfig'];
        $this->assertEquals($testStoreCode, $testStoreResponseHitResult['code']);
        $this->assertEquals($defaultLocale, $testStoreResponseHitResult['locale']);
    }

    /**
     * Store scoped config change triggers purging only the cache of the changed store.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      test - base - main_website_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @throws NoSuchEntityException
     */
    public function testCachePurgedWithStoreScopeConfigChange(): void
    {
        $defaultStoreId = $this->defaultStoreConfig->getId();
        $defaultStoreCode = $this->defaultStoreConfig->getCode();
        $defaultLocale = $this->defaultStoreConfig->getLocale();
        $query = $this->getQuery();

        // Query default store config
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseResult['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseResult['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseResult['locale']);

        // Query second store config
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
        $this->assertArrayHasKey('storeConfig', $secondStoreResponse['body']);
        $secondStoreResponseResult = $secondStoreResponse['body']['storeConfig'];
        $this->assertEquals($secondStoreCode, $secondStoreResponseResult['code']);
        $this->assertEquals($defaultLocale, $secondStoreResponseResult['locale']);

        // Change second store locale
        $localeConfigPath = 'general/locale/code';
        $newLocale = 'de_DE';
        $this->setConfig($localeConfigPath, $newLocale, ScopeInterface::SCOPE_STORE, $secondStoreCode);

        // Query default store config after second store config is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseHitResult['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseHitResult['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseHitResult['locale']);

        // Query second store config after second store config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('storeConfig', $secondStoreResponseMiss['body']);
        $secondStoreResponseMissResult = $secondStoreResponseMiss['body']['storeConfig'];
        $this->assertEquals($secondStoreCode, $secondStoreResponseMissResult['code']);
        $this->assertEquals($newLocale, $secondStoreResponseMissResult['locale']);
        // Verify we obtain a cache HIT at the 3rd time
        $secondStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('storeConfig', $secondStoreResponseHit['body']);
        $secondStoreResponseHitResult = $secondStoreResponseHit['body']['storeConfig'];
        $this->assertEquals($secondStoreCode, $secondStoreResponseHitResult['code']);
        $this->assertEquals($newLocale, $secondStoreResponseHitResult['locale']);
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
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithWebsiteScopeConfigChange(): void
    {
        $this->changeToTwoWebsitesThreeStoreGroupsThreeStores();
        $defaultLocale = $this->defaultStoreConfig->getLocale();
        $query = $this->getQuery();

        // Query default store config
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store config
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
        $this->assertEquals($defaultLocale, $secondStoreResponse['body']['storeConfig']['locale']);

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
        $this->assertEquals($defaultLocale, $thirdStoreResponse['body']['storeConfig']['locale']);

        // Change second website locale
        $localeConfigPath = 'general/locale/code';
        $newLocale = 'de_DE';
        $this->setConfig($localeConfigPath, $newLocale, ScopeInterface::SCOPE_WEBSITES, 'second');

        // Query default store config after the config of the second website is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store config after the config of its associated second website is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(
            $newLocale,
            $secondStoreResponseMiss['body']['storeConfig']['locale']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store config after the config of its associated second website is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $thirdStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals(
            $newLocale,
            $thirdStoreResponseMiss['body']['storeConfig']['locale']
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
     * Default scope config change triggers purging the cache of all stores.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - third - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithDefaultScopeConfigChange(): void
    {
        $defaultLocale = $this->defaultStoreConfig->getLocale();
        $query = $this->getQuery();

        // Query default store config
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store config
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
        $this->assertEquals($defaultLocale, $secondStoreResponse['body']['storeConfig']['locale']);

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
        $this->assertEquals($defaultLocale, $thirdStoreResponse['body']['storeConfig']['locale']);

        // Change default locale
        $localeConfigPath = 'general/locale/code';
        $newLocale = 'de_DE';
        $this->setConfig($localeConfigPath, $newLocale, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

        // Query default store config after the default config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $defaultStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertEquals(
            $newLocale,
            $defaultStoreResponseMiss['body']['storeConfig']['locale']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store config after the default config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(
            $newLocale,
            $secondStoreResponseMiss['body']['storeConfig']['locale']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store config after the default config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $thirdStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals(
            $newLocale,
            $thirdStoreResponseMiss['body']['storeConfig']['locale']
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
     * Store change triggers purging only the cache of the changed store.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      test - base - main_website_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @throws NoSuchEntityException
     */
    public function testCachePurgedWithStoreChange(): void
    {
        $defaultStoreId = $this->defaultStoreConfig->getId();
        $defaultStoreCode = $this->defaultStoreConfig->getCode();
        $defaultLocale = $this->defaultStoreConfig->getLocale();
        $query = $this->getQuery();

        // Query default store config
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseResult['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseResult['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseResult['locale']);

        // Query second store config
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
        $this->assertArrayHasKey('storeConfig', $secondStoreResponse['body']);
        $secondStoreResponseResult = $secondStoreResponse['body']['storeConfig'];
        $this->assertEquals($secondStoreCode, $secondStoreResponseResult['code']);
        $secondStoreName = 'Test Store';
        $this->assertEquals($secondStoreName, $secondStoreResponseResult['store_name']);

        // Change second store name
        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $store->load($secondStoreCode, 'code');
        $secondStoreNewName = $secondStoreName . ' 2';
        $store->setName($secondStoreNewName);
        $store->save();

        // Query default store config after second store is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseHitResult['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseHitResult['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseHitResult['locale']);
        $this->assertEquals($defaultStoreResponseResult['store_name'], $defaultStoreResponseHitResult['store_name']);

        // Query second store config after second store is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('storeConfig', $secondStoreResponseMiss['body']);
        $secondStoreResponseMissResult = $secondStoreResponseMiss['body']['storeConfig'];
        $this->assertEquals($secondStoreCode, $secondStoreResponseMissResult['code']);
        $this->assertEquals($secondStoreNewName, $secondStoreResponseMissResult['store_name']);
        // Verify we obtain a cache HIT at the 3rd time
        $secondStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('storeConfig', $secondStoreResponseHit['body']);
        $secondStoreResponseHitResult = $secondStoreResponseHit['body']['storeConfig'];
        $this->assertEquals($secondStoreCode, $secondStoreResponseHitResult['code']);
        $this->assertEquals($secondStoreNewName, $secondStoreResponseHitResult['store_name']);
    }

    /**
     * Store group change triggers purging only the cache of the stores associated with the changed store group.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - base - second_store
     *      third_store_view - base - second_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithStoreGroupChange(): void
    {
        $this->changeToOneWebsiteTwoStoreGroupsThreeStores();
        $query = $this->getQuery();

        // Query default store config
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store config
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
        $secondStoreGroupName = 'Second store group';
        $this->assertEquals($secondStoreGroupName, $secondStoreResponse['body']['storeConfig']['store_group_name']);

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
        $this->assertEquals($secondStoreGroupName, $thirdStoreResponse['body']['storeConfig']['store_group_name']);

        // Change second store group name
        /** @var Group $storeGroup */
        $storeGroup = $this->objectManager->create(Group::class);
        $storeGroup->load('second_store', 'code');
        $secondStoreGroupNewName = $secondStoreGroupName . ' 2';
        $storeGroup->setName($secondStoreGroupNewName);
        $storeGroup->save();

        // Query default store config after second store group is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store config after its associated second store group is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(
            $secondStoreGroupNewName,
            $secondStoreResponseMiss['body']['storeConfig']['store_group_name']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store config after its associated second store group is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $thirdStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertEquals(
            $secondStoreGroupNewName,
            $thirdStoreResponseMiss['body']['storeConfig']['store_group_name']
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
     * Store website change triggers purging only the cache of the stores associated with the changed store website.
     *
     * Test stores set up:
     *      STORE - WEBSITE - STORE GROUP
     *      default - base - main_website_store
     *      second_store_view - second - second_store
     *      third_store_view - third - third_store
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/multiple_websites_with_store_groups_stores.php
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithWebsiteChange(): void
    {
        $query = $this->getQuery();

        // Query default store config
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store config
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
        $secondStoreWebsiteName = 'Second Test Website';
        $this->assertEquals($secondStoreWebsiteName, $secondStoreResponse['body']['storeConfig']['website_name']);

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
        $this->assertEquals('Third test Website', $thirdStoreResponse['body']['storeConfig']['website_name']);

        // Change second store website name
        /** @var Website $website */
        $website = $this->objectManager->create(Website::class);
        $website->load('second', 'code');
        $secondStoreWebsiteNewName = $secondStoreWebsiteName . ' 2';
        $website->setName($secondStoreWebsiteNewName);
        $website->save();

        // Query default store config after second store website is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store config after its associated second store group is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $secondStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertEquals(
            $secondStoreWebsiteNewName,
            $secondStoreResponseMiss['body']['storeConfig']['website_name']
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store config after second store website is changed
        // Verify we obtain a cache HIT at the 2nd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );
    }

    private function changeToOneWebsiteTwoStoreGroupsThreeStores()
    {
        // Change second store to the same website of the default store
        /** @var Store $store2 */
        $store2 = $this->objectManager->create(Store::class);
        $store2->load('second_store_view', 'code');
        $store2GroupId = $store2->getStoreGroupId();
        /** @var Group $store2Group */
        $store2Group = $this->objectManager->create(Group::class);
        $store2Group->load($store2GroupId);
        $store2Group->setWebsiteId(1)->save();
        $store2->setWebsiteId(1)->save();

        // Change third store to the same store group and website of second store
        /** @var Store $store3 */
        $store3 = $this->objectManager->create(Store::class);
        $store3->load('third_store_view', 'code');
        $store3->setGroupId($store2GroupId)->setWebsiteId(1)->save();
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
        $query
            = <<<QUERY
{
  storeConfig {
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
