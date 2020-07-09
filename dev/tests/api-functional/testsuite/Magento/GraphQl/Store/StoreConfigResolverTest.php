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
        /** @var StoreConfigInterface $defaultStoreConfig */
        $defaultStoreConfig = current($storeConfigManager->getStoreConfigs([$store->getCode()]));
        $query
            = <<<QUERY
{
  storeConfig {
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
        $this->assertArrayHasKey('storeConfig', $response);
        $this->validateStoreConfig($defaultStoreConfig, $response['storeConfig'], $store->getName());
    }

    /**
     * Validate Store Config Data
     *
     * @param StoreConfigInterface $storeConfig
     * @param array $responseConfig
     * @param string $storeName
     */
    private function validateStoreConfig(
        StoreConfigInterface $storeConfig,
        array $responseConfig,
        string $storeName
    ): void {
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
        $this->assertEquals($storeName, $responseConfig['store_name']);
    }
}
