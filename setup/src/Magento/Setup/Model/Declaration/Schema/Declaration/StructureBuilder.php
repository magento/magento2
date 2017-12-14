<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Declaration;

use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementFactory;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\Structure;
use Magento\Setup\Model\Declaration\Schema\Dto\StructureFactory;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;
use Magento\Setup\Model\Declaration\Schema\Sharding;

/**
 * Class StructureBuilder
 * Return collection with DTO objects from array
 */
class StructureBuilder
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
     * StructureBuilder constructor.
     * @param ElementFactory $elementFactory
     * @param BooleanUtils $booleanUtils
     * @param Sharding $sharding
     * @internal param array $tablesData
     */
    public function __construct(
        ElementFactory $elementFactory,
        BooleanUtils $booleanUtils,
        Sharding $sharding
    ) {
        $this->sharding = $sharding;
        $this->elementFactory = $elementFactory;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Add tables data to builder
     * Tables data holds tables information: columns, constraints, indexes, attributes
     *
     * @param array $tablesData
     * @return self
     */
    public function addTablesData(array $tablesData)
    {
        $this->tablesData = $tablesData;
        return $this;
    }

    /**
     * Build structure
     *
     * @param Structure $structure
     * @return Structure
     */
    public function build(Structure $structure)
    {
        foreach ($this->tablesData as $tableData) {
            if (!$structure->getTableByName($tableData['name'])) {
                if (!$this->isDisabled($tableData)) {
                    $this->processTable($structure, $tableData);
                }
            }
        }

        return $structure;
    }

    /**
     * If some element was renamed, we need to retrieve new name instead of old one
     *
     * @param array $elementData
     * @return string
     */
    private function resolveElementName(array $elementData)
    {
        return isset($elementData['renameTo']) ? $elementData['renameTo'] : $elementData['name'];
    }

    /**
     * If element was renamed or should be renamed we need to retrieve its old name
     * In order to do rename in future or in order to do compare
     *
     * @param array $elementData
     * @return string
     */
    private function wasRenamedFrom(array $elementData)
    {
        return isset($elementData['renameTo']) ? $elementData['name'] : null;
    }

    /**
     * @param array $tableData
     * @return string
     */
    private function getStructuralElementResource(array $tableData)
    {
        return isset($tableData['resource']) && $this->sharding->canUseResource($tableData['resource']) ?
            $tableData['resource'] : 'default';
    }

    /**
     * Check whether element is disabled and should not appear in final declaration
     *
     * @param array $structuralElementData
     * @return bool
     */
    private function isDisabled(array $structuralElementData)
    {
        return isset($structuralElementData['disabled']) &&
            $this->booleanUtils->toBoolean($structuralElementData['disabled']);
    }

    /**
     * Instantiate column DTO objects from array
     * If column was renamed new key will be associated to it
     *
     * @param array $tableData
     * @param string $resource
     * @param Table $table
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
     * Process generic data that is support by all 3 child types: columns, constraints, indexes
     *
     * @param array $elementData
     * @param Table $table
     * @param $resource
     * @return array
     */
    private function processGenericData(array $elementData, $resource, Table $table)
    {
        $elementData['wasRenamedFrom'] = $this->wasRenamedFrom($elementData);
        $elementData['name'] = $this->resolveElementName($elementData);
        $elementData['table'] = $table;
        $elementData['resource'] = $resource;

        return $elementData;
    }

    /**
     * Process tables and add them to structure
     * If table already exists - then we need to skip it
     *
     * @param Structure $structure
     * @param array $tableData
     * @return \Magento\Setup\Model\Declaration\Schema\Dto\Table
     */
    private function processTable(Structure $structure, array $tableData)
    {
        if (!$structure->getTableByNameOrId($tableData['name'])) {
            $resource = $this->getStructuralElementResource($tableData);
            $tableParams = [
                'name' => $this->resolveElementName($tableData),
                'resource' => $resource,
                'wasRenamedFrom' => $this->wasRenamedFrom($tableData)
            ];
            /** @var Table $table */
            $table = $this->elementFactory->create('table', $tableParams);
            $columns = $this->processColumns($tableData, $resource, $table);
            $table->addColumns($columns);
            $structure->addTable($table);
            //Add indexes to table
            $table->addIndexes($this->processIndexes($tableData, $resource, $table));
            //Add internal and reference constraints
            $table->addConstraints($this->processConstraints($tableData, $resource, $structure));
        }

        return $structure->getTableByNameOrId($tableData['name']);
    }

    /**
     * Convert column names to objects
     *
     * @param array $columnNames
     * @param Table $table
     * @return array
     */
    private function convertColumnNamesToObjects(array $columnNames, Table $table)
    {
        $columns = [];

        foreach ($columnNames as $columnName) {
            $columns[] = $table->getColumnByNameOrId($columnName);
        }

        return $columns;
    }

    /**
     * Convert and instantiate index objects
     *
     * @param array $tableData
     * @param $resource
     * @param Table $table
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

            $indexData = $this->processGenericData($indexData, $resource, $table);
            $indexData['column'] = $this->convertColumnNamesToObjects($indexData['column'], $table);
            $index = $this->elementFactory->create('index', $indexData);
            $indexes[$index->getName()] = $index;
        }

        return $indexes;
    }

    /**
     * Convert and instantiate constraint objects
     *
     * @param array $tableData
     * @param $resource
     * @param Structure $structure
     * @return Constraint[]
     */
    private function processConstraints(array $tableData, $resource, Structure $structure)
    {
        if (!isset($tableData['constraint'])) {
            return [];
        }

        $constraints = [];

        foreach ($tableData['constraint'] as $constraintData) {
            if ($this->isDisabled($constraintData)) {
                continue;
            }
            $table = $structure->getTableByName($tableData['name']);
            $constraintData = $this->processGenericData($constraintData, $resource, $table);
            //As foreign constraint has different structure we need to process it in different way
            if ($constraintData['type'] === 'foreign') {
                $constraintData['column'] = $table->getColumnByNameOrId(
                    $constraintData['column']
                );
                //always in foreign key name will be old and in raw data will be always old too
                $structure->addTable(
                    $this->processTable(
                        $structure,
                        $this->tablesData[$constraintData['referenceTable']]
                    )
                );
                $constraintData['referenceTable'] = $structure->getTableByNameOrId(
                    $constraintData['referenceTable']
                );

                if (!$constraintData['referenceTable']) {
                    throw new \LogicException("Cannot find reference table");
                }

                $constraintData['referenceColumn'] = $constraintData['referenceTable']->getColumnByNameOrId(
                    $constraintData['referenceColumn']
                );
            } else {
                $constraintData['column'] = $this->convertColumnNamesToObjects($constraintData['column'], $table);
            }

            $constraint = $this->elementFactory->create($constraintData['type'], $constraintData);
            $constraints[$constraint->getName()] = $constraint;
        }

        return $constraints;
    }
}
