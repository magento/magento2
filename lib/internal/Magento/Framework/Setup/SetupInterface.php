<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * DB resource interface
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
