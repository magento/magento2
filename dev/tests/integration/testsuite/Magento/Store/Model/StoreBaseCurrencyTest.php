<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Store::getBaseCurrencyCode() with multi-website currency configuration.
 *
 * @see https://github.com/magento/magento2/issues/40632
 * @magentoDbIsolation disabled
 */
class StoreBaseCurrencyTest extends TestCase
{
    /**
     * Verify that getBaseCurrencyCode() returns the website-scoped base currency
     * when catalog price scope is set to WEBSITE.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_second_base_currency.php
     * @magentoAppIsolation enabled
     */
    public function testGetBaseCurrencyCodeReturnsWebsiteCurrencyWhenPriceScopeIsWebsite(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore('fixture_second_store');

        $this->assertSame(
            'EUR',
            $store->getBaseCurrencyCode(),
            'Store should return website-scoped base currency (EUR) when price scope is WEBSITE'
        );
    }

    /**
     * Verify that getBaseCurrencyCode() returns the default base currency
     * when catalog price scope is set to GLOBAL, even if website has a different base currency.
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_second_base_currency.php
     * @magentoConfigFixture current_store catalog/price/scope 0
     * @magentoAppIsolation enabled
     */
    public function testGetBaseCurrencyCodeReturnsDefaultCurrencyWhenPriceScopeIsGlobal(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore('fixture_second_store');

        $this->assertSame(
            'USD',
            $store->getBaseCurrencyCode(),
            'Store should return default base currency (USD) when price scope is GLOBAL'
        );
    }

    /**
     * Verify that default store still returns the correct base currency
     * when price scope is WEBSITE (default website uses USD).
     *
     * @magentoDataFixture Magento/Store/_files/second_website_with_second_base_currency.php
     * @magentoAppIsolation enabled
     */
    public function testDefaultStoreRetainsBaseCurrencyWhenPriceScopeIsWebsite(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $defaultStore = $storeManager->getStore('default');

        $this->assertSame(
            'USD',
            $defaultStore->getBaseCurrencyCode(),
            'Default store should retain USD base currency when price scope is WEBSITE'
        );
    }
}
