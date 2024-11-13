<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Db;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementFactory;
use Magento\Framework\Setup\Declaration\Schema\Dto\Schema;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;
use Magento\Framework\Setup\Declaration\Schema\Sharding;
use Magento\Framework\Config\FileResolverByModule;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite;

/**
 * This type of builder is responsible for converting ENTIRE data, that comes from db
 * into DTO`s format, with aggregation root: Schema.
 *
 * Note: SchemaBuilder can not be used for one structural element, like column or constraint
 * because it should have references to other DTO objects.
 * In order to convert build only 1 structural element use directly it factory.
 *
 * @see        Schema
 * @inheritdoc
 */
class SchemaBuilder
{
    /**
     * @var ElementFactory
     */
    private $elementFactory;

    /**
     * @var DbSchemaReaderInterface
     */
    private $dbSchemaReader;

    /**
     * @var Sharding
     */
    private $sharding;

    /**
     * @var array
     */
    private $tables;

    /**
     * @var ReaderComposite
     */
    private $readerComposite;

    /**
     * Constructor.
     *
     * @param ElementFactory $elementFactory
     * @param DbSchemaReaderInterface $dbSchemaReader
     * @param Sharding $sharding
     * @param ReaderComposite $readerComposite
     */
    public function __construct(
        ElementFactory $elementFactory,
        DbSchemaReaderInterface $dbSchemaReader,
        Sharding $sharding,
        ReaderComposite $readerComposite
    ) {
        $this->elementFactory = $elementFactory;
        $this->dbSchemaReader = $dbSchemaReader;
        $this->sharding = $sharding;
        $this->readerComposite = $readerComposite;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function build(Schema $schema)
    {
        $data = $this->readerComposite->read(FileResolverByModule::ALL_MODULES);
        $tablesWithJsonTypeField = [];
        if (isset($data['table'])) {
            foreach ($data['table'] as $keyTable => $tableColumns) {
                foreach ($tableColumns['column'] as $keyColumn => $columnData) {
                    if ($columnData['type'] == 'json') {
                        $tablesWithJsonTypeField[$keyTable] = $keyColumn;
                    }
                }
            }
        }

        foreach ($this->sharding->getResources() as $resource) {
            foreach ($this->dbSchemaReader->readTables($resource) as $tableName) {
                $columns = [];
                $indexes = [];
                $constraints = [];

                $tableOptions = $this->dbSchemaReader->getTableOptions($tableName, $resource);
                $columnsData = $this->dbSchemaReader->readColumns($tableName, $resource);
                $indexesData = $this->dbSchemaReader->readIndexes($tableName, $resource);
                $constrainsData = $this->dbSchemaReader->readConstraints($tableName, $resource);

                /**
                 * @var Table $table
                 */
                $table = $this->elementFactory->create(
                    'table',
                    [
                        'name' => $tableName,
                        'resource' => $resource,
                        'engine' => strtolower($tableOptions['engine'] ?? ''),
                        'comment' => $tableOptions['comment'] === '' ? null : $tableOptions['comment'],
                        'charset' => $tableOptions['charset'],
                        'collation' => $tableOptions['collation']
                    ]
                );

                // Process columns
                foreach ($columnsData as $columnData) {
                    if (isset($tablesWithJsonTypeField[$tableName])
                        && $tablesWithJsonTypeField[$tableName] === $columnData['name']) {
                        $columnData['type'] = 'json';
                    }
                    $columnData['table'] = $table;
                    $column = $this->elementFactory->create($columnData['type'], $columnData);
                    $columns[$column->getName()] = $column;
                }

                $table->addColumns($columns);
                //Process indexes
                foreach ($indexesData as $indexData) {
                    $indexData['table'] = $table;
                    $indexData['columns'] = $this->resolveInternalRelations($columns, $indexData);
                    $index = $this->elementFactory->create('index', $indexData);
                    $indexes[$index->getName()] = $index;
                }
                //Process internal constraints
                foreach ($constrainsData as $constraintData) {
                    $constraintData['table'] = $table;
                    $constraintData['columns'] = $this->resolveInternalRelations($columns, $constraintData);
                    $constraint = $this->elementFactory->create($constraintData['type'], $constraintData);
                    $constraints[$constraint->getName()] = $constraint;
                }

                $table->addIndexes($indexes);
                $table->addConstraints($constraints);
                $this->tables[$table->getName()] = $table;
            }
        }

        $this->processReferenceKeys($this->tables, $schema);
        return $schema;
    }

    /**
     * Process references for all tables. Schema validation required.
     *
     * @param   Table[] $tables
     * @param   Schema $schema
     */
    private function processReferenceKeys(array $tables, Schema $schema)
    {
        foreach ($tables as $table) {
            $tableName = $table->getName();
            if ($schema->getTableByName($tableName) instanceof Table) {
                continue;
            }
            $referencesData = $this->dbSchemaReader->readReferences($tableName, $table->getResource());
            $references = [];

            foreach ($referencesData as $referenceData) {
                //Prepare reference data
                $referenceData['table'] = $table;
                $referenceTableName = $referenceData['referenceTable'];
                $referenceData['column'] = $table->getColumnByName($referenceData['column']);
                $referenceData['referenceTable'] = $this->tables[$referenceTableName];
                $referenceData['referenceColumn'] = $referenceData['referenceTable']->getColumnByName(
                    $referenceData['referenceColumn']
                );

                $references[$referenceData['name']] = $this->elementFactory->create('foreign', $referenceData);
                //We need to instantiate tables in order of references tree
                if (isset($tables[$referenceTableName]) && $referenceTableName !== $tableName) {
                    $this->processReferenceKeys([$referenceTableName => $tables[$referenceTableName]], $schema);
                    unset($tables[$referenceTableName]);
                }
            }

            $table->addConstraints($references);
            $schema->addTable($table);
        }
    }

    /**
     * Retrieve column objects from names.
     *
     * @param   Column[] $columns
     * @param   array $data
     * @return  Column[]
     * @throws  NotFoundException
     */
    private function resolveInternalRelations(array $columns, array $data)
    {
        if (!is_array($data['column'])) {
            throw new NotFoundException(
                new Phrase("Cannot find columns for internal index")
            );
        }

        $referenceColumns = [];
        foreach ($data['column'] as $columnName) {
            if (!isset($columns[$columnName])) {
                $tableName = isset($data['table']) ? $data['table']->getName() : '';
                trigger_error(
                    (string)new Phrase(
                        'Column %1 does not exist for index/constraint %2 in table %3.',
                        [
                            $columnName,
                            $data['name'],
                            $tableName
                        ]
                    ),
                    E_USER_WARNING
                );
            } else {
                $referenceColumns[] = $columns[$columnName];
            }
        }

        return $referenceColumns;
    }
}
