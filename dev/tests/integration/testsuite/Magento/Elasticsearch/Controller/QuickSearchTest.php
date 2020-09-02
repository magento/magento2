<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Controller;

use Magento\TestFramework\TestCase\AbstractController;

class QuickSearchTest extends AbstractController
{
    /**
     * Tests quick search with "Minimum Terms to Match" sets to "100%".
     *
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     * @magentoConfigFixture current_store catalog/search/elasticsearch7_minimum_should_match 100%
     * @magentoConfigFixture current_store catalog/search/elasticsearch6_minimum_should_match 100%
     * @magentoDataFixture Magento/Elasticsearch/_files/products_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     */
    public function testQuickSearchWithImprovedPriceRangeCalculation()
    {
        $this->dispatch('/catalogsearch/result/?q=24+MB04');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString('search product 2', $responseBody);
        $this->assertStringNotContainsString('search product 1', $responseBody);
    }
}
