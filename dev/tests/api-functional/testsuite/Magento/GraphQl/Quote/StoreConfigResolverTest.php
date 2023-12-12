<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Checkout\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's StoreConfigs query
 */
class StoreConfigResolverTest extends GraphQlAbstract
{
    #[
        ConfigFixture(Data::XML_PATH_GUEST_CHECKOUT, true, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('checkout/options/onepage_checkout_enabled', true, ScopeInterface::SCOPE_STORE, 'default'),
    ]
    public function testGetStoreConfig(): void
    {
        $query
            = <<<QUERY
{
  storeConfig {
    is_guest_checkout_enabled,
    is_one_page_checkout_enabled,
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('storeConfig', $response);
        $this->validateStoreConfig($response['storeConfig']);
    }

    /**
     * Validate Store Config Data
     *
     * @param array $responseConfig
     */
    private function validateStoreConfig(
        array $responseConfig,
    ): void {
        $this->assertTrue($responseConfig['is_guest_checkout_enabled']);
        $this->assertTrue($responseConfig['is_one_page_checkout_enabled']);
    }
}
