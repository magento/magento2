<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Deploy;

use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\DbSchemaReader;

/**
 * The purpose of this class is adding test modules files to Magento code base.
 */
class DescribeTable
{
    /**
     * Schema reader.
     *
     * @var DbSchemaReader
     */
    private $dbSchemaReader;

    /**
     * This registry is used to ignore some tables, during comparison
     *
     * @var array
     */
    private static $ignoredSystemTables = ['cache', 'cache_tag', 'flag', 'session', 'setup_module', 'patch_list'];

    /**
     * Constructor.
     *
     * @param DbSchemaReader $dbSchemaReader
     */
    public function __construct(DbSchemaReader $dbSchemaReader)
    {
        $this->dbSchemaReader = $dbSchemaReader;
    }

    /**
     * Describe shards.
     *
     * @param  string $shardName
     * @return array
     */
    public function describeShard($shardName)
    {
        $data = [];
        $tables = $this->dbSchemaReader->readTables($shardName);

        foreach ($tables as $table) {
            if (in_array($table, self::$ignoredSystemTables)) {
                continue;
            }

            $data[$table] = $this->dbSchemaReader->getCreateTableSql($table, $shardName)['Create Table'];
        }

        return $data;
    }
}
