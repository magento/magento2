<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Model;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Store::getBaseCurrencyCode() with multi-website currency configuration.
 *
 * @see https://github.com/magento/magento2/issues/40632
 */
class StoreBaseCurrencyTest extends TestCase
{
    /**
     * Verify that getBaseCurrencyCode() returns the website-scoped base currency
     * when catalog price scope is set to WEBSITE.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(WebsiteFixture::class, ['code' => 'test_eur_website'], 'w1'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$w1.id$'], 'g1'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$g1.id$', 'code' => 'test_eur_store'], 's1'),
        ConfigFixture('catalog/price/scope', Store::PRICE_SCOPE_WEBSITE),
        ConfigFixture(Currency::XML_PATH_CURRENCY_BASE, 'EUR', ScopeInterface::SCOPE_WEBSITE, 'test_eur_website'),
    ]
    public function testGetBaseCurrencyCodeReturnsWebsiteCurrencyWhenPriceScopeIsWebsite(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore('test_eur_store');

        $this->assertSame(
            'EUR',
            $store->getBaseCurrencyCode(),
            'Store should return website-scoped base currency (EUR) when price scope is WEBSITE'
        );
    }

    /**
     * Verify that getBaseCurrencyCode() returns the default base currency
     * when catalog price scope is set to GLOBAL, even if website has a different base currency.
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(WebsiteFixture::class, ['code' => 'test_eur_website'], 'w1'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$w1.id$'], 'g1'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$g1.id$', 'code' => 'test_eur_store'], 's1'),
        ConfigFixture('catalog/price/scope', Store::PRICE_SCOPE_GLOBAL),
        ConfigFixture(Currency::XML_PATH_CURRENCY_BASE, 'EUR', ScopeInterface::SCOPE_WEBSITE, 'test_eur_website'),
    ]
    public function testGetBaseCurrencyCodeReturnsDefaultCurrencyWhenPriceScopeIsGlobal(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore('test_eur_store');

        $this->assertSame(
            'USD',
            $store->getBaseCurrencyCode(),
            'Store should return default base currency (USD) when price scope is GLOBAL'
        );
    }

    /**
     * Verify that default store still returns the correct base currency
     * when price scope is WEBSITE (default website uses USD).
     */
    #[
        DbIsolation(false),
        AppIsolation(true),
        DataFixture(WebsiteFixture::class, ['code' => 'test_eur_website'], 'w1'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$w1.id$'], 'g1'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$g1.id$', 'code' => 'test_eur_store'], 's1'),
        ConfigFixture('catalog/price/scope', Store::PRICE_SCOPE_WEBSITE),
        ConfigFixture(Currency::XML_PATH_CURRENCY_BASE, 'EUR', ScopeInterface::SCOPE_WEBSITE, 'test_eur_website'),
    ]
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
