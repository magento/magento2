<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Db;

/**
 * This class is responsible for read different schema
 * structural elements: indexes, constraints, table names and columns.
 *
 * @api
 */
interface DbSchemaReaderInterface
{
    /**
     * Read indexes from Magento tables.
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readIndexes($tableName, $resource);

    /**
     * Read constraints from Magento tables.
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readConstraints($tableName, $resource);

    /**
     * Read columns from Magento tables.
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readColumns($tableName, $resource);

    /**
     * Show table options like engine, partitioning, etc.
     *
     * @param string $tableName
     * @param string $resource
     * @return array
     */
    public function getTableOptions($tableName, $resource);

    /**
     * Read references (foreign keys) from Magento tables.
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readReferences($tableName, $resource);

    /**
     * Read table names from Magento tables.
     *
     * @param  string $resource
     * @return array
     */
    public function readTables($resource);
}
