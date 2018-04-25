<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * DB resource interface
 *
 * @api
 */
interface SetupInterface
{
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
     * Gets table placeholder by table name
     *
     * @param string $tableName
     * @return string
     * @since 100.1.0
     */
    public function getTablePlaceholder($tableName);

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
     *
     * @deprecated Call individual methods only if required
     * @see disableAutoValueOnZero()
     * @see disableForeignKeyChecks()
     */
    public function startSetup();

    /**
     * Set no auto value on zero
     *
     * @return $this
     */
    public function disableAutoValueOnZero();

    /**
     * Disable foreign key checks
     *
     * @return $this
     */
    public function disableForeignKeyChecks();

    /**
     * Prepares database after install/upgrade
     *
     * @return $this
     *
     * @deprecated Call individual methods only if required
     * @see resetAutoValueOnZero()
     * @see resetForeignKeyChecks()
     */
    public function endSetup();

    /**
     * Reset mode back to what it was previously
     *
     * @return $this
     */
    public function resetAutoValueOnZero();

    /**
     * Reset foreign key checks back to what they were previously
     *
     * @return $this
     */
    public function resetForeignKeyChecks();
}
