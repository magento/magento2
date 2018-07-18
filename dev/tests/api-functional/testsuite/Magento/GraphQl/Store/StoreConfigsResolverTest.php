<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's StoreConfigs query
 */
class StoreConfigsResolverTest extends GraphQlAbstract
{

    /** @var  ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/store.php
     */
    public function testStoreConfigsFilteredByStoreCode()
    {
        $storeCode = 'test';
        /** @var  StoreConfigManagerInterface $storeConfigsManager */
        $storeConfigsManager = $this->objectManager->get(StoreConfigManagerInterface::class);
        /** @var StoreConfigInterface $storeConfig */
        $storeConfig = current($storeConfigsManager->getStoreConfigs([$storeCode]));
        $query
            = <<<QUERY
{
  storeConfigs(storeCodes: "{$storeCode}"){
      items{
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
    	secure_base_media_url
      }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('storeConfigs', $response);
        $this->assertEquals(1, count($response['storeConfigs']['items']));
        $responseStoreConfig = current($response['storeConfigs']['items']);
        $this->assertEquals($storeConfig->getId(), $responseStoreConfig['id']);
        $this->assertEquals($storeConfig->getCode(), $responseStoreConfig['code']);
        $this->assertEquals($storeConfig->getLocale(), $responseStoreConfig['locale']);
        $this->assertEquals($storeConfig->getBaseCurrencyCode(), $responseStoreConfig['base_currency_code']);
        $this->assertEquals(
            $storeConfig->getDefaultDisplayCurrencyCode(),
            $responseStoreConfig['default_display_currency_code']
        );
        $this->assertEquals($storeConfig->getTimezone(), $responseStoreConfig['timezone']);
        $this->assertEquals($storeConfig->getWeightUnit(), $responseStoreConfig['weight_unit']);
        $this->assertEquals($storeConfig->getBaseUrl(), $responseStoreConfig['base_url']);
        $this->assertEquals($storeConfig->getBaseLinkUrl(), $responseStoreConfig['base_link_url']);
        $this->assertEquals($storeConfig->getBaseStaticUrl(), $responseStoreConfig['base_static_url']);
        $this->assertEquals($storeConfig->getBaseMediaUrl(), $responseStoreConfig['base_media_url']);
        $this->assertEquals($storeConfig->getSecureBaseUrl(), $responseStoreConfig['secure_base_url']);
        $this->assertEquals($storeConfig->getSecureBaseLinkUrl(), $responseStoreConfig['secure_base_link_url']);
        $this->assertEquals($storeConfig->getSecureBaseStaticUrl(), $responseStoreConfig['secure_base_static_url']);
        $this->assertEquals($storeConfig->getSecureBaseMediaUrl(), $responseStoreConfig['secure_base_media_url']);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/store.php
     */
    public function testGetStoreConfigsWithoutStoreCodes()
    {
        $query
            = <<<QUERY
{
  storeConfigs{
      items{
        id
      }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('storeConfigs', $response);
        $this->assertEquals(2, count($response['storeConfigs']['items']));
    }
}
