<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Controller;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractController;

class QuickSearchTest extends AbstractController
{
    /**
     * Tests quick search with "Price Navigation Step Calculation" sets to "Automatic (equalize product counts)".
     *
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @magentoConfigFixture fixturestore_store catalog/layered_navigation/price_range_calculation improved
     * @magentoConfigFixture fixturestore_store catalog/layered_navigation/one_price_interval 1
     * @magentoConfigFixture fixturestore_store catalog/layered_navigation/interval_division_limit 1
     * @magentoDataFixture Magento/Catalog/_files/products_for_search.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     */
    public function testQuickSearchWithImprovedPriceRangeCalculation()
    {
        $storeManager = $this->_objectManager->get(StoreManagerInterface::class);

        $secondStore = $storeManager->getStore('fixturestore');
        $storeManager->setCurrentStore($secondStore);

        try {
            $this->dispatch('/catalogsearch/result/?q=search+product');
            $responseBody = $this->getResponse()->getBody();
        } finally {
            $defaultStore = $storeManager->getStore('default');
            $storeManager->setCurrentStore($defaultStore);
        }

        $this->assertStringContainsString('search product 1', $responseBody);
    }

    /**
     * Tests quick search with "Minimum Terms to Match" sets to "100%".
     *
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/search/elasticsearch8_minimum_should_match 100%
     * @magentoConfigFixture current_store catalog/search/opensearch_minimum_should_match 100%
     * @magentoDataFixture Magento/Elasticsearch/_files/products_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     */
    public function testQuickSearchWithMinimumTermsToMatch()
    {
        $this->dispatch('/catalogsearch/result/?q=24+MB04');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString('search product 2', $responseBody);
        $this->assertStringNotContainsString('search product 1', $responseBody);
    }

    #[
        AppArea('frontend'),
        DbIsolation(false),
        Config('catalog/search/elasticsearch8_minimum_should_match', '100%', ScopeInterface::SCOPE_STORE, 'default'),
        Config('catalog/search/opensearch_minimum_should_match', '100%', ScopeInterface::SCOPE_STORE, 'default'),
        DataFixture(
            SelectAttributeFixture::class,
            [
                'is_searchable' => true,
                'options' => ['black', 'gray']
            ],
            'fabric_color'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Pullover Hoodie',
                '$fabric_color.attribute_code$' => '$fabric_color.black$'
            ],
            'hoodie'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Gym Jacket',
                '$fabric_color.attribute_code$' => '$fabric_color.black$'
            ],
            'jacket'
        ),
        DataFixture('Magento/CatalogSearch/_files/full_reindex.php'),
    ]
    /**
     * Tests that search result will return a product if query matches across searchable fields.
     *
     * In this test, we set "Minimum Terms to Match" to "100%", which means that all terms in the search query
     * must be matched for a product to be returned in search results.
     * Then, we search for "black hoodie" which does not fully match either the "name" or "fabric color" field.
     * The first term "black" matches the "fabric color" field and the second term "hoodie" matches the "name" field
     * of the "Pullover Hoodie" product.
     * Therefore, the expected behavior is that the search returns the "Pullover Hoodie" product
     */
    public function testQuickSearchAcrossSearchableFields(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $hoodie = $fixtures->get('hoodie');
        $jacket = $fixtures->get('jacket');
        $this->dispatch('/catalogsearch/result/?q=black+hoodie');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString($hoodie->getName(), $responseBody);
        $this->assertStringNotContainsString($jacket->getName(), $responseBody);
    }

    #[
        AppArea('frontend'),
        DbIsolation(false),
        Config('catalog/search/elasticsearch8_minimum_should_match', '100%', ScopeInterface::SCOPE_STORE, 'default'),
        Config('catalog/search/opensearch_minimum_should_match', '100%', ScopeInterface::SCOPE_STORE, 'default'),
        DataFixture(
            SelectAttributeFixture::class,
            [
                // Makes sure just because a field is filterable doesn't mean it's searchable
                'is_filterable' => true,
                'is_filterable_in_search' => true,
                'is_searchable' => false,
                'options' => ['black', 'gray']
            ],
            'fabric_color'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Pullover Hoodie',
                '$fabric_color.attribute_code$' => '$fabric_color.black$'
            ],
            'hoodie'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Gym Jacket',
                '$fabric_color.attribute_code$' => '$fabric_color.black$'
            ],
            'jacket'
        ),
        DataFixture('Magento/CatalogSearch/_files/full_reindex.php'),
    ]
    /**
     * Tests that search result will NOT return a product when query matches across non-searchable fields.
     *
     * In this test, we set "Minimum Terms to Match" to "100%", which means that all terms in the search query
     * must be matched for a product to be returned in search results.
     * Then, we search for "black hoodie" which does not fully match either the "name" or "fabric color" field.
     * The first term "black" matches the "fabric color" field and the second term "hoodie" matches the "name" field
     * of the "Pullover Hoodie" product.
     * However, since the "fabric color" attribute is not searchable, only the "hoodie" term is considered for matching.
     * Therefore, the expected behavior is that no products are returned in the search results.
     */
    public function testQuickSearchAcrossNonSearchableFields(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $hoodie = $fixtures->get('hoodie');
        $jacket = $fixtures->get('jacket');
        $this->dispatch('/catalogsearch/result/?q=black+hoodie');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringNotContainsString($hoodie->getName(), $responseBody);
        $this->assertStringNotContainsString($jacket->getName(), $responseBody);
    }
}
