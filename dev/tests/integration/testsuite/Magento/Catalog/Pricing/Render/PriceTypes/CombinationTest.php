<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Render\PriceTypes;

/**
 * Assertions related to check product price rendering with combination of different price types.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 */
class CombinationTest extends CombinationAbstract
{
    /**
     * Assert that product price rendered with expected special and regular prices if
     * product has special price which lower than regular and tier prices.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     *
     * @dataProvider tierPricesForAllCustomerGroupsDataProvider
     *
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $tierData
     * @return void
     */
    public function testRenderSpecialPriceInCombinationWithTierPrice(
        float $specialPrice,
        float $regularPrice,
        array $tierData
    ): void {
        $this->assertRenderedPrices('simple', $specialPrice, $regularPrice, $tierData);
    }

    /**
     * Assert that product price rendered with expected special and regular prices if
     * product has special price which lower than regular and tier prices and customer is logged.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @magentoAppIsolation enabled
     *
     * @dataProvider tierPricesForLoggedCustomerGroupDataProvider
     *
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $tierData
     * @return void
     */
    public function testRenderSpecialPriceInCombinationWithTierPriceForLoggedInUser(
        float $specialPrice,
        float $regularPrice,
        array $tierData
    ): void {
        try {
            $this->customerSession->setCustomerId(1);
            $this->assertRenderedPrices('simple', $specialPrice, $regularPrice, $tierData);
        } finally {
            $this->customerSession->setCustomerId(null);
        }
    }

    /**
     * Assert that product price rendered with expected special and regular prices if
     * product has catalog rule price with different type of prices.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoDataFixture Magento/CatalogRule/_files/delete_catalog_rule_data.php
     *
     * @dataProvider catalogRulesDataProvider
     *
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $catalogRules
     * @param array $tierData
     * @return void
     */
    public function testRenderCatalogRulePriceInCombinationWithDifferentPriceTypes(
        float $specialPrice,
        float $regularPrice,
        array $catalogRules,
        array $tierData
    ): void {
        $this->createCatalogRulesForProduct($catalogRules, 'base');
        $this->indexBuilder->reindexFull();
        $this->assertRenderedPrices('simple', $specialPrice, $regularPrice, $tierData);
    }

    /**
     * Assert that product price rendered with expected custom option price if product has special price.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     *
     * @dataProvider percentCustomOptionsDataProvider
     *
     * @param float $optionPrice
     * @param array $productPrices
     * @return void
     */
    public function testRenderSpecialPriceInCombinationWithCustomOptionPrice(
        float $optionPrice,
        array $productPrices
    ): void {
        $this->assertRenderedCustomOptionPrices('simple', $optionPrice, $productPrices);
    }
}
