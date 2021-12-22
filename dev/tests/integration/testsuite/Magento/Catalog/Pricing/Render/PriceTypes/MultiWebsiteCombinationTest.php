<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Pricing\Render\PriceTypes;

use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\Store\ExecuteInStoreContext;

/**
 * Assertions related to check product price rendering with combination of different price types on second website.
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea frontend
 */
class MultiWebsiteCombinationTest extends CombinationAbstract
{
    /**
     * @var ExecuteInStoreContext
     */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
    }

    /**
     * Assert that product price rendered with expected special and regular prices if
     * product has special price which lower than regular and tier prices on second website.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_price_on_second_website.php
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
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'assertRenderedPrices'],
            'second-website-price-product',
            $specialPrice,
            $regularPrice,
            $tierData,
            (int)$this->storeManager->getStore('fixture_second_store')->getWebsiteId()
        );
        $this->assertRenderedPricesOnDefaultStore('second-website-price-product');
    }

    /**
     * Assert that product price rendered with expected special and regular prices  on second website if
     * product has special price which lower than regular and tier prices and customer is logged.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_price_on_second_website.php
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
            $this->executeInStoreContext->execute(
                'fixture_second_store',
                [$this, 'assertRenderedPrices'],
                'second-website-price-product',
                $specialPrice,
                $regularPrice,
                $tierData,
                (int)$this->storeManager->getStore('fixture_second_store')->getWebsiteId()
            );
            $this->assertRenderedPricesOnDefaultStore('second-website-price-product');
        } finally {
            $this->customerSession->setCustomerId(null);
        }
    }

    /**
     * Assert that product price rendered with expected special and regular prices if
     * product has catalog rule price with different type of prices on second website.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_price_on_second_website.php
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
        $this->createCatalogRulesForProduct($catalogRules, 'test');
        $this->indexBuilder->reindexFull();
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'assertRenderedPrices'],
            'second-website-price-product',
            $specialPrice,
            $regularPrice,
            $tierData,
            (int)$this->storeManager->getStore('fixture_second_store')->getWebsiteId()
        );
        $this->assertRenderedPricesOnDefaultStore('second-website-price-product');
    }

    /**
     * Assert that product price rendered with expected custom option price if product has special price.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_price_on_second_website.php
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
        $this->executeInStoreContext->execute(
            'fixture_second_store',
            [$this, 'assertRenderedCustomOptionPrices'],
            'second-website-price-product',
            $optionPrice,
            $productPrices
        );
        $this->assertRenderedCustomOptionPricesOnDefaultStore('second-website-price-product');
    }

    /**
     * Checks price data for product on default store.
     *
     * @param string $sku
     * @return void
     */
    private function assertRenderedPricesOnDefaultStore(string $sku): void
    {
        //Reset layout page to get new block html
        $this->page = $this->objectManager->get(PageFactory::class)->create();
        $defaultStoreTierData = ['prices' => [], 'message_config' => null];
        $this->assertRenderedPrices($sku, 15, 20, $defaultStoreTierData);
    }

    /**
     * Checks custom option price data for product on default store.
     *
     * @param string $sku
     * @return void
     */
    private function assertRenderedCustomOptionPricesOnDefaultStore(string $sku): void
    {
        //Reset layout page to get new block html
        $this->page = $this->objectManager->get(PageFactory::class)->create();
        $this->assertRenderedCustomOptionPrices($sku, 7.5, []);
    }
}
