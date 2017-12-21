<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors\MySQL;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Db\AdapterMediator;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaReaderInterface;

/**
 * @inheritdoc
 */
class DbSchemaReader implements DbSchemaReaderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Prepare and fetch query: Describe {table_name}
     *
     * @param string $tableName
     * @param string $resource
     * @return array
     */
    public function readeColumns($tableName, $resource)
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $sql = 'DESCRIBE ' . $adapter->quoteIdentifier($tableName, true);
        $stmt = $adapter->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        return $stmt->fetchAll(\Zend_Db::FETCH_NUM);
    }

    /**
     * Fetch all indexes from table
     *
     * @param string $tableName
     * @param string $resource
     * @return array
     */
    public function readIndexes($tableName, $resource)
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $condition = sprintf('Non_unique = 1');
        $sql = sprintf('SHOW INDEXES FROM %s WHERE %s', $tableName, $condition);
        $stmt = $adapter->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        return $stmt->fetchAll(\Zend_Db::FETCH_ASSOC);
    }

    /**
     * As MySQL has bug and do not show foreign keys during
     * DESCRIBE and other directives we need to take it from SHOW CREATE TABLE ...
     * command
     *
     * @inheritdoc
     */
    public function readReferences($tableName, $resource)
    {
        return $this->getCreateTableSql($tableName, $resource);
    }

    /**
     * Retrieve Create table SQL, from SHOW CREATE TABLE query
     *
     * @param string $tableName
     * @param string $resource
     * @return array
     */
    public function getCreateTableSql($tableName, $resource)
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $sql = sprintf('SHOW CREATE TABLE %s', $tableName);
        $stmt = $adapter->query($sql);
        return $stmt->fetch(\Zend_Db::FETCH_ASSOC);
    }

    /**
     * For MySQL reading of constraints is almost the same as indexes
     * But we need to know, that we read 2 types of constraints:
     * primary and unique keys and this keys are always non_unique=0
     *
     * @inheritdoc
     */
    public function readConstraints($tableName, $resource)
    {
        $adapter = $this->resourceConnection->getConnection($resource);
        $condition = sprintf('Non_unique = 0');
        $sql = sprintf('SHOW INDEXES FROM %s WHERE %s', $tableName, $condition);
        $stmt = $adapter->query($sql);

        // Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
        return $stmt->fetchAll(\Zend_Db::FETCH_ASSOC);
    }

    /**
     * Return names of all tables from shard
     *
     * @param string $resource Shard name
     * @return array
     */
    public function readTables($resource = 'default')
    {
        return $this->resourceConnection
                ->getConnection($resource)
                ->getTables();
    }
}
