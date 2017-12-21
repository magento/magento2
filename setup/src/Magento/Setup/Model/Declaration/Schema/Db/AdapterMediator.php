<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaCreatorInterface;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaProcessorInterface;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaReaderInterface;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\GenericElement;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\Dto\TableElementInterface;

/**
 * Needs for different types of SQL engines
 * Depends on SQL engine, envolves different processors and convert from SQL schema represenation
 * to readable <array> or from do DDL operations: convert from DTO`s objects to SQL definition
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
    private $processors;

    /**
     * @var DbSchemaReaderInterface
     */
    private $dbSchemaReader;

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * @param DbSchemaReaderInterface $dbSchemaReader
     * @param DbSchemaWriterInterface $dbSchemaCreator
     * @param array $processors
     */
    public function __construct(
        DbSchemaReaderInterface $dbSchemaReader,
        DbSchemaWriterInterface $dbSchemaCreator,
        array $processors
    ) {
        $this->processors = $processors;
        $this->dbSchemaReader = $dbSchemaReader;
        $this->dbSchemaWriter = $dbSchemaCreator;
    }

    /**
     * Go through all processors and modify column data
     * depends on type column has
     *
     * @param array $elementData
     * @param $type
     * @return array
     */
    private function processElementFromDefinition(array $elementData, $type)
    {
        if (!isset($this->processors[$type])) {
            throw new \InvalidArgumentException(sprintf("Cannot find type %s", $type));
        }

        /** @var DbSchemaProcessorInterface $columnProcessor */
        foreach ($this->processors[$type] as $columnProcessor) {
            $elementData = $columnProcessor->fromDefinition($elementData);
        }

        return $elementData;
    }

    /**
     * Retrieve definition for all table elements
     *
     * @param Table $table
     * @return void
     */
    public function createTable(Table $table)
    {
        $definition = [];
        $tableOptions = [
            'resource' => $table->getResource(),
            'name' => $table->getName()
        ];
        $data = [
            Column::TYPE => $table->getColumns(),
            Constraint::TYPE => $table->getConstraints(),
            Index::TYPE => $table->getIndexes()
        ];

        foreach ($data as $type => $elements) {
            /** @var ElementInterface $element */
            foreach ($elements as $element) {
                $definition[$type][$element->getName()] = $this
                    ->processElementToDefinition($element);
            }
        }

        $this->dbSchemaWriter->createTable($tableOptions, $definition);
    }

    /**
     * Prepare and add element (column, constraint, index) to table
     *
     * @param ElementInterface | TableElementInterface $element
     * @return void
     */
    public function addElement(ElementInterface $element)
    {
        $elementOptions = [
            'table_name' => $element->getTable()->getName(),
            'element_name' => $element->getName(),
            'resource' => $element->getTable()->getResource()
        ];
        $definition = $this->processElementToDefinition($element);

        $this->dbSchemaWriter->addElement(
            $elementOptions,
            $definition,
            $element->getElementType()
        );
    }

    /**
     *
     *
     * @param Constraint $constraint
     */
    public function modifyConstraint(Constraint $constraint)
    {
        $constraintOptions = [
            'table_name' => $constraint->getTable()->getName(),
            'element_name' => $constraint->getName(),
            'resource' => $constraint->getTable()->getResource(),
            'type' => $constraint->getType()
        ];
        $definition = $this->processElementToDefinition($constraint);

        $this->dbSchemaWriter->modifyConstraint(
            $constraintOptions,
            $definition
        );
    }

    /**
     * Prepare and drop element from table
     *
     * @param ElementInterface | TableElementInterface $element
     * @return void
     */
    public function dropElement(ElementInterface $element)
    {
        $elementOptions = [
            'table_name' => $element->getTable()->getName(),
            'element_name' => $element->getName(),
            'resource' => $element->getTable()->getResource(),
            'type' => $element->getType()
        ];

        $this->dbSchemaWriter->dropElement(
            $element->getElementType(),
            $elementOptions
        );
    }

    /**
     * Modify column definition for existing table
     *
     * @param Column $column
     */
    public function modifyColumn(Column $column)
    {
        $columnOptions = [
            'table_name' => $column->getTable()->getName(),
            'element_name' => $column->getName(),
            'resource' => $column->getTable()->getResource()
        ];
        $definition = $this->processElementToDefinition(
            $column
        );

        $this->dbSchemaWriter->modifyColumn(
            $columnOptions,
            $definition
        );
    }

    /**
     * Drop table from DB
     *
     * @param Table $table
     */
    public function dropTable(Table $table)
    {
        $tableOptions = [
            'name' => $table->getName(),
            'resource' => $table->getResource()
        ];

        $this->dbSchemaWriter->dropTable($tableOptions);
    }

    /**
     * Process column definition
     *
     * @param ElementInterface $element
     * @return string
     */
    public function processElementToDefinition(ElementInterface $element)
    {
        $definition = '';
        /** @var DbSchemaProcessorInterface $processor */
        foreach ($this->processors[$element->getElementType()] as $processor) {
            //One column processor can override or modify existing one
            if ($processor->canBeApplied($element)) {
                $definition = $processor->toDefinition($element);
            }
        }

        return $definition;
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
                $index = $this->processElementFromDefinition($indexData, self::KEY_INDEXES);

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
                $constraint = $this->processElementFromDefinition($constraintData, self::KEY_CONSTRAINT);

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
            $foreignKeys = $this->processElementFromDefinition($createTable, self::KEY_CONSTRAINT);
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
                $column = $this->processElementFromDefinition($rawColumn, self::KEY_COLUMNS);
                $this->ddlCache[self::KEY_COLUMNS][$tableName][$column['name']] = $column;
            }
        }

        return $this->ddlCache[self::KEY_COLUMNS][$tableName];
    }

    /**
     * Flush cache
     *
     * @return void
     */
    public function flushCache()
    {
        $this->ddlCache = [];
    }
}
