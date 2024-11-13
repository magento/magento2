<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\Directory\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's StoreConfigs query
 */
class StoreConfigResolverTest extends GraphQlAbstract
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
     * @magentoConfigFixture default_store web/seo/use_rewrites 1
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @throws NoSuchEntityException
     */
    #[
        Config(Data::XML_PATH_DEFAULT_COUNTRY, 'es', ScopeInterface::SCOPE_STORE, 'default'),
        Config(Data::XML_PATH_STATES_REQUIRED, 'us', ScopeInterface::SCOPE_STORE, 'default'),
        Config(Data::OPTIONAL_ZIP_COUNTRIES_CONFIG_PATH, 'fr', ScopeInterface::SCOPE_STORE, 'default'),
        Config(Data::XML_PATH_DISPLAY_ALL_STATES, true, ScopeInterface::SCOPE_STORE, 'default'),
    ]
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
        /** @var StoreConfigInterface $defaultStoreConfig */
        $defaultStoreConfig = current($storeConfigManager->getStoreConfigs([$store->getCode()]));
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
    default_country,
    countries_with_required_region,
    optional_zip_countries,
    display_state_if_optional
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('storeConfig', $response);
        $this->validateStoreConfig($defaultStoreConfig, $response['storeConfig'], $store);
    }

    /**
     * Validate Store Config Data
     *
     * @param StoreConfigInterface $storeConfig
     * @param array $responseConfig
     * @param Store $store
     */
    private function validateStoreConfig(
        StoreConfigInterface $storeConfig,
        array $responseConfig,
        Store $store
    ): void {
        $this->assertEquals($storeConfig->getId(), $responseConfig['id']);
        $this->assertEquals($storeConfig->getCode(), $responseConfig['code']);

        $this->assertEquals($store->getName(), $responseConfig['store_name']);
        $this->assertEquals($store->getSortOrder(), $responseConfig['store_sort_order']);
        $this->assertEquals(
            $store->getGroup()->getDefaultStoreId() == $store->getId(),
            $responseConfig['is_default_store']
        );
        $this->assertEquals($store->getGroup()->getCode(), $responseConfig['store_group_code']);
        $this->assertEquals($store->getGroup()->getName(), $responseConfig['store_group_name']);
        $this->assertEquals(
            $store->getWebsite()->getDefaultGroupId() === $store->getGroupId(),
            $responseConfig['is_default_store_group']
        );
        $this->assertEquals($store->getWebsite()->getCode(), $responseConfig['website_code']);
        $this->assertEquals($store->getWebsite()->getName(), $responseConfig['website_name']);
        $this->assertEquals($storeConfig->getCode(), $responseConfig['store_code']);

        $this->assertEquals($storeConfig->getLocale(), $responseConfig['locale']);
        $this->assertEquals($storeConfig->getBaseCurrencyCode(), $responseConfig['base_currency_code']);
        $this->assertEquals(
            $storeConfig->getDefaultDisplayCurrencyCode(),
            $responseConfig['default_display_currency_code']
        );
        $this->assertEquals($storeConfig->getTimezone(), $responseConfig['timezone']);
        $this->assertEquals($storeConfig->getWeightUnit(), $responseConfig['weight_unit']);
        $this->assertEquals($storeConfig->getBaseUrl(), $responseConfig['base_url']);
        $this->assertEquals($storeConfig->getBaseLinkUrl(), $responseConfig['base_link_url']);
        $this->assertEquals($storeConfig->getBaseStaticUrl(), $responseConfig['base_static_url']);
        $this->assertEquals($storeConfig->getBaseMediaUrl(), $responseConfig['base_media_url']);
        $this->assertEquals($storeConfig->getSecureBaseUrl(), $responseConfig['secure_base_url']);
        $this->assertEquals($storeConfig->getSecureBaseLinkUrl(), $responseConfig['secure_base_link_url']);
        $this->assertEquals($storeConfig->getSecureBaseStaticUrl(), $responseConfig['secure_base_static_url']);
        $this->assertEquals($storeConfig->getSecureBaseMediaUrl(), $responseConfig['secure_base_media_url']);
        $this->assertEquals('es', $responseConfig['default_country']);
        $this->assertEquals('us', $responseConfig['countries_with_required_region']);
        $this->assertEquals('fr', $responseConfig['optional_zip_countries']);
        $this->assertEquals('true', $responseConfig['display_state_if_optional']);
    }
}
