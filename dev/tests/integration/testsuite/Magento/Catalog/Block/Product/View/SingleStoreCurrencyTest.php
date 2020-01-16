<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     *
     * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
     * @magentoDataFixture Magento/Directory/_files/usd_euro_rate.php
     *
     * @return void
     */
    public function testRenderPrice(): void
    {
        $priceHtml = $this->processPriceView('simple2');
        $this->assertEquals('€7.00', $priceHtml);
    }

    /**
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoDataFixture Magento/Directory/_files/usd_euro_rate.php
     *
     * @return void
     */
    public function testRenderSpecialPrice(): void
    {
        $priceHtml = $this->processPriceView('simple');
        $this->assertEquals('Special Price €4.19 Regular Price €7.00', $priceHtml);
    }

    /**
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/default EUR
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_fixed_tier_price.php
     * @magentoDataFixture Magento/Directory/_files/usd_euro_rate.php
     *
     * @return void
     */
    public function testRenderTierPrice(): void
    {
        $priceHtml = $this->processPriceView('simple-product-tax-none', true);
        $this->assertContains('Buy 2 for €28.00', $priceHtml);
    }
}
