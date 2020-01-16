<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\Controller;

use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Tests quick search on Storefront.
 */
class QuickSearchTest extends AbstractController
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Tests quick search with "Price Navigation Step Calculation" sets to "Automatic (equalize product counts)".
     *
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @magentoConfigFixture fixturestore_store catalog/layered_navigation/price_range_calculation improved
     * @magentoConfigFixture fixturestore_store catalog/layered_navigation/one_price_interval 1
     * @magentoConfigFixture fixturestore_store catalog/layered_navigation/interval_division_limit 1
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoConfigFixture default_store catalog/search/elasticsearch6_index_prefix storefront_quick_search
     * @magentoConfigFixture fixturestore_store catalog/search/elasticsearch6_index_prefix storefront_quick_search
     * @magentoDataFixture Magento/Catalog/_files/products_for_search.php
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     */
    public function testQuickSearchWithImprovedPriceRangeCalculation()
    {
        $secondStore = $this->storeManager->getStore('fixturestore');
        $this->storeManager->setCurrentStore($secondStore);

        try {
            $this->dispatch('/catalogsearch/result/?q=search+product');
            $responseBody = $this->getResponse()->getBody();
        } finally {
            $defaultStore = $this->storeManager->getStore('default');
            $this->storeManager->setCurrentStore($defaultStore);
        }

        $this->assertContains('search product 1', $responseBody);
    }
}
