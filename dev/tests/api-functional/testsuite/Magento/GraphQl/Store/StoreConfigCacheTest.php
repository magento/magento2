<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\App\ApiMutableScopeConfig;
use Magento\TestFramework\Config\Model\ConfigStorage;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test storeConfig query cache
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

        // Change test store locale
        $localeConfigPath = 'general/locale/code';
        $newLocale = 'de_DE';
        $this->setConfig($localeConfigPath, $newLocale, ScopeInterface::SCOPE_STORE, $testStoreCode);

        // Query default store config after test store config change
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $defaultStoreResponseHit= $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseHitResult['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseHitResult['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseHitResult['locale']);

        // Query test store config after test store config change
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $testStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('storeConfig', $testStoreResponseMiss['body']);
        $testStoreResponseMissResult = $testStoreResponseMiss['body']['storeConfig'];
        $this->assertEquals($testStoreCode, $testStoreResponseMissResult['code']);
        $this->assertEquals($newLocale, $testStoreResponseMissResult['locale']);
        // Verify we obtain a cache HIT at the 3rd time
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
        $this->assertEquals($newLocale, $testStoreResponseHitResult['locale']);
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
