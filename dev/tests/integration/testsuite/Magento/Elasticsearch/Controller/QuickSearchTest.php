<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Controller;

use Magento\Store\Model\StoreManagerInterface;
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
     * @magentoConfigFixture current_store catalog/search/elasticsearch7_minimum_should_match 100%
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
}
