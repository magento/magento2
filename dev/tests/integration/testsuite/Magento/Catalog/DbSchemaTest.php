<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog;

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

    /**
     * @return array[]
     */
    public static function indexDataProvider(): array
    {
        return [
            [
                'catalog_product_index_price_tmp',
                'CAT_PRD_IDX_PRICE_TMP_ENTT_ID_CSTR_GROUP_ID_WS_ID',
                ['entity_id', 'customer_group_id', 'website_id']
            ]
        ];
    }
}
