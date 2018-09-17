<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Declaration;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Setup\Declaration\Schema\TableNameResolver;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Setup\Exception;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Index;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\Sharding;

/**
 * This type of builder is responsible for converting ENTIRE data, that comes from XML
 * into DTO`s format, with aggregation root: Schema.
 *
 * Note: SchemaBuilder can not be used for one structural element, like column or constraint
 * because it should have references to other DTO objects.
 * In order to convert build only 1 structural element use directly it factory.
 *
 * structure
 *  - table[N,]
 *   -column
 *   -constraint
 *    -internal (unique, primary, check, nullable)
 *    -reference (referenceTable=<DTO>, referenceColumn=<DTO>, ...)
 *   -index
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SchemaBuilder
{
    /**
     * @var array
     */
    private $tablesData = [];

    /**
     * @var Sharding
     */
    private $sharding;

    /**
     * @var ElementFactory
     */
    private $elementFactory;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var ValidationComposite
     */
    private $validationComposite;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var TableNameResolver
     */
    private $tableNameResolver;

    /**
     * SchemaBuilder constructor.
     *
     * @param    ElementFactory $elementFactory
     * @param    BooleanUtils $booleanUtils
     * @param    Sharding $sharding
     * @param    ValidationComposite $validationComposite
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param TableNameResolver $tableNameResolver
     * @internal param array $tablesData
     */
    public function __construct(
        ElementFactory $elementFactory,
        BooleanUtils $booleanUtils,
        Sharding $sharding,
        ValidationComposite $validationComposite,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        TableNameResolver $tableNameResolver
    ) {
        $this->sharding = $sharding;
        $this->elementFactory = $elementFactory;
        $this->booleanUtils = $booleanUtils;
        $this->validationComposite = $validationComposite;
        $this->resourceConnection = $resourceConnection;
        $this->tableNameResolver = $tableNameResolver;
    }

    /**
     * Add tables data to builder.
     * Tables data holds tables information: columns, constraints, indexes, attributes.
     *
     * @param  array $tablesData
     * @return self
     */
    public function addTablesData(array $tablesData)
    {
        $this->tablesData = $tablesData;
        return $this;
    }

    /**
     * Do schema validation and print all errors.
     *
     * @param  Schema $schema
     * @throws Exception
     */
    private function validate(Schema $schema)
    {
        $errors = $this->validationComposite->validate($schema);

        if (!empty($errors)) {
            $messages = '';
            foreach ($errors as $error) {
                $messages .= sprintf("%s%s", PHP_EOL, $error['message']);
            }

            throw new Exception(new Phrase($messages));
        }
    }

    /**
     * Build schema.
     *
     * @param  Schema $schema
     * @throws Exception
     * @return Schema
     */
    public function build(Schema $schema)
    {
        foreach ($this->tablesData as $tableData) {
            if (!$schema->getTableByName($tableData['name'])) {
                if (!$this->isDisabled($tableData)) {
                    $this->processTable($schema, $tableData);
                }
            }
        }

        $this->validate($schema);

        return $schema;
    }

    /**
     * Get resource for structural elements.
     *
     * @param array $tableData
     * @return string
     */
    private function getStructuralElementResource(array $tableData)
    {
        return isset($tableData['resource']) && $this->sharding->canUseResource($tableData['resource']) ?
            $tableData['resource'] : 'default';
    }

    /**
     * Check whether element is disabled and should not appear in final declaration.
     *
     * @param  array $structuralElementData
     * @return bool
     */
    private function isDisabled($structuralElementData)
    {
        return isset($structuralElementData['disabled']) &&
            $this->booleanUtils->toBoolean($structuralElementData['disabled']);
    }

    /**
     * Instantiate column DTO objects from array.
     * If column was renamed new key will be associated to it.
     *
     * @param  array  $tableData
     * @param  string $resource
     * @param  Table  $table
     * @return array
     */
    private function processColumns(array $tableData, $resource, Table $table)
    {
        $columns = [];

        foreach ($tableData['column'] as $columnData) {
            if ($this->isDisabled($columnData)) {
                continue;
            }

            $columnData = $this->processGenericData($columnData, $resource, $table);
            $column = $this->elementFactory->create($columnData['type'], $columnData);
            $columns[$column->getName()] = $column;
        }

        return $columns;
    }

    /**
     * Process generic data that is support by all 3 child types: columns, constraints, indexes.
     *
     * @param  array    $elementData
     * @param  Table    $table
     * @param  $resource
     * @return array
     */
    private function processGenericData(array $elementData, $resource, Table $table)
    {
        $elementData['table'] = $table;
        $elementData['resource'] = $resource;

        return $elementData;
    }

    /**
     * Process tables and add them to schema.
     * If table already exists - then we need to skip it.
     *
     * @param  Schema $schema
     * @param  array  $tableData
     * @return \Magento\Framework\Setup\Declaration\Schema\Dto\Table
     */
    private function processTable(Schema $schema, array $tableData)
    {
        if (!$schema->getTableByName($tableData['name'])) {
            $resource = $this->getStructuralElementResource($tableData);
            $tableData['resource'] = $resource;
            $tableData['comment'] = $tableData['comment'] ?? null;
            /** @var Table $table */
            $table = $this->elementFactory->create('table', $tableData);
            $columns = $this->processColumns($tableData, $resource, $table);
            $table->addColumns($columns);
            //Add indexes to table
            $table->addIndexes($this->processIndexes($tableData, $resource, $table));
            //Add internal and reference constraints
            $table->addConstraints($this->processConstraints($tableData, $resource, $schema, $table));
            $schema->addTable($table);
        }

        return $schema->getTableByName($tableData['name']);
    }

    /**
     * @param string $columnName
     * @param Table $table
     * @return Column
     */
    private function getColumnByName(string $columnName, Table $table)
    {
        $columnCandidate = $table->getColumnByName($columnName);

        if (!$columnCandidate) {
            throw new \LogicException(
                sprintf('Table %s do not have column with name %s', $table->getName(), $columnName)
            );
        }

        return $columnCandidate;
    }

    /**
     * Convert column names to objects.
     *
     * @param  array $columnNames
     * @param  Table $table
     * @return array
     */
    private function convertColumnNamesToObjects(array $columnNames, Table $table)
    {
        $columns = [];

        foreach ($columnNames as $columnName) {
            $columns[] = $this->getColumnByName($columnName, $table);
        }

        return $columns;
    }

    /**
     * Provides the full index name based on the prefix value.
     *
     * @param string $name
     * @param Table $table
     * @param array $columns
     * @param string $type
     * @return string
     */
    private function getFullIndexName(
        string $name,
        Table $table,
        array $columns,
        string $type = AdapterInterface::INDEX_TYPE_INDEX
    ) {
        if (AdapterInterface::INDEX_TYPE_PRIMARY === $type) {
            return $name;
        }

        $tableName = $this->tableNameResolver->getNameOfOriginTable($table->getName());

        return $this->resourceConnection
            ->getIdxName(
                $tableName,
                $columns,
                $type
            );
    }

    /**
     * Convert and instantiate index objects.
     *
     * @param  array    $tableData
     * @param  $resource
     * @param  Table    $table
     * @return Index[]
     */
    private function processIndexes(array $tableData, $resource, Table $table)
    {
        if (!isset($tableData['index'])) {
            return [];
        }

        $indexes = [];

        foreach ($tableData['index'] as $indexData) {
            if ($this->isDisabled($indexData)) {
                continue;
            }

            /**
             * Temporary solution.
             * @see MAGETWO-91365
             */
            $indexType = AdapterInterface::INDEX_TYPE_INDEX;
            if (isset($indexData['indexType']) && $indexData['indexType'] === AdapterInterface::INDEX_TYPE_FULLTEXT) {
                $indexType = $indexData['indexType'];
            }

            $indexData['name'] = $this->getFullIndexName(
                $indexData['name'],
                $table,
                $indexData['column'],
                $indexType
            );
            $indexData = $this->processGenericData($indexData, $resource, $table);
            $indexData['columns'] = $this->convertColumnNamesToObjects($indexData['column'], $table);
            $index = $this->elementFactory->create('index', $indexData);
            $indexes[$index->getName()] = $index;
        }

        return $indexes;
    }

    /**
     * Convert and instantiate constraint objects.
     *
     * @param  array $tableData
     * @param  $resource
     * @param  Schema $schema
     * @param Table $table
     * @return Constraint[]
     */
    private function processConstraints(array $tableData, $resource, Schema $schema, Table $table)
    {
        if (!isset($tableData['constraint'])) {
            return [];
        }

        $constraints = [];

        foreach ($tableData['constraint'] as $constraintData) {
            if ($this->isDisabled($constraintData)) {
                continue;
            }
            $constraintData = $this->processGenericData($constraintData, $resource, $table);
            //As foreign constraint has different schema we need to process it in different way
            if ($constraintData['type'] === 'foreign') {
                $constraintData['column'] = $this->getColumnByName($constraintData['column'], $table);
                $referenceTableData = $this->tablesData[$constraintData['referenceTable']];
                //If we are referenced to the same table we need to specify it
                //Get table name from resource connection regarding prefix settings
                $refTableName = $this->resourceConnection->getTableName($referenceTableData['name']);
                $referenceTable = $refTableName === $table->getName() ?
                    $table :
                    $this->processTable($schema, $referenceTableData);

                if ($referenceTable->getResource() !== $table->getResource()) {
                    continue; //we should avoid creating foreign keys
                    //for tables that are on another shard
                }
                $constraintData['referenceTable'] = $referenceTable;

                if (!$constraintData['referenceTable']) {
                    throw new \LogicException(
                        sprintf('Cannot find reference table with name %s', $constraints['referenceTable'])
                    );
                }

                $constraintData['referenceColumn'] = $this->getColumnByName(
                    $constraintData['referenceColumn'],
                    $constraintData['referenceTable']
                );
                /**
                 * Calculation of the full name of Foreign Key based on the prefix value.
                 */
                $constraintData['name'] = $this->resourceConnection
                    ->getFkName(
                        $this->tableNameResolver->getNameOfOriginTable($table->getName()),
                        $constraintData['column']->getName(),
                        $constraintData['referenceTable']->getName(),
                        $constraintData['referenceColumn']->getName()
                    );
                $constraint = $this->elementFactory->create($constraintData['type'], $constraintData);
                $constraints[$constraint->getName()] = $constraint;
            } else {
                $constraintData['name'] = $this->getFullIndexName(
                    $constraintData['name'],
                    $table,
                    $constraintData['column'],
                    $constraintData['type']
                );
                $constraintData['columns'] = $this->convertColumnNamesToObjects($constraintData['column'], $table);
                $constraint = $this->elementFactory->create($constraintData['type'], $constraintData);
                $constraints[$constraint->getName()] = $constraint;
            }
        }

        return $constraints;
    }
}
