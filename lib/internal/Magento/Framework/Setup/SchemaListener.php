<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaListenerDefinition\DefinitionConverterInterface;
use Magento\Framework\Setup\SchemaListenerHandlers\SchemaListenerHandlerInterface;

/**
 * Listen for all changes and record them in order to reuse later.
 */
class SchemaListener
{
    /**
     * Ignore all ddl queries.
     */
    const IGNORE_ON = 0;

    /**
     * Disable ignore mode.
     */
    const IGNORE_OFF = 1;

    /**
     * Staging FK keys installer key. Indicates that changes should be moved from ordinary module to staging module.
     */
    const STAGING_FK_KEYS = 2;

    /**
     * @var array
     */
    private $tables = [];

    /**
     * @var string
     */
    private $resource;

    /**
     * This flag allows to ignore some DDL operations.
     *
     * @var bool
     */
    private $ignore = self::IGNORE_OFF;

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
     * @var SchemaListenerHandlerInterface[]
     */
    private $handlers;

    /**
     * Constructor.
     *
     * @param array $definitionMappers
     * @param array $handlers
     */
    public function __construct(
        array $definitionMappers,
        array $handlers = []
    ) {
        $this->definitionMappers = $definitionMappers;
        $this->handlers = $handlers;
    }

    /**
     * Drop foreign key in table by name.
     *
     * @param string $tableName
     * @param string $fkName
     */
    public function dropForeignKey($tableName, $fkName)
    {
        $dataToLog['constraints']['foreign'][$fkName] = [
            'disabled' => true,
        ];
        $this->log($tableName, $dataToLog);
    }

    /**
     * Cast column definition.
     *
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
        $definition['name'] = strtolower($columnName);
        $definitionType = $definition['type'] === 'int' ? 'integer' : $definition['type'];
        $definition = $this->definitionMappers[$definitionType]->convertToDefinition($definition);
        if (isset($definition['default']) && $definition['default'] === false) {
            $definition['default'] = null; //uniform default values
        }
        $definition['disabled'] = false;

        return $definition;
    }

    /**
     * Add primary key if exists in definition.
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     * @param string $primaryKeyName
     * @return array
     */
    private function addPrimaryKeyIfExists($tableName, $columnName, $definition, $primaryKeyName)
    {
        if (isset($definition['primary']) && $definition['primary']) {
            $dataToLog['constraints']['primary'][$primaryKeyName] = [
                'type' => 'primary',
                'name' => $primaryKeyName,
                'disabled' => false,
                'columns' => [$columnName => strtolower($columnName)]
            ];

            $this->log($tableName, $dataToLog);
        }

        unset($definition['primary']);
        return $definition;
    }

    /**
     * Rename table.
     *
     * @param string $oldTableName
     * @param string $newTableName
     * @return void
     */
    public function renameTable($oldTableName, $newTableName)
    {
        $moduleName = $this->getModuleName();

        if (isset($this->tables[$moduleName][strtolower($oldTableName)])) {
            $this->tables[$moduleName][strtolower($newTableName)] =
                $this->tables[$moduleName][strtolower($oldTableName)];
            unset($this->tables[$moduleName][strtolower($oldTableName)]);
        }
    }

    /**
     * Process definition to not specific format.
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
     * Add column.
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     * @param string $primaryKeyName
     * @param null $onCreate
     */
    public function addColumn($tableName, $columnName, $definition, $primaryKeyName = 'PRIMARY', $onCreate = null)
    {
        $definition = $this->castColumnDefinition($definition, $columnName);
        $definition = $this->addPrimaryKeyIfExists($tableName, $columnName, $definition, $primaryKeyName);
        $definition['onCreate'] = $onCreate;
        $dataToLog['columns'][strtolower($columnName)] = $definition;
        $this->log($tableName, $dataToLog);
    }

    /**
     * Drop index.
     *
     * @param string $tableName
     * @param string $keyName
     * @param string $indexType
     */
    public function dropIndex($tableName, $keyName, $indexType)
    {
        if ($indexType === 'index') {
            $dataToLog['indexes'][$keyName] = [
                'disabled' => true
            ];
        } else {
            $dataToLog['constraints'][$indexType][$keyName] = [
                'disabled' => true
            ];
        }

        $this->log($tableName, $dataToLog);
    }

    /**
     * Do drop column.
     *
     * @param string $tableName
     * @param string $columnName
     */
    public function dropColumn($tableName, $columnName)
    {
        $dataToLog['columns'][strtolower($columnName)] = [
            'disabled' => true
        ];
        $this->log($tableName, $dataToLog);
    }

    /**
     * Change column is the same as rename.
     *
     * @param string $tableName
     * @param string $oldColumnName
     * @param string $newColumnName
     * @param array $definition
     */
    public function changeColumn($tableName, $oldColumnName, $newColumnName, $definition)
    {
        foreach ($this->handlers as $handler) {
            $this->tables = $handler->handle(
                $this->moduleName,
                $this->tables,
                [
                    'table' => $tableName,
                    'old_column' => $oldColumnName,
                    'new_column' => $newColumnName,
                ],
                $definition
            );
        }

        $this->dropColumn($tableName, $oldColumnName);
        $this->addColumn(
            $tableName,
            $newColumnName,
            $definition,
            'STAGING_PRIMARY',
            sprintf('migrateDataFrom(%s)', $oldColumnName)
        );
    }

    /**
     * Modify column.
     *
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     */
    public function modifyColumn($tableName, $columnName, $definition)
    {
        $this->addColumn($tableName, $columnName, $definition);
    }

    /**
     * Retrieved processed module name.
     *
     * @return string
     */
    private function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Log any change done.
     *
     * @param string $tableName
     * @param array $dataToLog
     * @return void
     */
    public function log($tableName, array $dataToLog)
    {
        if ($this->ignore & self::IGNORE_OFF === 0) {
            return;
        }
        $moduleName = $this->getModuleName();
        if (isset($this->tables[$moduleName][strtolower($tableName)])) {
            $this->tables[$moduleName][strtolower($tableName)] = array_replace_recursive(
                $this->tables[$moduleName][strtolower($tableName)],
                $dataToLog
            );
        } else {
            $this->tables[$moduleName][strtolower($tableName)] = $dataToLog;
        }

        $this->tables[$moduleName][strtolower($tableName)]['resource'] = $this->resource;
    }

    /**
     * Add foreign key constraint.
     *
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
        $dataToLog['constraints']['foreign'][$fkName] =
            [
                'table' => strtolower($tableName),
                'column' => strtolower($columnName),
                'referenceTable' => strtolower($refTableName),
                'referenceColumn' => strtolower($refColumnName),
                'onDelete' => $onDelete,
                'disabled' => false
            ];
        $this->log($tableName, $dataToLog);
    }

    /**
     * Convert all index columns to lower case.
     *
     * @param array $indexColumns
     * @return array
     */
    private function prepareIndexColumns(array $indexColumns)
    {
        $columnNames = [];

        foreach ($indexColumns as $key => $indexColumn) {
            if (is_array($indexColumn)) {
                $columnNames[strtolower($key)] = strtolower($key);
            } else {
                $columnNames[$indexColumn] = $indexColumn;
            }
        }

        return $columnNames;
    }

    /**
     * Add index for table column(s).
     *
     * @param string $tableName
     * @param string $indexName
     * @param array $fields
     * @param string $indexType
     * @param string $indexAlhoritm
     */
    public function addIndex(
        $tableName,
        $indexName,
        $fields,
        $indexType = AdapterInterface::INDEX_TYPE_INDEX,
        $indexAlhoritm = 'btree'
    ) {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        if ($indexType == AdapterInterface::INDEX_TYPE_FULLTEXT || $indexType === AdapterInterface::INDEX_TYPE_INDEX) {
            if ($indexType === AdapterInterface::INDEX_TYPE_INDEX) {
                $indexType = $indexAlhoritm;
            }
            $dataToLog['indexes'][$indexName] =
                [
                    'columns' => $this->prepareIndexColumns($fields),
                    'indexType' => $indexType,
                    'disabled' => false
                ];
        } else {
            $dataToLog['constraints'][$indexType][$indexName] =
                ['columns' => $this->prepareIndexColumns($fields), 'disabled' => false];
        }

        $this->log($tableName, $dataToLog);
    }

    /**
     * Prepare table columns to registration.
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
     * Convert constraints from old format to new one.
     *
     * @param array $foreignKeys
     * @param array $indexes
     * @param string $tableName
     */
    private function prepareConstraintsAndIndexes(array $foreignKeys, array $indexes, $tableName, $engine)
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
                $index['TYPE'],
                $engine === 'memory' ? 'hash' : 'btree'
            );
        }
    }

    /**
     * Create table.
     *
     * @param Table $table
     */
    public function createTable(Table $table)
    {
        $engine = strtolower($table->getOption('type'));
        $this->tables[$this->getModuleName()][strtolower($table->getName())]['engine'] = $engine;
        $this->prepareColumns($table->getName(), $table->getColumns());
        $this->prepareConstraintsAndIndexes($table->getForeignKeys(), $table->getIndexes(), $table->getName(), $engine);
    }

    /**
     * Flush all tables.
     *
     * @return void
     */
    public function flush()
    {
        $this->tables = [];
    }

    /**
     * Turn on/off ignore mode.
     *
     * @param bool $flag
     */
    public function toogleIgnore($flag)
    {
        $this->ignore = $flag;
    }

    /**
     * Drop table.
     *
     * @param $tableName
     */
    public function dropTable($tableName)
    {
        if (isset($this->tables[$this->getModuleName()][strtolower($tableName)])) {
            unset($this->tables[$this->getModuleName()][strtolower($tableName)]);
        } else {
            $this->tables[$this->getModuleName()][strtolower($tableName)]['disabled'] = true;
        }
    }

    /**
     * Set resource.
     *
     * @param string $resource
     */
    public function setResource(string $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Set module name.
     *
     * @param string $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * Get tables.
     *
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }
}
