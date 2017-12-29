<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Setup\Model\SchemaListenerDefinition\DefinitionConverterInterface;

/**
 * Listen for all changes and record them in order to reuse later
 */
class SchemaListener
{
    /**
     * @var array
     */
    private $tables = [];

    /**
     * @var array
     */
    private static $mapping = [
        'DATA_TYPE' => 'type',
        'COLUMN_NAME' => 'name',
        'TYPE' => 'type',
        'DEFAULT' => 'default',
        'NULLABLE' => 'nullable',
        'LENGTH' => 'length',
        'PRECISION' => 'precision',
        'SCALE' => 'scale',
        'UNSIGNED' => 'unsigned',
        'IDENTITY' => 'identity',
        'PRIMARY' => 'primary'
    ];

    /**
     * @var array
     */
    private static $toUnset = [
        'COLUMN_POSITION',
        'COLUMN_TYPE',
        'PRIMARY_POSITION',
        'COMMENT'
    ];

    /**
     * @var string
     */
    private $moduleName = '';

    /**
     * @var DefinitionConverterInterface[]
     */
    private $definitionMappers;

    /**
     * @param array $definitionMappers
     */
    public function __construct(array $definitionMappers)
    {
        $this->definitionMappers = $definitionMappers;
    }

    /**
     * @param string $tableName
     * @param string $fkName
     */
    public function dropForeignKey($tableName, $fkName)
    {
        $this->tables[$this->moduleName][$tableName]['constraints']['foreign'][$fkName] = ['disabled' => 1];
    }

    /**
     * @param array $definition
     * @param string $columnName
     * @return array
     */
    private function castColumnDefinition($definition, $columnName)
    {
        if (is_string($definition)) {
            $definition = ['type' => $definition];
        }
        $definition = $this->doColumnMapping($definition);
        $definition['name'] = $columnName;
        $definition = $this->definitionMappers[$definition['type']]->convertToDefinition($definition);
        if (isset($definition['default']) && $definition['default'] === false) {
            $definition['default'] = null; //uniform default values
        }

        return $definition;
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     * @return array
     */
    private function addPrimaryKeyIfExists($tableName, $columnName, $definition)
    {
        if (isset($definition['primary'])) {
            if (isset($this->tables[$this->moduleName][$tableName]['constraints']['primary'])) {
                $this->tables[$this->moduleName][$tableName]['constraints']['primary']['PRIMARY'] = array_replace_recursive(
                    $this->tables[$this->moduleName][$tableName]['constraints']['primary']['PRIMARY'],
                    [
                        'columns' => [strtolower($columnName)]
                    ]
                );

            } else {
                $this->tables[$this->moduleName][$tableName]['constraints']['primary']['PRIMARY'] = [
                    'type' => 'primary',
                    'name' => 'PRIMARY',
                    'columns' => [strtolower($columnName)]
                ];
            }
        }

        unset($definition['primary']);
        return $definition;
    }

    /**
     * Process definition to not specific format
     *
     * @param array $definition
     * @return array
     */
    private function doColumnMapping(array $definition)
    {
        foreach ($definition as $key => $keyValue) {
            if (isset(self::$mapping[$key])) {
                $definition[self::$mapping[$key]] = $keyValue;
                unset($definition[$key]);
            }

            if (in_array($key, self::$toUnset)) {
                unset($definition[$key]);
            }
        }

        return $definition;
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     */
    public function addColumn($tableName, $columnName, $definition)
    {
        $definition = $this->castColumnDefinition($definition, $columnName);
        $definition = $this->addPrimaryKeyIfExists($tableName, $columnName, $definition);
        $this->tables[$this->moduleName][strtolower($tableName)]['columns'][strtolower($columnName)] = $definition;
    }

    /**
     * @param string $tableName
     * @param string $oldColumnName
     * @param string $newColumnName
     * @param array $definition
     */
    public function changeColumn($tableName, $oldColumnName, $newColumnName, $definition)
    {
        $this->addColumn($tableName, $newColumnName, $definition);
        $this->tables[$this->moduleName][$tableName]['columns'][strtolower($oldColumnName)] = [
            'disabled' => 1
        ];
        //remove old column if not equal
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     */
    public function modifyColumn($tableName, $columnName, $definition)
    {
        $this->addColumn($tableName, $columnName, $definition);
    }

    /**
     * @param string $fkName
     * @param string $tableName
     * @param string $columnName
     * @param string $refTableName
     * @param string $refColumnName
     * @param string $onDelete
     */
    public function addForeignKey(
        $fkName,
        $tableName,
        $columnName,
        $refTableName,
        $refColumnName,
        $onDelete = AdapterInterface::FK_ACTION_CASCADE
    ) {
        $this->tables[$this->moduleName][strtolower($tableName)]['constraints']['foreign'][$fkName] =
            [
                'table' => strtolower($tableName),
                'column' => strtolower($columnName),
                'referenceTable' => strtolower($refTableName),
                'referenceColumn' => strtolower($refColumnName),
                'onDelete' => $onDelete
            ];
    }

    /**
     * Strtolower all index columns
     *
     * @param array $indexColumns
     * @return array
     */
    private function prepareIndexColumns(array $indexColumns)
    {
        $columnNames = [];

        foreach ($indexColumns as $key => $indexColumn) {
            if (is_array($indexColumn)) {
                $columnNames[] = strtolower($key);
            } else {
                $columnNames[] = $indexColumn;
            }
        }

        return $columnNames;
    }

    /**
     * @param string $tableName
     * @param string $indexName
     * @param array $fields
     * @param string $indexType
     */
    public function addIndex(
        $tableName,
        $indexName,
        $fields,
        $indexType = AdapterInterface::INDEX_TYPE_INDEX
    ) {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        if ($indexType == AdapterInterface::INDEX_TYPE_FULLTEXT || $indexType === AdapterInterface::INDEX_TYPE_INDEX) {
            if ($indexType === AdapterInterface::INDEX_TYPE_INDEX) {
                $indexType = 'btree';
            }
            $this->tables[$this->moduleName][$tableName]['indexes'][$indexName] =
                ['columns' => $this->prepareIndexColumns($fields), 'type' => $indexType];
        } else {
            $this->tables[$this->moduleName][$tableName]['constraints'][$indexType][$indexName] =
                ['columns' => $this->prepareIndexColumns($fields)];
        }
    }

    /**
     * Prepare table columns to registration
     *
     * @param string $tableName
     * @param array $tableColumns
     */
    private function prepareColumns($tableName, array $tableColumns)
    {
        foreach ($tableColumns as $name => $tableColumn) {
            $this->addColumn($tableName, $name, $tableColumn);
        }
    }

    /**
     * Convert constraints from old format to new one
     *
     * @param array $foreignKeys
     * @param array $indexes
     * @param string $tableName
     */
    private function prepareConstraintsAndIndexes(array $foreignKeys, array $indexes, $tableName)
    {
        //Process foreign keys
        foreach ($foreignKeys as $name => $foreignKey) {
            $this->addForeignKey(
                $name,
                $tableName,
                $foreignKey['COLUMN_NAME'],
                $foreignKey['REF_TABLE_NAME'],
                $foreignKey['REF_COLUMN_NAME'],
                $foreignKey['ON_DELETE']
            );
        }
        //Process indexes
        foreach ($indexes as $name => $index) {
            $this->addIndex(
                $tableName,
                $name,
                $index['COLUMNS'],
                $index['TYPE']
            );
        }
    }

    /**
     * Create table
     *
     * @param Table $table
     */
    public function createTable(Table $table)
    {
        $this->prepareColumns($table->getName(), $table->getColumns());
        $this->prepareConstraintsAndIndexes($table->getForeignKeys(), $table->getIndexes(), $table->getName());
    }

    /**
     * Flush all tables
     *
     * @return void
     */
    public function flush()
    {
        $this->tables = [];
    }

    /**
     * @param string $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }
}