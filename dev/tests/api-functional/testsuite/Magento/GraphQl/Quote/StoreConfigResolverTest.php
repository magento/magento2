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
    private const MAX_ITEMS_TO_DISPLAY = 5;
    private const CART_SUMMARY_DISPLAY_TOTAL = 1;
    private const MINICART_MAX_ITEMS = 10;
    private const CART_EXPIRES_IN_DAYS = 5;

    #[
        ConfigFixture(Data::XML_PATH_GUEST_CHECKOUT, true, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('checkout/options/onepage_checkout_enabled', true, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('checkout/options/max_items_display_count', self::MAX_ITEMS_TO_DISPLAY),
        ConfigFixture('checkout/cart_link/use_qty', 1, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('checkout/sidebar/display', true, ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture(
            'checkout/sidebar/max_items_display_count',
            self::MINICART_MAX_ITEMS,
            ScopeInterface::SCOPE_STORE,
            'default'
        ),
        ConfigFixture(
            'checkout/cart/delete_quote_after',
            self::CART_EXPIRES_IN_DAYS,
            ScopeInterface::SCOPE_STORE,
            'default'
        ),
    ]
    public function testGetStoreConfig(): void
    {
        $query
            = <<<QUERY
{
  storeConfig {
    is_guest_checkout_enabled,
    is_one_page_checkout_enabled,
    max_items_in_order_summary,
    cart_summary_display_quantity,
    minicart_display,
    minicart_max_items,
    cart_expires_in_days
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
        $this->assertEquals(self::MAX_ITEMS_TO_DISPLAY, $responseConfig['max_items_in_order_summary']);
        $this->assertEquals(self::CART_SUMMARY_DISPLAY_TOTAL, $responseConfig['cart_summary_display_quantity']);
        $this->assertTrue($responseConfig['minicart_display']);
        $this->assertEquals(self::MINICART_MAX_ITEMS, $responseConfig['minicart_max_items']);
        $this->assertEquals(self::CART_EXPIRES_IN_DAYS, $responseConfig['cart_expires_in_days']);
    }
}
