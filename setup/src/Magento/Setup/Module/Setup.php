<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\ResourceConnection;

class Setup extends \Magento\Framework\Module\Setup implements SchemaSetupInterface
{
    /**
     * Retrieve 32bit UNIQUE HASH for a Table index
     *
     * @param string $tableName
     * @param array|string $fields
     * @param string $indexType
     * @param string $connectionName
     * @return string
     */
    public function getIdxName(
        $tableName,
        $fields,
        $indexType = '',
        $connectionName = ResourceConnection::DEFAULT_CONNECTION
    ) {
        return $this->getConnection($connectionName)->getIndexName($this->getTable($tableName), $fields, $indexType);
    }

    /**
     * Retrieve 32bit UNIQUE HASH for a Table foreign key
     *
     * @param string $priTableName  the target table name
     * @param string $priColumnName the target table column name
     * @param string $refTableName  the reference table name
     * @param string $refColumnName the reference table column name
     * @param string $connectionName
     * @return string
     */
    public function getFkName(
        $priTableName,
        $priColumnName,
        $refTableName,
        $refColumnName,
        $connectionName = ResourceConnection::DEFAULT_CONNECTION
    ) {
        return $this->getConnection($connectionName)->getForeignKeyName(
            $this->getTable($priTableName),
            $priColumnName,
            $refTableName,
            $refColumnName
        );
    }
}
