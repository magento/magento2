<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaReaderInterface;
use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;

/**
 * @inheritdoc
 */
class DbSchemaReader implements DbSchemaReaderInterface
{
    /**
     * Table type in information_schema.TABLES which allows to identify only tables and ignore views
     */
    const MYSQL_TABLE_TYPE = 'BASE TABLE';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DefinitionAggregator
     */
    private $definitionAggregator;

    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param DefinitionAggregator $definitionAggregator
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefinitionAggregator $definitionAggregator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->definitionAggregator = $definitionAggregator;
    }

    /**
     * @inheritdoc
     */
    public function getTableOptions($tableName, $resource)
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $dbName = $this->resourceConnection->getSchemaName($resource);
        $stmt = $adapter->select()
            ->from(
                ['i_tables' => 'information_schema.TABLES'],
                [
                    'engine' => 'ENGINE',
                    'comment' => 'TABLE_COMMENT',
                    'collation' => 'TABLE_COLLATION'
                ]
            )
            ->joinInner(
                ['charset_applicability' => 'information_schema.COLLATION_CHARACTER_SET_APPLICABILITY'],
                'i_tables.table_collation = charset_applicability.collation_name',
                [
                    'charset' => 'charset_applicability.CHARACTER_SET_NAME'
                ]
            )
            ->where('TABLE_SCHEMA = ?', $dbName)
            ->where('TABLE_NAME = ?', $tableName);

        return $adapter->fetchRow($stmt);
    }

    /**
     * Prepare and fetch query: Describe {table_name}.
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readColumns($tableName, $resource)
    {
        $columns = [];
        $adapter = $this->resourceConnection->getConnection($resource);
        $dbName = $this->resourceConnection->getSchemaName($resource);
        $stmt = $adapter->select()
            ->from(
                'information_schema.COLUMNS',
                [
                    'name' => 'COLUMN_NAME',
                    'default' => 'COLUMN_DEFAULT',
                    'type' => 'DATA_TYPE',
                    'nullable' => new Expression('IF(IS_NULLABLE="YES", true, false)'),
                    'definition' => 'COLUMN_TYPE',
                    'extra' => 'EXTRA',
                    'comment' => new Expression('IF(COLUMN_COMMENT="", NULL, COLUMN_COMMENT)')
                ]
            )
            ->where('TABLE_SCHEMA = ?', $dbName)
            ->where('TABLE_NAME = ?', $tableName)
            ->order('ORDINAL_POSITION ASC');

        $columnsDefinition = $adapter->fetchAssoc($stmt);

        foreach ($columnsDefinition as $columnDefinition) {
            $column = $this->definitionAggregator->fromDefinition($columnDefinition);
            $columns[$column['name']] = $column;
        }

        return $columns;
    }

    /**
     * Fetch all indexes from table.
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readIndexes($tableName, $resource)
    {
        $indexes = [];
        $adapter = $this->resourceConnection->getConnection($resource);
        $condition = sprintf('`Non_unique` = 1');
        $sql = sprintf('SHOW INDEXES FROM `%s` WHERE %s', $tableName, $condition);
        $stmt = $adapter->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        $indexesDefinition = $stmt->fetchAll(\Zend_Db::FETCH_ASSOC);

        foreach ($indexesDefinition as $indexDefinition) {
            $indexDefinition['type'] = 'index';
            $index = $this->definitionAggregator->fromDefinition($indexDefinition);

            if (!isset($indexes[$index['name']])) {
                $indexes[$index['name']] = [];
            }

            $indexes[$index['name']] = array_replace_recursive($indexes[$index['name']], $index);
        }

        return $indexes;
    }

    /**
     * Read references (foreign keys) from Magento tables.
     *
     * As MySQL has bug and do not show foreign keys during DESCRIBE and other directives required
     * to take it from "SHOW CREATE TABLE ..." command.
     *
     * @inheritdoc
     */
    public function readReferences($tableName, $resource)
    {
        $createTableSql = $this->getCreateTableSql($tableName, $resource);
        $createTableSql['type'] = 'reference';
        return $this->definitionAggregator->fromDefinition($createTableSql);
    }

    /**
     * Retrieve Create table SQL, from SHOW CREATE TABLE query.
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function getCreateTableSql($tableName, $resource)
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $sql = sprintf('SHOW CREATE TABLE `%s`', $tableName);
        $stmt = $adapter->query($sql);
        return $stmt->fetch(\Zend_Db::FETCH_ASSOC);
    }

    /**
     * Reading DB constraints.
     *
     * Primary and unique constraints are always non_unique=0.
     *
     * @inheritdoc
     */
    public function readConstraints($tableName, $resource)
    {
        $constraints = [];
        $adapter = $this->resourceConnection->getConnection($resource);
        $condition = sprintf('`Non_unique` = 0');
        $sql = sprintf('SHOW INDEXES FROM `%s` WHERE %s', $tableName, $condition);
        $stmt = $adapter->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        $constraintsDefinition = $stmt->fetchAll(\Zend_Db::FETCH_ASSOC);

        foreach ($constraintsDefinition as $constraintDefinition) {
            $constraintDefinition['type'] = Constraint::TYPE;
            $constraint = $this->definitionAggregator->fromDefinition($constraintDefinition);

            if (!isset($constraints[$constraint['name']])) {
                $constraints[$constraint['name']] = [];
            }

            $constraints[$constraint['name']] = array_replace_recursive($constraints[$constraint['name']], $constraint);
        }

        return $constraints;
    }

    /**
     * Return names of all tables from shard.
     *
     * @param  string $resource Shard name.
     * @return array
     */
    public function readTables($resource)
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $dbName = $this->resourceConnection->getSchemaName($resource);
        $stmt = $adapter->select()
            ->from(
                ['information_schema.TABLES'],
                ['TABLE_NAME']
            )
            ->where('TABLE_SCHEMA = ?', $dbName)
            ->where('TABLE_TYPE = ?', self::MYSQL_TABLE_TYPE);
        return $adapter->fetchCol($stmt);
    }
}
