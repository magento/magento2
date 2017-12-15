<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Setup\Model\Declaration\Schema\Casters\CasterAggregator;
use Magento\Setup\Model\Declaration\Schema\Db\Processors\DbSchemaReaderInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementFactory;
use Magento\Setup\Model\Declaration\Schema\Dto\Structure;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\Sharding;

/**
 * This type of builder is responsible for converting ENTIRE data, that comes from db
 * into DTO`s format, with aggregation root: Structure
 *
 * Note: StructureBuilder can not be used for one structural element, like column or constraint
 * because it should have references to other DTO objects.
 * In order to convert build only 1 structural element use directly it factory
 *
 * @see Structure
 * @inheritdoc
 */
class StructureBuilder
{
    /**
     * @var AdapterMediator
     */
    private $adapter;

    /**
     * @var ElementFactory
     */
    private $elementFactory;

    /**
     * @var CasterAggregator
     */
    private $casterAggregator;

    /**
     * @var DbSchemaReaderInterface
     */
    private $dbSchemaReader;

    /**
     * @var Sharding
     */
    private $sharding;

    /**
     * Parser constructor.
     * @param AdapterMediator $adapter
     * @param ElementFactory $elementFactory
     * @param DbSchemaReaderInterface $dbSchemaReader
     * @param Sharding $sharding
     * @param CasterAggregator $casterAggregator
     */
    public function __construct(
        AdapterMediator $adapter,
        ElementFactory $elementFactory,
        DbSchemaReaderInterface $dbSchemaReader,
        Sharding $sharding,
        CasterAggregator $casterAggregator
    ) {
        $this->adapter = $adapter;
        $this->elementFactory = $elementFactory;
        $this->casterAggregator = $casterAggregator;
        $this->dbSchemaReader = $dbSchemaReader;
        $this->sharding = $sharding;
    }

    /**
     * @inheritdoc
     */
    public function build(Structure $structure)
    {
        $tables = [];

        foreach ($this->dbSchemaReader->readTables($this->sharding->getDefaultResource()) as $tableName) {
            $columns = [];
            $indexes = [];
            $constraints = [];

            $columnsData = $this->adapter->getColumnsList($tableName);
            $indexesData = $this->adapter->getIndexesList($tableName);
            $constrainsData = $this->adapter->getConstraintsList($tableName);

            /** @var Table $table */
            $table = $this->elementFactory->create('table', [
                'name' => $tableName
            ]);

            // Process columns
            foreach ($columnsData as $columnData) {
                $columnData = $this->casterAggregator->cast($columnData);
                $columnData['table'] = $table;
                $column = $this->elementFactory->create($columnData['type'], $columnData);
                $columns[$column->getName()] = $column;
            }

            $table->addColumns($columns);
            //Process indexes
            foreach ($indexesData as $indexData) {
                $indexData = $this->casterAggregator->cast($indexData);
                $indexData['column'] = $this->resolveInternalRelations($columns, $indexData);
                $indexData['table'] = $table;
                $index = $this->elementFactory->create('index', $indexData);
                $indexes[$index->getName()] = $index;
            }
            //Process internal constraints
            foreach ($constrainsData as $constraintData) {
                $constraintData = $this->casterAggregator->cast($constraintData);
                $constraintData['column'] = $this->resolveInternalRelations($columns, $constraintData);
                $constraintData['table'] = $table;
                $constraint = $this->elementFactory->create($constraintData['type'], $constraintData);
                $constraints[$constraint->getName()] = $constraint;
            }

            $table->addIndexes($indexes);
            $table->addConstraints($constraints);
            $tables[$table->getName()] = $table;
            $structure->addTable($table);
        }

        $this->processReferenceKeys($tables);
        return $structure;
    }

    /**
     * Process references for all tables
     * This needs to validate schema. And find out invalid references, for example
     * for tables that do not exists already
     *
     * @param Table[] $tables
     * @return Table[]
     */
    private function processReferenceKeys(array $tables)
    {
        foreach ($this->dbSchemaReader->readTables($this->sharding->getDefaultResource()) as $tableName) {
            $referencesData = $this->adapter->getReferencesList($tableName);
            $references = [];

            foreach ($referencesData as $referenceData) {
                //Prepare reference data
                $referenceData = $this->casterAggregator->cast($referenceData);
                $referenceData['table'] = $tables[$tableName];
                $referenceData['column'] = $tables[$tableName]->getColumnByNameOrId($referenceData['column']);
                $referenceData['referenceTable'] = $tables[$referenceData['referenceTable']];
                $referenceData['referenceColumn'] = $referenceData['referenceTable']->getColumnByNameOrId(
                    $referenceData['referenceColumn']
                );

                $references[$referenceData['name']] = $this->elementFactory->create('foreign', $referenceData);
            }

            $tables[$tableName]->addConstraints($references);
        }

        return $tables;
    }

    /**
     * Retrieve column objects from names
     *
     * @param Column[] $columns
     * @param array $data
     * @return Column[]
     */
    private function resolveInternalRelations(array $columns, array $data)
    {
        if (!is_array($data['column'])) {
            throw new \InvalidArgumentException("Cannot find columns for internal index");
        }

        $referenceColumns = [];
        foreach ($data['column'] as $columnName) {
            if (!isset($columns[$columnName])) {
                //Depends on business logic, we can either ignore non-existing column
                //Or throw exception if db is not consistence and there is no column
                //that was specified for key
                //Right now we prefer to ignore such columns
            } else {
                $referenceColumns[] = $columns[$columnName];
            }
        }

        return $referenceColumns;
    }
}
