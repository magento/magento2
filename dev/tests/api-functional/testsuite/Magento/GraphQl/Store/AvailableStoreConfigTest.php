<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's AvailableStores query
 */
class AvailableStoreConfigTest extends GraphQlAbstract
{

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreConfigManagerInterface
     */
    private $storeConfigManager;

    /**
     * @var StoreResource
     */
    private $storeResource;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeConfigManager = $this->objectManager->get(StoreConfigManagerInterface::class);
        $this->storeResource = $this->objectManager->get(StoreResource::class);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoApiDataFixture Magento/Store/_files/inactive_store.php
     */
    public function testDefaultWebsiteAvailableStoreConfigs(): void
    {
        $storeConfigs = $this->storeConfigManager->getStoreConfigs();

        $expectedAvailableStores = [];
        $expectedAvailableStoreCodes = [
            'default',
            'test'
        ];

        foreach ($storeConfigs as $storeConfig) {
            if (in_array($storeConfig->getCode(), $expectedAvailableStoreCodes)) {
                $expectedAvailableStores[] = $storeConfig;
            }
        }

        $query
            = <<<QUERY
{
  availableStores {
    id,
    code,
    website_id,
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
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('availableStores', $response);
        foreach ($expectedAvailableStores as $key => $storeConfig) {
            $this->validateStoreConfig($storeConfig, $response['availableStores'][$key]);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/second_website_with_two_stores.php
     */
    public function testNonDefaultWebsiteAvailableStoreConfigs(): void
    {
        $storeConfigs = $this->storeConfigManager->getStoreConfigs(['fixture_second_store', 'fixture_third_store']);

        $query
            = <<<QUERY
{
  availableStores {
    id,
    code,
    website_id,
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
        $headerMap = ['Store' => 'fixture_second_store'];
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        $this->assertArrayHasKey('availableStores', $response);
        foreach ($storeConfigs as $key => $storeConfig) {
            $this->validateStoreConfig($storeConfig, $response['availableStores'][$key]);
        }
    }

    /**
     * Validate Store Config Data
     *
     * @param StoreConfigInterface $storeConfig
     * @param array $responseConfig
     */
    private function validateStoreConfig(StoreConfigInterface $storeConfig, array $responseConfig): void
    {
        $store = $this->objectManager->get(Store::class);
        $this->storeResource->load($store, $storeConfig->getCode(), 'code');
        $this->assertEquals($storeConfig->getId(), $responseConfig['id']);
        $this->assertEquals($storeConfig->getCode(), $responseConfig['code']);
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
        $this->assertEquals($store->getName(), $responseConfig['store_name']);
    }
}
