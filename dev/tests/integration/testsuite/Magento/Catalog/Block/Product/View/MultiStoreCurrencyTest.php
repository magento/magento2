<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View;

use Magento\Catalog\Api\Data\ProductInterface;
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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     * @magentoConfigFixture fixturestore_store currency/options/default UAH
     * @magentoConfigFixture fixturestore_store currency/options/allow UAH,USD
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Directory/_files/usd_euro_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_uah_rate.php
     *
     * @return void
     */
    public function testMultiStoreRenderPrice(): void
    {
        $this->checkMultiStorePriceView('simple2', '€7.00', '₴240.00');
    }

    /**
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     * @magentoConfigFixture fixturestore_store currency/options/default UAH
     * @magentoConfigFixture fixturestore_store currency/options/allow UAH,USD
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoDataFixture Magento/Directory/_files/usd_euro_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_uah_rate.php
     *
     * @return void
     */
    public function testMultiStoreRenderSpecialPrice(): void
    {
        $this->checkMultiStorePriceView(
            'simple',
            'Special Price €4.19 Regular Price €7.00',
            'Special Price ₴143.76 Regular Price ₴240.00'
        );
    }

    /**
     * @magentoConfigFixture default/currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     * @magentoConfigFixture fixturestore_store currency/options/default UAH
     * @magentoConfigFixture fixturestore_store currency/options/allow UAH,USD
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_fixed_tier_price.php
     * @magentoDataFixture Magento/Directory/_files/usd_euro_rate.php
     * @magentoDataFixture Magento/Directory/_files/usd_uah_rate.php
     *
     * @return void
     */
    public function testMultiStoreRenderTierPrice(): void
    {
        $this->checkMultiStorePriceView(
            'simple-product-tax-none',
            'Buy 2 for €28.00 each and save 80%',
            'Buy 2 for ₴960.00 each and save 80%',
            true
        );
    }

    /**
     * Check currency displaying and converting per stores
     *
     * @param string $productSku
     * @param string $expectedDataCurrentStore
     * @param string $expectedDataSecondStore
     * @param bool $isTierPrice
     * @return void
     */
    private function checkMultiStorePriceView(
        string $productSku,
        string $expectedDataCurrentStore,
        string $expectedDataSecondStore,
        bool $isTierPrice = false
    ): void {
        $currentStore = $this->storeManager->getStore();
        $priceHtmlCurrentStore = $this->processPriceView($productSku, $isTierPrice);
        $this->assertEquals($expectedDataCurrentStore, $priceHtmlCurrentStore);
        try {
            $this->storeManager->setCurrentStore('fixturestore');
            $product = $this->reloadProductPriceInfo();
            $this->registerProduct($product);
            $priceHtmlSecondStore = $this->preparePriceHtml($isTierPrice);
            $this->assertEquals($expectedDataSecondStore, $priceHtmlSecondStore);
        } finally {
            $this->storeManager->setCurrentStore($currentStore);
        }
    }

    /**
     * Reload product price info
     *
     * @return ProductInterface
     */
    private function reloadProductPriceInfo(): ProductInterface
    {
        $product = $this->registry->registry('product');
        $product->reloadPriceInfo();

        return $product;
    }
}
