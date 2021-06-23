<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Checks currency displaying and converting on the catalog pages on multi store mode
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation disabled
 */
class MultiStoreCurrencyTest extends AbstractCurrencyTest
{
    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var ResolverInterface */
    private $localeResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->localeResolver = $this->objectManager->get(ResolverInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default CNY
     * @magentoConfigFixture current_store currency/options/allow CNY,USD
     * @magentoConfigFixture fixturestore_store currency/options/default UAH
     * @magentoConfigFixture fixturestore_store currency/options/allow UAH,USD
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_uah_rate.php
     *
     * @return void
     */
    public function testMultiStoreRenderPrice(): void
    {
        $this->localeResolver->setLocale('zh_CN');
        $this->assertProductStorePrice('simple2', '¥70.00');

        $this->reloadProductPriceInfo();
        $this->localeResolver->setLocale('uk_UA');
        $this->assertProductStorePrice('simple2', '240,00 ₴', 'fixturestore');
    }

    /**
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default CNY
     * @magentoConfigFixture current_store currency/options/allow CNY,USD
     * @magentoConfigFixture fixturestore_store currency/options/default UAH
     * @magentoConfigFixture fixturestore_store currency/options/allow UAH,USD
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_uah_rate.php
     *
     * @return void
     */
    public function testMultiStoreRenderSpecialPrice(): void
    {
        $this->localeResolver->setLocale('zh_CN');
        $this->assertProductStorePrice('simple', 'Special Price ¥41.93 Regular Price ¥70.00');

        $this->reloadProductPriceInfo();
        $this->localeResolver->setLocale('uk_UA');
        $this->assertProductStorePrice('simple', 'Special Price 143,76 ₴ Regular Price 240,00 ₴', 'fixturestore');
    }

    /**
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default CNY
     * @magentoConfigFixture current_store currency/options/allow CNY,USD
     * @magentoConfigFixture fixturestore_store currency/options/default UAH
     * @magentoConfigFixture fixturestore_store currency/options/allow UAH,USD
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_fixed_tier_price.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_uah_rate.php
     *
     * @return void
     */
    public function testMultiStoreRenderTierPrice(): void
    {
        $this->localeResolver->setLocale('zh_CN');
        $this->assertProductStorePrice(
            'simple-product-tax-none',
            'Buy 2 for ¥280.00 each and save 80%',
            'default',
            self::TIER_PRICE_BLOCK_NAME
        );

        $this->reloadProductPriceInfo();
        $this->localeResolver->setLocale('uk_UA');
        $this->assertProductStorePrice(
            'simple-product-tax-none',
            'Buy 2 for 960,00 ₴ each and save 80%',
            'fixturestore',
            self::TIER_PRICE_BLOCK_NAME
        );
    }

    /**
     * Check price per stores
     *
     * @param string $productSku
     * @param string $expectedData
     * @param string $storeCode
     * @param string $priceBlockName
     * @return void
     */
    private function assertProductStorePrice(
        string $productSku,
        string $expectedData,
        string $storeCode = 'default',
        string $priceBlockName = self::FINAL_PRICE_BLOCK_NAME
    ): void {
        $currentStore = $this->storeManager->getStore();
        try {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($storeCode);
            }

            $actualData = $this->processPriceView($productSku, $priceBlockName);
            self::assertEquals($expectedData, $actualData);
        } finally {
            if ($currentStore->getCode() !== $storeCode) {
                $this->storeManager->setCurrentStore($currentStore);
            }
        }
    }

    /**
     * Reload product price info
     *
     * @return void
     */
    private function reloadProductPriceInfo(): void
    {
        $product = $this->registry->registry('product');
        self::assertNotNull($product);
        $product->reloadPriceInfo();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->localeResolver->setLocale(\Magento\Setup\Module\I18n\Locale::DEFAULT_SYSTEM_LOCALE);
        parent::tearDown();
    }
}
