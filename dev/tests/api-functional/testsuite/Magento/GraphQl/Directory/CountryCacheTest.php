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
 * Test Country query cache
 */
class CountryCacheTest extends GraphQLPageCacheAbstract
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
     * Country query is cached
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetCountry()
    {
        // Query default store US country
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($this->getQuery('US'));
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery('US'),
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('country', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['country'];
        $this->assertEquals('US', $defaultStoreResponseResult['id']);
        $this->assertEquals('US', $defaultStoreResponseResult['two_letter_abbreviation']);
        $this->assertEquals('USA', $defaultStoreResponseResult['three_letter_abbreviation']);
        $this->assertEquals('United States', $defaultStoreResponseResult['full_name_locale']);
        $this->assertEquals('United States', $defaultStoreResponseResult['full_name_english']);
        $this->assertCount(65, $defaultStoreResponseResult['available_regions']);
        $this->assertArrayHasKey('id', $defaultStoreResponseResult['available_regions'][0]);
        $this->assertArrayHasKey('code', $defaultStoreResponseResult['available_regions'][0]);
        $this->assertArrayHasKey('name', $defaultStoreResponseResult['available_regions'][0]);
        // Verify we obtain a cache HIT at the 2nd time
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery('US'),
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('country', $defaultStoreResponse['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['country'];
        $this->assertEquals('US', $defaultStoreResponseHitResult['id']);
        $this->assertEquals('US', $defaultStoreResponseHitResult['two_letter_abbreviation']);
        $this->assertEquals('USA', $defaultStoreResponseHitResult['three_letter_abbreviation']);
        $this->assertEquals('United States', $defaultStoreResponseHitResult['full_name_locale']);
        $this->assertEquals('United States', $defaultStoreResponseHitResult['full_name_english']);
        $this->assertCount(65, $defaultStoreResponseHitResult['available_regions']);
        $this->assertArrayHasKey('id', $defaultStoreResponseHitResult['available_regions'][0]);
        $this->assertArrayHasKey('code', $defaultStoreResponseHitResult['available_regions'][0]);
        $this->assertArrayHasKey('name', $defaultStoreResponseHitResult['available_regions'][0]);

        // Query test store US country
        $testStoreCode = 'test';
        $responseTestStoreUsCountry = $this->graphQlQueryWithResponseHeaders(
            $this->getQuery('US'),
            [],
            '',
            ['Store' => $testStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseTestStoreUsCountry['headers']);
        $testStoreUsCountryCacheId = $responseTestStoreUsCountry['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($testStoreUsCountryCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $testStoreUsCountryResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery('US'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreUsCountryCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $testStoreUsCountryResponse['body']);
        $testStoreUsCountryResponseResult = $testStoreUsCountryResponse['body']['country'];
        $this->assertEquals('US', $testStoreUsCountryResponseResult['id']);
        // Verify we obtain a cache HIT at the 2nd time
        $testStoreUsCountryResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery('US'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreUsCountryCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $testStoreUsCountryResponseHit['body']);
        $testStoreUsCountryResponseHitResult = $testStoreUsCountryResponseHit['body']['country'];
        $this->assertEquals('US', $testStoreUsCountryResponseHitResult['id']);

        // Query test store DE country
        $responseTestStoreDeCountry = $this->graphQlQueryWithResponseHeaders(
            $this->getQuery('DE'),
            [],
            '',
            ['Store' => $testStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseTestStoreDeCountry['headers']);
        $testStoreDeCountryCacheId = $responseTestStoreDeCountry['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $testStoreDeCountryResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery('DE'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreDeCountryCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $testStoreDeCountryResponse['body']);
        $testStoreDeCountryResponseResult = $testStoreDeCountryResponse['body']['country'];
        $this->assertEquals('DE', $testStoreDeCountryResponseResult['id']);
        $this->assertCount(16, $testStoreDeCountryResponseResult['available_regions']);
        // Verify we obtain a cache HIT at the 2nd time
        $testStoreDeCountryResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery('DE'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreDeCountryCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $testStoreDeCountryResponseHit['body']);
        $testStoreDeCountryResponseHitResult = $testStoreDeCountryResponseHit['body']['country'];
        $this->assertEquals('DE', $testStoreDeCountryResponseHitResult['id']);
        $this->assertCount(16, $testStoreDeCountryResponseHitResult['available_regions']);
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCachePurgedWithStoreScopeCountryConfigChange()
    {
        // Query default store US country
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($this->getQuery('US'));
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseDefaultStore['headers']);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $defaultStoreResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery('US'),
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('country', $defaultStoreResponse['body']);
        $defaultStoreResponseResult = $defaultStoreResponse['body']['country'];
        $this->assertEquals('US', $defaultStoreResponseResult['id']);

        // Query test store US country
        $testStoreCode = 'test';
        $responseTestStoreUsCountry = $this->graphQlQueryWithResponseHeaders(
            $this->getQuery('US'),
            [],
            '',
            ['Store' => $testStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseTestStoreUsCountry['headers']);
        $testStoreCacheId = $responseTestStoreUsCountry['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        $this->assertNotEquals($testStoreCacheId, $defaultStoreCacheId);
        // Verify we obtain a cache MISS at the 1st time
        $testStoreUsCountryResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery("US"),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $testStoreUsCountryResponse['body']);
        $testStoreUsCountryResponseResult = $testStoreUsCountryResponse['body']['country'];
        $this->assertEquals('US', $testStoreUsCountryResponseResult['id']);

        // Query test store DE country
        $responseTestStoreDeCountry = $this->graphQlQueryWithResponseHeaders(
            $this->getQuery('DE'),
            [],
            '',
            ['Store' => $testStoreCode]
        );
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $responseTestStoreDeCountry['headers']);
        $testStoreDeCountryCacheId = $responseTestStoreDeCountry['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $testStoreDeCountryResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery("DE"),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreDeCountryCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $testStoreDeCountryResponse['body']);
        $testStoreDeCountryResponseResult = $testStoreDeCountryResponse['body']['country'];
        $this->assertEquals('DE', $testStoreDeCountryResponseResult['id']);

        // Change test store allowed country
        $this->setConfig('general/country/allow', 'DE', ScopeInterface::SCOPE_STORE, $testStoreCode);

        // Query default store countries after test store country config is changed
        // Verify we obtain a cache HIT at the 2nd time
        $defaultStoreResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery('US'),
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        $this->assertArrayHasKey('country', $defaultStoreResponseHit['body']);
        $defaultStoreResponseHitResult = $defaultStoreResponseHit['body']['country'];
        $this->assertEquals('US', $defaultStoreResponseHitResult['id']);

        // Query test store DE country after test store country config is changed
        // Verify we obtain a cache MISS at the 2nd time
        $testStoreDeCountryResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery("DE"),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreDeCountryCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $testStoreDeCountryResponse['body']);
        $testStoreDeCountryResponseResult = $testStoreDeCountryResponse['body']['country'];
        $this->assertEquals('DE', $testStoreDeCountryResponseResult['id']);
        // Verify we obtain a cache HIT at the 3rd time
        $testStoreDeCountryResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery("DE"),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreDeCountryCacheId,
                'Store' => $testStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $testStoreDeCountryResponseHit['body']);
        $testStoreDeCountryResponseHitResult = $testStoreDeCountryResponseHit['body']['country'];
        $this->assertEquals('DE', $testStoreDeCountryResponseHitResult['id']);

        // Query test store US country after test store country config is changed
        // Verify we obtain a cache MISS at the 2nd time
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: The country isn\'t available.');
        $this->assertCacheMissAndReturnResponse(
            $this->getQuery('US'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $testStoreCacheId,
                'Store' => $testStoreCode
            ]
        );
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
        $query = $this->getQuery('US');

        // Query default store US country
        $responseDefaultStore = $this->graphQlQueryWithResponseHeaders($query);
        $defaultStoreCacheId = $responseDefaultStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store US country
        $secondStoreCode = 'second_store_view';
        $responseSecondStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $secondStoreCacheId = $responseSecondStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

        // Query third store US country
        $thirdStoreCode = 'third_store_view';
        $responseThirdStore = $this->graphQlQueryWithResponseHeaders(
            $query,
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $thirdStoreCacheId = $responseThirdStore['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );

        // Change second website allowed country
        $this->setConfig('general/country/allow', 'US,DE', ScopeInterface::SCOPE_WEBSITES, 'second');

        // Query default store countries after the country config of the second website is changed
        // Verify we obtain a cache HIT at the 2nd time, the cache is not purged
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store US country after the country config of its associated second website is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
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

        // Query second store DE country after the country config of its associated second website is changed
        $responseSecondStoreDeCountry = $this->graphQlQueryWithResponseHeaders(
            $this->getQuery('DE'),
            [],
            '',
            ['Store' => $secondStoreCode]
        );
        $secondStoreDeCountryCacheId = $responseSecondStoreDeCountry['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time, the cache is purged
        $secondStoreDeCountryResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery('DE'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreDeCountryCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $secondStoreDeCountryResponse['body']);
        $secondStoreDeCountryResponseResult = $secondStoreDeCountryResponse['body']['country'];
        $this->assertEquals('DE', $secondStoreDeCountryResponseResult['id']);
        // Verify we obtain a cache HIT at the 2nd time
        $secondStoreDeCountryResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery('DE'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreDeCountryCacheId,
                'Store' => $secondStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $secondStoreDeCountryResponseHit['body']);
        $secondStoreDeCountryResponseHitResult = $secondStoreDeCountryResponseHit['body']['country'];
        $this->assertEquals('DE', $secondStoreDeCountryResponseHitResult['id']);

        // Query third store US country after the country config of its associated second website is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
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

        // Query third store DE country after the country config of its associated second website is changed
        $responseThirdStoreDeCountry = $this->graphQlQueryWithResponseHeaders(
            $this->getQuery('DE'),
            [],
            '',
            ['Store' => $thirdStoreCode]
        );
        $thirdStoreDeCountryCacheId = $responseThirdStoreDeCountry['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS at the 1st time, the cache is purged
        $thirdStoreDeCountryResponse = $this->assertCacheMissAndReturnResponse(
            $this->getQuery('DE'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreDeCountryCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $thirdStoreDeCountryResponse['body']);
        $thirdStoreDeCountryResponseResult = $thirdStoreDeCountryResponse['body']['country'];
        $this->assertEquals('DE', $thirdStoreDeCountryResponseResult['id']);
        // Verify we obtain a cache HIT at the 2nd time
        $thirdStoreDeCountryResponseHit = $this->assertCacheHitAndReturnResponse(
            $this->getQuery('DE'),
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreDeCountryCacheId,
                'Store' => $thirdStoreCode
            ]
        );
        $this->assertArrayHasKey('country', $thirdStoreDeCountryResponseHit['body']);
        $thirdStoreDeCountryResponseHitResult = $thirdStoreDeCountryResponseHit['body']['country'];
        $this->assertEquals('DE', $thirdStoreDeCountryResponseHitResult['id']);
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
        $query = $this->getQuery('US');

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
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $secondStoreCacheId,
                'Store' => $secondStoreCode
            ]
        );

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
        $this->assertCacheMissAndReturnResponse(
            $query,
            [
                CacheIdCalculator::CACHE_ID_HEADER => $thirdStoreCacheId,
                'Store' => $thirdStoreCode
            ]
        );

        // Change default allowed country
        $this->setConfig('general/country/allow', 'US,DE', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

        // Query default store countries after the default country config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
        $this->assertCacheMissAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );
        // Verify we obtain a cache HIT at the 3rd time
        $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $defaultStoreCacheId]
        );

        // Query second store countries after the default country config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
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

        // Query third store countries after the default country config is changed
        // Verify we obtain a cache MISS at the 2nd time, the cache is purged
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
     * @param string $countryId
     * @return string
     */
    private function getQuery(string $countryId): string
    {
        return <<<QUERY
query {
    country(id: {$countryId}) {
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
