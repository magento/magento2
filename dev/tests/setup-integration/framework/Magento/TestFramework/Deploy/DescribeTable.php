<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL\DbSchemaReader;

/**
 * The purpose of this class is adding test modules files to Magento code base
 */
class DescribeTable
{
    /**
     * @var DbSchemaReader
     */
    private $dbSchemaReader;

    /**
     * @param DbSchemaReader $dbSchemaReader
     */
    public function __construct(DbSchemaReader $dbSchemaReader)
    {
        $this->dbSchemaReader = $dbSchemaReader;
    }

    /**
     * Describe shards
     *
     * @param  string $shardName
     * @return array
     */
    public function describeShard($shardName)
    {
        $data = [];
        $tables = $this->dbSchemaReader->readTables($shardName);

        foreach ($tables as $table) {
            $data[$table] = $this->dbSchemaReader->getCreateTableSql($table, $shardName)['Create Table'];
        }

        return $data;
    }
}
