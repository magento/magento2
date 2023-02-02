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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * Test storeConfig query cache
 */
class StoreConfigCacheTest extends GraphQLPageCacheAbstract
{

    /** @var ObjectManager */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @throws NoSuchEntityException
     */
    public function testGetStoreConfig(): void
    {
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
        $storeConfig = current($storeConfigManager->getStoreConfigs([$defaultStoreCode]));
        $defaultLocale = $storeConfig->getLocale();
        $query = $this->getQuery();

        // Query default store config
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseResult['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseResult['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseResult['locale']);
        // Verify we obtain a cache HIT the second time
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
        $headerMap['Store'] = $testStoreCode;
        $responseTestStore = $this->graphQlQueryWithResponseHeaders($query, [], '', $headerMap);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseTestStore['headers']);
        $testStoreCacheId = $responseTestStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($testStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS the first time
        $testStoreResponse = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $testStoreResponse['body']);
        $testStoreResponseResult = $testStoreResponse['body']['storeConfig'];
        $this->assertEquals($testStoreCode, $testStoreResponseResult['code']);
        $this->assertEquals($defaultLocale, $testStoreResponseResult['locale']);
        // Verify we obtain a cache HIT the second time
        $testStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $testStoreResponseHit['body']);
        $testStoreResponseHitResult = $testStoreResponseHit['body']['storeConfig'];
        $this->assertEquals($testStoreCode, $testStoreResponseHitResult['code']);
        $this->assertEquals($defaultLocale, $testStoreResponseHitResult['locale']);

        // Change test store locale
        $newLocale = 'de_DE';
        $this->setConfig('general/locale/code', $newLocale, ScopeInterface::SCOPE_STORES, $testStoreCode);

        // Query default store config after test store config change
        // Verify we obtain a cache HIT the 3rd time
        $defaultStoreResponseHit2 = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $defaultStoreResponseHit2['body']);
        $defaultStoreResponseHit2Result = $defaultStoreResponseHit2['body']['storeConfig'];
        $this->assertEquals($defaultStoreId, $defaultStoreResponseHit2Result['id']);
        $this->assertEquals($defaultStoreCode, $defaultStoreResponseHit2Result['code']);
        $this->assertEquals($defaultLocale, $defaultStoreResponseHit2Result['locale']);

        // Query test store config after test store config change
        // Verify we obtain a cache MISS the 3rd time
        $testStoreResponseMiss = $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $testStoreResponseMiss['body']);
        $testStoreResponseMissResult = $testStoreResponseMiss['body']['storeConfig'];
        $this->assertEquals($testStoreCode, $testStoreResponseMissResult['code']);
        $this->assertEquals($newLocale, $testStoreResponseMissResult['locale']);
        // Verify we obtain a cache HIT the 4th time
        $testStoreResponseHit2 = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId]
        );
        $this->assertArrayHasKey('storeConfig', $testStoreResponseHit2['body']);
        $testStoreResponseHit2Result = $testStoreResponseHit2['body']['storeConfig'];
        $this->assertEquals($testStoreCode, $testStoreResponseHit2Result['code']);
        $this->assertEquals($newLocale, $testStoreResponseHit2Result['locale']);
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

    /**
     * Set configuration
     *
     * @param string $path
     * @param string $value
     * @param string|null $scope
     * @param string|null $scopeCode
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setConfig(string $path, string $value, ?string $scope = null, ?string $scopeCode = null) : void
    {
        $options = '';
        $options .= $scope ? "--scope=$scope " : '';
        $options .= $scopeCode ? "--scope-code=$scopeCode " : '';
        $options .= "$path $value";
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        $out = '';
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento config:set $options", $out);
    }
}
