<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
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
        ConfigFixture('checkout/cart/grouped_product_image', 'parent', ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('checkout/cart/configurable_product_image', 'itself', ScopeInterface::SCOPE_STORE, 'default'),
        ConfigFixture('checkout/options/enable_agreements', true, ScopeInterface::SCOPE_STORE, 'default')
    ]
    public function testGetStoreConfig(): void
    {
        $this->assertEquals(
            [
              'storeConfig' => [
                  'is_guest_checkout_enabled' => true,
                  'is_one_page_checkout_enabled' => true,
                  'max_items_in_order_summary' => self::MAX_ITEMS_TO_DISPLAY,
                  'cart_summary_display_quantity' => self::CART_SUMMARY_DISPLAY_TOTAL,
                  'minicart_display' => true,
                  'minicart_max_items' => self::MINICART_MAX_ITEMS,
                  'cart_expires_in_days' => self::CART_EXPIRES_IN_DAYS,
                  'grouped_product_image' => 'PARENT',
                  'configurable_product_image' => 'ITSELF',
                  'is_checkout_agreements_enabled' => true,
              ],
            ],
            $this->graphQlQuery($this->getStoreConfigQuery())
        );
    }

    /**
     * Generates storeConfig query
     *
     * @return string
     */
    private function getStoreConfigQuery(): string
    {
        return <<<QUERY
            {
              storeConfig {
                is_guest_checkout_enabled
                is_one_page_checkout_enabled
                max_items_in_order_summary
                cart_summary_display_quantity
                minicart_display
                minicart_max_items
                cart_expires_in_days
                grouped_product_image
                configurable_product_image
                is_checkout_agreements_enabled
              }
            }
        QUERY;
    }
}
