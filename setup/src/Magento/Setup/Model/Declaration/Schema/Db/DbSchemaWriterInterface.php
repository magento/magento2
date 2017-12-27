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
     * @param  array $tableOptions
     * @param  array $definition
     * @return void
     */
    public function createTable(array $tableOptions, array $definition);

    /**
     * Drop table from SQL database
     *
     * @param  array $tableOptions must have 2 options: table_name, resource
     * @return mixed
     */
    public function dropTable(array $tableOptions);

    /**
     * Add generic element to table (table must be specified in elementOptions)
     *
     * Can be: column, constraint, index
     *
     * @param  array  $elementOptions     must have 3 options: resource, column_name, table_name
     * @param  string $elementDefinition, for example: like CHAR(200) NOT NULL
     * @param  string $elementType
     * @return mixed
     */
    public function addElement(array $elementOptions, $elementDefinition, $elementType);

    /**
     * Modify column and change it definition
     *
     * Please note: that from all structural elements only column can be modified
     *
     * @param  array  $columnOptions    must have 3 options: resource, column_name, table_name
     * @param  string $columnDefinition
     * @return void
     */
    public function modifyColumn(array $columnOptions, $columnDefinition);

    /**
     * As we can`t just drop and recreate constraint in 2 requests
     * we need to do this in one request
     *
     * @param  array  $constraintOptions
     * @param  string $constraintDefinition
     * @return void
     */
    public function modifyConstraint(array $constraintOptions, $constraintDefinition);

    /**
     * Drop any element (constraint, column, index) from index
     *
     * @param  string $elementType    enum(CONSTRAINT, INDEX, COLUMN)
     * @param  array  $elementOptions
     * @return \Zend_Db_Statement_Interface
     */
    public function dropElement($elementType, array $elementOptions);
}
