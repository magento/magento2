<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

/**
 * This class is responsible for read different schema
 * structural elements: indexes, constraints, talbe names and columns
 */
interface DbSchemaReaderInterface
{
    /**
     * Read indexes, from Magento tables
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readIndexes($tableName, $resource);

    /**
     * Read constraints, from Magento tables
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readConstraints($tableName, $resource);

    /**
     * Read columns, from Magento tables
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readColumns($tableName, $resource);

    /**
     * Read references (foreign keys) from Magento tables
     *
     * @param  string $tableName
     * @param  string $resource
     * @return array
     */
    public function readReferences($tableName, $resource);

    /**
     * Read table names, from Magento tables
     *
     * @param  string $resource
     * @return array
     */
    public function readTables($resource);
}
