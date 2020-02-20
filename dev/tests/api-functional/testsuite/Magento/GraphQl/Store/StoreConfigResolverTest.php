<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's StoreConfigs query
 */
class StoreConfigResolverTest extends GraphQlAbstract
{

    /** @var  ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default_store store/information/name Test Store
     */
    public function testGetStoreConfig()
    {
        /** @var  StoreConfigManagerInterface $storeConfigsManager */
        $storeConfigsManager = $this->objectManager->get(StoreConfigManagerInterface::class);
        /** @var StoreResolverInterface $storeResolver */
        $storeResolver = $this->objectManager->get(StoreResolverInterface::class);
        /** @var StoreRepositoryInterface $storeRepository */
        $storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
        $storeId = $storeResolver->getCurrentStoreId();
        $store = $storeRepository->getById($storeId);
        /** @var StoreConfigInterface $storeConfig */
        $storeConfig = current($storeConfigsManager->getStoreConfigs([$store->getCode()]));
        $query
            = <<<QUERY
{
  storeConfig{
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
        $this->assertEquals($storeConfig->getId(), $response['storeConfig']['id']);
        $this->assertEquals($storeConfig->getCode(), $response['storeConfig']['code']);
        $this->assertEquals($storeConfig->getLocale(), $response['storeConfig']['locale']);
        $this->assertEquals($storeConfig->getBaseCurrencyCode(), $response['storeConfig']['base_currency_code']);
        $this->assertEquals(
            $storeConfig->getDefaultDisplayCurrencyCode(),
            $response['storeConfig']['default_display_currency_code']
        );
        $this->assertEquals($storeConfig->getTimezone(), $response['storeConfig']['timezone']);
        $this->assertEquals($storeConfig->getWeightUnit(), $response['storeConfig']['weight_unit']);
        $this->assertEquals($storeConfig->getBaseUrl(), $response['storeConfig']['base_url']);
        $this->assertEquals($storeConfig->getBaseLinkUrl(), $response['storeConfig']['base_link_url']);
        $this->assertEquals($storeConfig->getBaseStaticUrl(), $response['storeConfig']['base_static_url']);
        $this->assertEquals($storeConfig->getBaseMediaUrl(), $response['storeConfig']['base_media_url']);
        $this->assertEquals($storeConfig->getSecureBaseUrl(), $response['storeConfig']['secure_base_url']);
        $this->assertEquals($storeConfig->getSecureBaseLinkUrl(), $response['storeConfig']['secure_base_link_url']);
        $this->assertEquals(
            $storeConfig->getSecureBaseStaticUrl(),
            $response['storeConfig']['secure_base_static_url']
        );
        $this->assertEquals($storeConfig->getSecureBaseMediaUrl(), $response['storeConfig']['secure_base_media_url']);
        $this->assertEquals('Test Store', $response['storeConfig']['store_name']);
    }
}
