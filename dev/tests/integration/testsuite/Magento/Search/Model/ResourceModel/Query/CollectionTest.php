<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Model\ResourceModel\Query;

use Magento\Search\Model\ResourceModel\Query;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Query
     */
    private $queryResource;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->queryResource = $objectManager->get(Query::class);
    }

    public function testSearchQueryTableHasProperIndex()
    {
        $table = $this->queryResource->getTable('search_query');
        $indexName = 'SEARCH_QUERY_STORE_ID_NUM_RESULTS_POPULARITY';
        $connection = $this->queryResource->getConnection();
        $tableIndexes = $connection->getIndexList($table);
        $this->assertArrayHasKey($indexName, $tableIndexes);
    }
}
