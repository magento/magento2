<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Model;

use Magento\Search\Model\Query;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SuggestedQueriesTest extends TestCase
{
    /**
     * @var SuggestedQueries
     */
    private $suggestedQueries;

    protected function setUp(): void
    {
        $this->suggestedQueries = Bootstrap::getObjectManager()
            ->create(SuggestedQueries::class);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento\Catalog\Test\Fixture\Product with:{"name":"fresh arugula salad"}
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix suggested_queries_test
     * @magentoConfigFixture current_store catalog/search/search_suggestion_enabled 1
     * @magentoConfigFixture current_store catalog/search/search_suggestion_count 8
     */
    public function testGetItems(): void
    {
        $query = Bootstrap::getObjectManager()
            ->create(Query::class, ['data' => ['query_text' => 'frshe arugul salat']]);
        $queryResults = $this->suggestedQueries->getItems($query);
        $queryTexts = [];
        foreach ($queryResults as $queryResult) {
            $queryTexts[] = $queryResult->getQueryText();
        }
        self::assertCount(7, $queryTexts);
        self::assertEquals('fresh arugula salad', $queryTexts[0]);
    }
}
