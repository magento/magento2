<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Declaration;

use Magento\Framework\Phrase;
use Magento\Framework\Setup\Declaration\Schema\Declaration\TableElement\ElementNameResolver;
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
 * This type of builder is responsible for converting ENTIRE data, that comes from XML into DTO`s format.
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
     * @var ElementNameResolver
     */
    private $elementNameResolver;

    /**
     * SchemaBuilder constructor.
     *
     * @param    ElementFactory $elementFactory
     * @param    BooleanUtils $booleanUtils
     * @param    Sharding $sharding
     * @param    ValidationComposite $validationComposite
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param ElementNameResolver $elementNameResolver
     */
    public function __construct(
        ElementFactory $elementFactory,
        BooleanUtils $booleanUtils,
        Sharding $sharding,
        ValidationComposite $validationComposite,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        ElementNameResolver $elementNameResolver
    ) {
        $this->sharding = $sharding;
        $this->elementFactory = $elementFactory;
        $this->booleanUtils = $booleanUtils;
        $this->validationComposite = $validationComposite;
        $this->resourceConnection = $resourceConnection;
        $this->elementNameResolver = $elementNameResolver;
    }

    /**
     * Add tables data to builder.
     *
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
    public function build(Schema $schema): Schema
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
    private function getStructuralElementResource(array $tableData): string
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
    private function isDisabled(array $structuralElementData): bool
    {
        return isset($structuralElementData['disabled']) &&
            $this->booleanUtils->toBoolean($structuralElementData['disabled']);
    }

    /**
     * Instantiate column DTO objects from array.
     *
     * If column was renamed new key will be associated to it.
     *
     * @param array $tableData
     * @param string $resource
     * @param Table $table
     * @return array
     */
    private function processColumns(array $tableData, string $resource, Table $table): array
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
     * @param array $elementData
     * @param string $resource
     * @param Table $table
     * @return array
     */
    private function processGenericData(array $elementData, string $resource, Table $table): array
    {
        $elementData['table'] = $table;
        $elementData['resource'] = $resource;

        return $elementData;
    }

    /**
     * Process tables and add them to schema.
     *
     * If table already exists - then we need to skip it.
     *
     * @param  Schema $schema
     * @param  array $tableData
     * @return Table
     */
    private function processTable(Schema $schema, array $tableData): Table
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
     * Provides column by name.
     *
     * @param string $columnName
     * @param Table $table
     * @return Column
     */
    private function getColumnByName(string $columnName, Table $table): Column
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
    private function convertColumnNamesToObjects(array $columnNames, Table $table): array
    {
        $columns = [];

        foreach ($columnNames as $columnName) {
            $columns[] = $this->getColumnByName($columnName, $table);
        }

        return $columns;
    }

    /**
     * Convert and instantiate index objects.
     *
     * @param  array $tableData
     * @param  string $resource
     * @param  Table $table
     * @return Index[]
     */
    private function processIndexes(array $tableData, string $resource, Table $table): array
    {
        if (!isset($tableData['index'])) {
            return [];
        }

        $indexes = [];

        foreach ($tableData['index'] as $indexData) {
            if ($this->isDisabled($indexData)) {
                continue;
            }

            $indexData['name'] = $this->elementNameResolver->getFullIndexName(
                $table,
                $indexData['column'],
                $indexData['indexType'] ?? null
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
     * @param  string $resource
     * @param  Schema $schema
     * @param Table $table
     * @return Constraint[]
     */
    private function processConstraints(array $tableData, string $resource, Schema $schema, Table $table): array
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

                if ($this->isDisabled($referenceTableData)) {
                    throw new \LogicException(
                        sprintf('The reference table named "%s" is disabled', $referenceTableData['name'])
                    );
                }

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
                $constraintData['name'] = $this->elementNameResolver->getFullFKName(
                    $table,
                    $constraintData['column'],
                    $constraintData['referenceTable'],
                    $constraintData['referenceColumn']
                );
                $constraint = $this->elementFactory->create($constraintData['type'], $constraintData);
                $constraints[$constraint->getName()] = $constraint;
            } else {
                $constraintData['name'] = $this->elementNameResolver->getFullIndexName(
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
