<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Monolog\Test\TestCase;

class DbSchemaTest extends TestCase
{
    /**
     * @param string $tableName
     * @param string $indexName
     * @param array $columns
     * @param string $indexType
     * @return void
     * @dataProvider indexDataProvider
     */
    public function testIndex(
        string $tableName,
        string $indexName,
        array $columns,
        string $indexType = AdapterInterface::INDEX_TYPE_INDEX,
    ): void {
        $connection = ObjectManager::getInstance()->get(ResourceConnection::class)->getConnection();
        $indexes = $connection->getIndexList($tableName);
        $this->assertArrayHasKey($indexName, $indexes);
        $this->assertSame($columns, $indexes[$indexName]['COLUMNS_LIST']);
        $this->assertSame($indexType, $indexes[$indexName]['INDEX_TYPE']);
    }

    public function indexDataProvider(): array
    {
        return [
            [
                'quote',
                'QUOTE_STORE_ID_UPDATED_AT',
                ['store_id', 'updated_at']
            ]
        ];
    }
}
