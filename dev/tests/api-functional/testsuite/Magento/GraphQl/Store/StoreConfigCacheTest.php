<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test storeConfig query cache
 */
class StoreConfigCacheTest extends GraphQlAbstract
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
        $storeId = $storeResolver->getCurrentStoreId();
        $store = $storeRepository->getById($storeId);
        /** @var StoreConfigInterface $storeConfig */
        $storeConfig = current($storeConfigManager->getStoreConfigs([$store->getCode()]));
        $defaultLocale = $storeConfig->getLocale();
        $query = $this->getQuery();

        // Query default store config
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $response['headers']);
        $this->assertEquals('MISS', $response['headers']['X-Magento-Cache-Debug']);
        $this->assertArrayHasKey('storeConfig', $response['body']);
        $responseConfig = $response['body']['storeConfig'];
        $this->assertEquals($storeConfig->getId(), $responseConfig['id']);
        $this->assertEquals($storeConfig->getCode(), $responseConfig['code']);
        $this->assertEquals($defaultLocale, $responseConfig['locale']);
        // Query default store config again
        $responseHit = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHit['headers']);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);
        $responseHitConfig = $responseHit['body']['storeConfig'];
        $this->assertEquals($storeConfig->getCode(), $responseHitConfig['code']);
        $this->assertEquals($defaultLocale, $responseHitConfig['locale']);

        // Query test store config
        $headerMap['Store'] = 'test';
        $responseTestStore = $this->graphQlQueryWithResponseHeaders($query, [], '', $headerMap);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseTestStore['headers']);
        $this->assertEquals('MISS', $responseTestStore['headers']['X-Magento-Cache-Debug']);
        $responseTestStoreConfig = $responseTestStore['body']['storeConfig'];
        $this->assertEquals('test', $responseTestStoreConfig['code']);
        $this->assertEquals($defaultLocale, $responseTestStoreConfig['locale']);
        // Query test store config again
        $responseTestStoreHit = $this->graphQlQueryWithResponseHeaders($query, [], '', $headerMap);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseTestStoreHit['headers']);
        $this->assertEquals('HIT', $responseTestStoreHit['headers']['X-Magento-Cache-Debug']);
        $responseTestStoreHitConfig = $responseTestStoreHit['body']['storeConfig'];
        $this->assertEquals('test', $responseTestStoreHitConfig['code']);
        $this->assertEquals($defaultLocale, $responseTestStoreHitConfig['locale']);

        // Change test store locale
        $newLocale = 'de_DE';
        $this->setConfig('general/locale/code', $newLocale, ScopeInterface::SCOPE_STORES, 'test');

        // Query default store config
        $responseHit2 = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHit2['headers']);
        $this->assertEquals('HIT', $responseHit2['headers']['X-Magento-Cache-Debug']);
        $responseHit2Config = $responseHit2['body']['storeConfig'];
        $this->assertEquals($storeConfig->getCode(), $responseHit2Config['code']);
        $this->assertEquals($defaultLocale, $responseHit2Config['locale']);

        // Query test store config
        $responseTestStoreMiss = $this->graphQlQueryWithResponseHeaders($query, [], '', $headerMap);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseTestStoreMiss['headers']);
        $this->assertEquals('MISS', $responseTestStoreMiss['headers']['X-Magento-Cache-Debug']);
        $responseTestStoreMissConfig = $responseTestStoreMiss['body']['storeConfig'];
        $this->assertEquals('test', $responseTestStoreMissConfig['code']);
        $this->assertEquals($newLocale, $responseTestStoreMissConfig['locale']);

        // Query test store config again
        $responseTestStoreHit2 = $this->graphQlQueryWithResponseHeaders($query, [], '', $headerMap);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseTestStoreHit2['headers']);
        $this->assertEquals('HIT', $responseTestStoreHit2['headers']['X-Magento-Cache-Debug']);
        $responseTestStoreHit2Config = $responseTestStoreHit2['body']['storeConfig'];
        $this->assertEquals('test', $responseTestStoreHit2Config['code']);
        $this->assertEquals($newLocale, $responseTestStoreHit2Config['locale']);
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
