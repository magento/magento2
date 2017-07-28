<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * DB schema resource interface
 * @api
 * @since 2.0.0
 */
interface SchemaSetupInterface extends SetupInterface
{
    /**
     * Retrieves 32bit UNIQUE HASH for a Table index
     *
     * @param string $tableName
     * @param array|string $fields
     * @param string $indexType
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName);
}
