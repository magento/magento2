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
interface DbSchemaWriterInterface
{
    /**
     * Create table from SQL fragments, like columns, constraints, foreign keys, indexes, etc
     *
     * @param $tableName
     * @param $resource
     * @param  array $definition
     * @return void
     */
    public function createTable($tableName, $resource, array $definition);

    /**
     * Drop table from SQL database
     *
     * @param string $tableName
     * @param string $resource
     * @return mixed
     */
    public function dropTable($tableName, $resource);

    /**
     * Add generic element to table (table must be specified in elementOptions)
     *
     * Can be: column, constraint, index
     *
     * @param string $elementName
     * @param string $resource
     * @param string $tableName
     * @param string $elementDefinition , for example: like CHAR(200) NOT NULL
     * @param string $elementType
     * @return mixed
     */
    public function addElement($elementName, $resource, $tableName, $elementDefinition, $elementType);

    /**
     * Modify column and change it definition
     *
     * Please note: that from all structural elements only column can be modified
     *
     * @param $resource
     * @param $tableName
     * @param  string $columnDefinition
     * @return void
     */
    public function modifyColumn($resource, $tableName, $columnDefinition);

    /**
     * As we can`t just drop and recreate constraint in 2 requests
     * we need to do this in one request
     *
     * @param string $resource
     * @param string $elementName
     * @param string $tableName
     * @param string $type
     * @param string $constraintDefinition
     * @return void
     */
    public function modifyConstraint($resource, $elementName, $tableName, $type, $constraintDefinition);

    /**
     * Drop any element (constraint, column, index) from index
     *
     * @param string $resource
     * @param string $elementName
     * @param string $tableName
     * @param string $type
     * @return \Zend_Db_Statement_Interface
     */
    public function dropElement($resource, $elementName, $tableName, $type);
}
