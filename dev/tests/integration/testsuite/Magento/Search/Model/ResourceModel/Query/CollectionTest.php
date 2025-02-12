<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Model\ResourceModel\Query;

use Magento\Search\Model\ResourceModel\Query;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Query
     */
    private Query $queryResource;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->queryResource = $objectManager->get(Query::class);
    }

    /**
     * @return void
     */
    public function testSearchQueryTableHasProperIndex(): void
    {
        $table = $this->queryResource->getTable('search_query');
        $indexQueryStoreNumPopularity = 'SEARCH_QUERY_STORE_ID_NUM_RESULTS_POPULARITY';
        $indexQueryTextStoreNumPopularity = 'SEARCH_QUERY_QUERY_TEXT_STORE_ID_NUM_RESULTS_POPULARITY';
        $connection = $this->queryResource->getConnection();
        $tableIndexes = $connection->getIndexList($table);
        $this->assertArrayHasKey($indexQueryStoreNumPopularity, $tableIndexes);
        $this->assertArrayHasKey($indexQueryTextStoreNumPopularity, $tableIndexes);
    }
}
