<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Search\Model\Query;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Fixture\Config as FixtureConfig;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
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

    #[
        DbIsolation(false),
        FixtureConfig(
            'catalog/search/elasticsearch_index_prefix',
            'suggested_queries_test',
            ScopeInterface::SCOPE_STORE
        ),
        FixtureConfig(SuggestedQueriesInterface::SEARCH_SUGGESTION_ENABLED, 1, ScopeInterface::SCOPE_STORE),
        FixtureConfig(SuggestedQueriesInterface::SEARCH_SUGGESTION_COUNT, 8, ScopeInterface::SCOPE_STORE),
        DataFixture(ProductFixture::class, ['name' => 'fresh arugula salad']),
        DataFixture('Magento/CatalogSearch/_files/full_reindex.php'),
    ]
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
