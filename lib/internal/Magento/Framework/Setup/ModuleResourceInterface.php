<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * DB schema resource interface for a module
 */
interface ModuleResourceInterface
{
    /**
     * Applies module recurring post schema updates
     *
     * @return $this
     * @throws \Exception
     */
    public function applyRecurringUpdates();

    /**
     * Applies module resource install, upgrade and data scripts
     *
     * @return $this
     */
    public function applyUpdates();

    /**
     * Retrieves 32bit UNIQUE HASH for a Table index
     *
     * @param string $tableName
     * @param array|string $fields
     * @param string $indexType
     * @return string
     */
    public function getIdxName($tableName, $fields, $indexType = '');

    /**
     * Retrieves 32bit UNIQUE HASH for a Table foreign key
     *
     * @param string $priTableName  the target table name
     * @param string $priColumnName the target table column name
     * @param string $refTableName  the reference table name
     * @param string $refColumnName the reference table column name
     * @return string
     */
    public function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName);

    /**
     * Gets connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection();

    /**
     * Adds table placeholder/table name relation
     *
     * @param string $tableName
     * @param string $realTableName
     * @return $this
     */
    public function setTable($tableName, $realTableName);

    /**
     * Gets table name (validated by db adapter) by table placeholder
     *
     * @param string|array $tableName
     * @return string
     */
    public function getTable($tableName);


    /**
     * Checks if table exists
     *
     * @param string $table
     * @return bool
     */
    public function tableExists($table);

    /**
     * Runs plain SQL query(ies)
     *
     * @param string $sql
     * @return $this
     */
    public function run($sql);

    /**
     * Prepares database before install/upgrade
     *
     * @return $this
     */
    public function startSetup();

    /**
     * Prepares database after install/upgrade
     *
     * @return $this
     */
    public function endSetup();
}
