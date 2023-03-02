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
