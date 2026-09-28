<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Render\PriceTypes;

use PHPUnit\Framework\Attributes\DataProvider;

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
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $tierData
     * @return void
     */
    #[DataProvider('tierPricesForAllCustomerGroupsDataProvider')]
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
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $tierData
     * @return void
     */
    #[DataProvider('tierPricesForLoggedCustomerGroupDataProvider')]
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
     * @param float $specialPrice
     * @param float $regularPrice
     * @param array $catalogRules
     * @param array $tierData
     * @return void
     */
    #[DataProvider('catalogRulesDataProvider')]
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
     * @param float $optionPrice
     * @param array $productPrices
     * @return void
     */
    #[DataProvider('percentCustomOptionsDataProvider')]
    public function testRenderSpecialPriceInCombinationWithCustomOptionPrice(
        float $optionPrice,
        array $productPrices
    ): void {
        $this->assertRenderedCustomOptionPrices('simple', $optionPrice, $productPrices);
    }
}
