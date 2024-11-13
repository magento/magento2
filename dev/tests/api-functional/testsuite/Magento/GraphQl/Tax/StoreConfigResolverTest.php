<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Tax;

use Magento\Directory\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Tax\Model\Config;

/**
 * Test the GraphQL endpoint's StoreConfigs query
 */
class StoreConfigResolverTest extends GraphQlAbstract
{
    #[
        ConfigFixture(Config::XML_PATH_DISPLAY_CART_PRICE, 1, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture(Config::XML_PATH_DISPLAY_CART_SHIPPING, 1, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture(Config::XML_PATH_DISPLAY_CART_SUBTOTAL, 1, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture(Config::XML_PATH_DISPLAY_CART_GRANDTOTAL, 1, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture(Config::XML_PATH_DISPLAY_CART_FULL_SUMMARY, 1, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture(Config::XML_PATH_DISPLAY_CART_ZERO_TAX, 1, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('tax/cart_display/gift_wrapping', 3, ScopeInterface::SCOPE_STORE, 'default'),
    ]
    public function testGetStoreConfig(): void
    {
        $query
            = <<<QUERY
{
  storeConfig {
    shopping_cart_display_price,
    shopping_cart_display_shipping,
    shopping_cart_display_subtotal,
    shopping_cart_display_grand_total,
    shopping_cart_display_full_summary,
    shopping_cart_display_zero_tax,
    shopping_cart_display_tax_gift_wrapping,
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
        $this->assertEquals(1, $responseConfig['shopping_cart_display_price']);
        $this->assertEquals(1, $responseConfig['shopping_cart_display_shipping']);
        $this->assertEquals(1, $responseConfig['shopping_cart_display_subtotal']);
        $this->assertEquals(1, $responseConfig['shopping_cart_display_grand_total']);
        $this->assertEquals(1, $responseConfig['shopping_cart_display_full_summary']);
        $this->assertEquals(1, $responseConfig['shopping_cart_display_zero_tax']);
        $this->assertEquals('DISPLAY_TYPE_BOTH', $responseConfig['shopping_cart_display_tax_gift_wrapping']);
    }
}
