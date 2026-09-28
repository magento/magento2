<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View;

/**
 * Checks currency displaying and converting on the catalog pages
 *
 * @magentoAppArea frontend
 */
class SingleStoreCurrencyTest extends AbstractCurrencyTest
{
    /**
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default CNY
     * @magentoConfigFixture current_store currency/options/allow CNY,USD
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     *
     * @return void
     */
    public function testRenderPrice(): void
    {
        $priceHtml = $this->processPriceView('simple2');
        $this->assertEquals('CN¥70.00', $priceHtml);
    }

    /**
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default CNY
     * @magentoConfigFixture current_store currency/options/allow EUR,CNY
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     *
     * @return void
     */
    public function testRenderSpecialPrice(): void
    {
        $priceHtml = $this->processPriceView('simple');
        $this->assertEquals('Special Price CN¥41.93 Regular Price CN¥70.00', $priceHtml);
    }

    /**
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default CNY
     * @magentoConfigFixture current_store currency/options/allow CNY,USD
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_fixed_tier_price.php
     * @magentoDataFixture Magento/Directory/_files/usd_cny_rate.php
     *
     * @return void
     */
    public function testRenderTierPrice(): void
    {
        $priceHtml = $this->processPriceView('simple-product-tax-none', self::TIER_PRICE_BLOCK_NAME);
        $this->assertEquals('Buy 2 for CN¥280.00 each and save 80%', $priceHtml);
    }
}
