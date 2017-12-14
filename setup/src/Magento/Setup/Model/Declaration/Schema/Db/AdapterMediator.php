<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaReaderInterface;

/**
 * Needs for different types of SQL engines
 * Depends on SQL engine, envolves different processors and prepare SQL code or Data Objects tables representation
 */
class AdapterMediator
{
    /**
     * Show all table columns
     */
    const KEY_COLUMNS = 'column';

    /**
     * Under index mean only. All keys will be in constraint
     */
    const KEY_INDEXES = 'index';

    /**
     * Under reference mean foreign key
     */
    const KEY_CONSTRAINT = 'constraint';

    /**
     * Foreign key
     */
    const KEY_REFERENCE = 'reference';

    /**
     * Table key name
     */
    const KEY_TABLE = 'table';

    /**
     * Cache by table, that cached ddl raw query result
     *
     * @var array
     */
    private $ddlCache;

    /**
     * @var array
     */
    private $columnProcessors;
    /**
     * @var DbSchemaReaderInterface
     */
    private $dbSchemaReader;

    /**
     * @param DbSchemaReaderInterface $dbSchemaReader
     * @param array $columnProcessors
     */
    public function __construct(
        DbSchemaReaderInterface $dbSchemaReader,
        array $columnProcessors
    ) {
        $this->columnProcessors = $columnProcessors;
        $this->dbSchemaReader = $dbSchemaReader;
    }

    /**
     * Go through all processors and modify column data
     * depends on type column has
     *
     * @param array $elementData
     * @param $type
     * @return array
     */
    private function processElement(array $elementData, $type)
    {
        if (!isset($this->columnProcessors[$type])) {
            throw new \InvalidArgumentException(sprintf("Cannot find type %s", $type));
        }

        /** @var DbSchemaProcessorInterface $columnProcessor */
        foreach ($this->columnProcessors[$type] as $columnProcessor) {
            $elementData = $columnProcessor->fromDefinition($elementData);
        }

        return $elementData;
    }

    /**
     * Retrieve the list of all Magento non-unique and non-primary indexes
     *
     * @param string $tableName
     * @param string $resource
     * @return array
     */
    public function getIndexesList($tableName, $resource = 'default')
    {
        if (!isset($this->ddlCache[self::KEY_INDEXES][$tableName])) {
            $this->ddlCache[self::KEY_INDEXES][$tableName] = [];
            foreach ($this->dbSchemaReader->readIndexes($tableName, $resource) as $indexData) {
                $index = $this->processElement($indexData, self::KEY_INDEXES);

                if (!isset($this->ddlCache[self::KEY_INDEXES][$tableName][$index['name']])) {
                    $this->ddlCache[self::KEY_INDEXES][$tableName][$index['name']] = [];
                }

                $this->ddlCache[self::KEY_INDEXES][$tableName][$index['name']] = array_replace_recursive(
                    $this->ddlCache[self::KEY_INDEXES][$tableName][$index['name']],
                    $index
                );
            }
        }

        return $this->ddlCache[self::KEY_INDEXES][$tableName];
    }

    /**
     * @param string $tableName
     * @param string $resource
     * @return mixed
     */
    public function getConstraintsList($tableName, $resource = 'default')
    {
        if (!isset($this->ddlCache[self::KEY_CONSTRAINT][$tableName])) {
            $this->ddlCache[self::KEY_CONSTRAINT][$tableName] = [];
            $constraintsData = $this->dbSchemaReader->readConstraints($tableName, $resource);
            foreach ($constraintsData as $constraintData) {
                $constraint = $this->processElement($constraintData, self::KEY_CONSTRAINT);

                if (!isset($this->ddlCache[self::KEY_CONSTRAINT][$tableName][$constraint['name']])) {
                    $this->ddlCache[self::KEY_CONSTRAINT][$tableName][$constraint['name']] = [];
                }

                $this->ddlCache[self::KEY_CONSTRAINT][$tableName][$constraint['name']] = array_replace_recursive(
                    $this->ddlCache[self::KEY_CONSTRAINT][$tableName][$constraint['name']],
                    $constraint
                );
            }
        }

        return $this->ddlCache[self::KEY_CONSTRAINT][$tableName];
    }

    /**
     * Retrieve list of preprocessed foreign keys
     *
     * @param string $tableName
     * @param string $resource
     * @return array
     */
    public function getReferencesList($tableName, $resource = 'default')
    {
        if (!isset($this->ddlCache[self::KEY_REFERENCE][$tableName])) {
            $this->ddlCache[self::KEY_REFERENCE][$tableName] = [];
            $createTable = $this->dbSchemaReader->readReferences($tableName, $resource);
            $foreignKeys = $this->processElement($createTable, self::KEY_REFERENCE);
            //Process foreign keys
            foreach ($foreignKeys as $foreignKey) {
                $this->ddlCache[self::KEY_REFERENCE][$tableName][$foreignKey['name']] = $foreignKey;
            }
        }

        return $this->ddlCache[self::KEY_REFERENCE][$tableName];
    }

    /**
     * @param $tableName
     * @param string $resource
     * @return array
     */
    public function getColumnsList($tableName, $resource = 'default')
    {
        if (!isset($this->ddlCache[self::KEY_COLUMNS][$tableName])) {
            $this->ddlCache[self::KEY_COLUMNS][$tableName] = [];
            foreach ($this->dbSchemaReader->readeColumns($tableName, $resource) as $rawColumn) {
                $column = $this->processElement($rawColumn, self::KEY_COLUMNS);
                $this->ddlCache[self::KEY_COLUMNS][$tableName][$column['name']] = $column;
            }
        }

        return $this->ddlCache[self::KEY_COLUMNS][$tableName];
    }
}
