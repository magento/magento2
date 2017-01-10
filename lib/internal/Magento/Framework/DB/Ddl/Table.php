<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Data Definition for table
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\DB\Ddl;

use Magento\Framework\DB\Adapter\AdapterInterface;

class Table
{
    /**
     * Types of columns
     */
    const TYPE_BOOLEAN = 'boolean';

    const TYPE_SMALLINT = 'smallint';

    const TYPE_INTEGER = 'integer';

    const TYPE_BIGINT = 'bigint';

    const TYPE_FLOAT = 'float';

    const TYPE_NUMERIC = 'numeric';

    const TYPE_DECIMAL = 'decimal';

    const TYPE_DATE = 'date';

    const TYPE_TIMESTAMP = 'timestamp';

    // Capable to support date-time from 1970 + auto-triggers in some RDBMS
    const TYPE_DATETIME = 'datetime';

    // Capable to support long date-time before 1970
    const TYPE_TEXT = 'text';

    const TYPE_BLOB = 'blob';

    // Used for back compatibility, when query param can't use statement options
    const TYPE_VARBINARY = 'varbinary';

    // A real blob, stored as binary inside DB

    /**
     * Default and maximal TEXT and BLOB columns sizes we can support for different DB systems.
     */
    const DEFAULT_TEXT_SIZE = 1024;

    const MAX_TEXT_SIZE = 2147483648;

    const MAX_VARBINARY_SIZE = 2147483648;

    /**
     * Default values for timestamps - fill with current timestamp on inserting record, on changing and both cases
     */
    const TIMESTAMP_INIT_UPDATE = 'TIMESTAMP_INIT_UPDATE';

    const TIMESTAMP_INIT = 'TIMESTAMP_INIT';

    const TIMESTAMP_UPDATE = 'TIMESTAMP_UPDATE';

    /**
     * Actions used for foreign keys
     */
    const ACTION_CASCADE = 'CASCADE';

    const ACTION_SET_NULL = 'SET NULL';

    const ACTION_NO_ACTION = 'NO ACTION';

    const ACTION_RESTRICT = 'RESTRICT';

    const ACTION_SET_DEFAULT = 'SET DEFAULT';

    /**
     * Name of table
     *
     * @var string
     */
    protected $_tableName;

    /**
     * Schema name
     *
     * @var string
     */
    protected $_schemaName;

    /**
     * Comment for Table
     *
     * @var string
     */
    protected $_tableComment;

    /**
     * Column descriptions for a table
     *
     * Is an associative array keyed by the uppercase column name
     * The value of each array element is an associative array
     * with the following keys:
     *
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; constant datatype of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR/INT
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     * COMMENT          => string; column description
     *
     * @var array
     */
    protected $_columns = [];

    /**
     * Index descriptions for a table
     *
     * Is an associative array keyed by the uppercase index name
     * The value of each array element is an associative array
     * with the following keys:
     *
     * INDEX_NAME       => string; index name
     * COLUMNS          => array; array of index columns
     * TYPE             => string; Optional special index type
     *
     * COLUMNS is an associative array keyed by the uppercase column name
     * The value of each COLUMNS array element is an associative array
     * with the following keys:
     *
     * NAME             => string; The column name
     * SIZE             => int|null; Length of index column (always null if index is unique)
     * POSITION         => int; Position in index
     *
     * @var array
     */
    protected $_indexes = [];

    /**
     * Foreign key descriptions for a table
     *
     * Is an associative array keyed by the uppercase foreign key name
     * The value of each array element is an associative array
     * with the following keys:
     *
     * FK_NAME          => string; The foreign key name
     * COLUMN_NAME      => string; The column name in table
     * REF_TABLE_NAME   => string; Reference table name
     * REF_COLUMN_NAME  => string; Reference table column name
     * ON_DELETE        => string; Integrity Actions, default NO ACTION
     * ON_UPDATE        => string; Integrity Actions, default NO ACTION
     *
     * Valid Integrity Actions:
     * CASCADE | SET NULL | NONE | RESTRICT | SET DEFAULT
     *
     * @var array
     */
    protected $_foreignKeys = [];

    /**
     * Additional table options
     *
     * @var array
     */
    protected $_options = ['type' => 'INNODB', 'charset' => 'utf8', 'collate' => 'utf8_general_ci'];

    /**
     * Set table name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_tableName = $name;
        if ($this->_tableComment === null) {
            $this->_tableComment = $name;
        }
        return $this;
    }

    /**
     * Set schema name
     *
     * @param string $name
     * @return $this
     */
    public function setSchema($name)
    {
        $this->_schemaName = $name;
        return $this;
    }

    /**
     * Set comment for table
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->_tableComment = $comment;
        return $this;
    }

    /**
     * Retrieve name of table
     *
     * @return string
     * @throws \Zend_Db_Exception
     */
    public function getName()
    {
        if ($this->_tableName === null) {
            throw new \Zend_Db_Exception('Table name is not defined');
        }
        return $this->_tableName;
    }

    /**
     * Get schema name
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schemaName;
    }

    /**
     * Return comment for table
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_tableComment;
    }

    /**
     * Adds column to table.
     *
     * $options contains additional options for columns. Supported values are:
     * - 'unsigned', for number types only. Default: FALSE.
     * - 'precision', for numeric and decimal only. Default: taken from $size, if not set there then 0.
     * - 'scale', for numeric and decimal only. Default: taken from $size, if not set there then 10.
     * - 'default'. Default: not set.
     * - 'nullable'. Default: TRUE.
     * - 'primary', add column to primary index. Default: do not add.
     * - 'primary_position', only for column in primary index. Default: count of primary columns + 1.
     * - 'identity' or 'auto_increment'. Default: FALSE.
     *
     * @param string $name the column name
     * @param string $type the column data type
     * @param string|int|array $size the column length
     * @param array $options array of additional options
     * @param string $comment column description
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function addColumn($name, $type, $size = null, $options = [], $comment = null)
    {
        $position = count($this->_columns);
        $default = false;
        $nullable = true;
        $length = null;
        $scale = null;
        $precision = null;
        $unsigned = false;
        $primary = false;
        $primaryPosition = 0;
        $identity = false;

        // Prepare different properties
        switch ($type) {
            case self::TYPE_BOOLEAN:
                break;

            case self::TYPE_SMALLINT:
            case self::TYPE_INTEGER:
            case self::TYPE_BIGINT:
                if (!empty($options['unsigned'])) {
                    $unsigned = true;
                }

                break;

            case self::TYPE_FLOAT:
                if (!empty($options['unsigned'])) {
                    $unsigned = true;
                }
                break;

            case self::TYPE_DECIMAL:
            case self::TYPE_NUMERIC:
                $match = [];
                $scale = 0;
                $precision = 10;
                // parse size value
                if (is_array($size)) {
                    if (count($size) == 2) {
                        $size = array_values($size);
                        $precision = $size[0];
                        $scale = $size[1];
                    }
                } elseif (preg_match('#^(\d+),(\d+)$#', $size, $match)) {
                    $precision = $match[1];
                    $scale = $match[2];
                }
                // check options
                if (isset($options['precision'])) {
                    $precision = $options['precision'];
                }

                if (isset($options['scale'])) {
                    $scale = $options['scale'];
                }

                if (!empty($options['unsigned'])) {
                    $unsigned = true;
                }
                break;
            case self::TYPE_DATE:
            case self::TYPE_DATETIME:
            case self::TYPE_TIMESTAMP:
                break;
            case self::TYPE_TEXT:
            case self::TYPE_BLOB:
            case self::TYPE_VARBINARY:
                $length = $size;
                break;
            default:
                throw new \Zend_Db_Exception('Invalid column data type "' . $type . '"');
        }

        if (array_key_exists('default', $options)) {
            $default = $options['default'];
        }
        if (array_key_exists('nullable', $options)) {
            $nullable = (bool)$options['nullable'];
        }
        if (!empty($options['primary'])) {
            $primary = true;
            if (isset($options['primary_position'])) {
                $primaryPosition = (int)$options['primary_position'];
            } else {
                $primaryPosition = 0;
                foreach ($this->_columns as $v) {
                    if ($v['PRIMARY']) {
                        $primaryPosition++;
                    }
                }
            }
        }
        if (!empty($options['identity']) || !empty($options['auto_increment'])) {
            $identity = true;
        }

        if ($comment === null) {
            $comment = ucfirst($name);
        }

        $upperName = strtoupper($name);
        $this->_columns[$upperName] = [
            'COLUMN_NAME' => $name,
            'COLUMN_TYPE' => $type,
            'COLUMN_POSITION' => $position,
            'DATA_TYPE' => $type,
            'DEFAULT' => $default,
            'NULLABLE' => $nullable,
            'LENGTH' => $length,
            'SCALE' => $scale,
            'PRECISION' => $precision,
            'UNSIGNED' => $unsigned,
            'PRIMARY' => $primary,
            'PRIMARY_POSITION' => $primaryPosition,
            'IDENTITY' => $identity,
            'COMMENT' => $comment,
        ];

        return $this;
    }

    /**
     * Add Foreign Key to table
     *
     * @param string $fkName        the foreign key name
     * @param string $column        the foreign key column name
     * @param string $refTable      the reference table name
     * @param string $refColumn     the reference table column name
     * @param string $onDelete      the action on delete row
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addForeignKey($fkName, $column, $refTable, $refColumn, $onDelete = null)
    {
        $upperName = strtoupper($fkName);

        // validate column name
        if (!isset($this->_columns[strtoupper($column)])) {
            throw new \Zend_Db_Exception('Undefined column "' . $column . '"');
        }

        switch ($onDelete) {
            case self::ACTION_CASCADE:
            case self::ACTION_RESTRICT:
            case self::ACTION_SET_DEFAULT:
            case self::ACTION_SET_NULL:
                break;
            default:
                $onDelete = self::ACTION_NO_ACTION;
        }

        $this->_foreignKeys[$upperName] = [
            'FK_NAME' => $fkName,
            'COLUMN_NAME' => $column,
            'REF_TABLE_NAME' => $refTable,
            'REF_COLUMN_NAME' => $refColumn,
            'ON_DELETE' => $onDelete
        ];

        return $this;
    }

    /**
     * Add index to table
     *
     * @param string $indexName the index name
     * @param array|string $fields array of columns or column string
     * @param array $options array of additional options
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addIndex($indexName, $fields, $options = [])
    {
        $idxType = AdapterInterface::INDEX_TYPE_INDEX;
        $position = 0;
        $columns = [];
        if (!is_array($fields)) {
            $fields = [$fields];
        }

        foreach ($fields as $columnData) {
            $columnSize = null;
            $columnPos = $position;
            if (is_string($columnData)) {
                $columnName = $columnData;
            } elseif (is_array($columnData)) {
                if (!isset($columnData['name'])) {
                    throw new \Zend_Db_Exception('Invalid index column data');
                }

                $columnName = $columnData['name'];
                if (!empty($columnData['size'])) {
                    $columnSize = (int)$columnData['size'];
                }
                if (!empty($columnData['position'])) {
                    $columnPos = (int)$columnData['position'];
                }
            } else {
                continue;
            }

            $columns[strtoupper(
                $columnName
            )] = [
                'NAME' => $columnName,
                'SIZE' => $columnSize,
                'POSITION' => $columnPos,
            ];

            $position++;
        }

        if (empty($columns)) {
            throw new \Zend_Db_Exception('Columns for index are not defined');
        }

        if (!empty($options['type'])) {
            $idxType = $options['type'];
        }

        $this->_indexes[strtoupper(
            $indexName
        )] = [
            'INDEX_NAME' => $indexName,
            'COLUMNS' => $this->_normalizeIndexColumnPosition($columns),
            'TYPE' => $idxType,
        ];

        return $this;
    }

    /**
     * Retrieve array of table columns
     *
     * @param bool $normalized
     * @see $this->_columns
     * @return array
     */
    public function getColumns($normalized = true)
    {
        if ($normalized) {
            return $this->_normalizeColumnPosition($this->_columns);
        }
        return $this->_columns;
    }

    /**
     * Set column, formatted according to DDL Table format, into columns structure
     *
     * @param array $column
     * @see $this->_columns
     * @return $this
     */
    public function setColumn($column)
    {
        $upperName = strtoupper($column['COLUMN_NAME']);
        $this->_columns[$upperName] = $column;
        return $this;
    }

    /**
     * Retrieve array of table indexes
     *
     * @see $this->_indexes
     * @return array
     */
    public function getIndexes()
    {
        return $this->_indexes;
    }

    /**
     * Retrieve array of table foreign keys
     *
     * @see $this->_foreignKeys
     * @return array
     */
    public function getForeignKeys()
    {
        return $this->_foreignKeys;
    }

    /**
     * Set table option
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * Retrieve table option value by option name
     * Return null if option does not exits
     *
     * @param string $key
     * @return null|string
     */
    public function getOption($key)
    {
        if (!isset($this->_options[$key])) {
            return null;
        }
        return $this->_options[$key];
    }

    /**
     * Retrieve array of table options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Index column position comparison function
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortIndexColumnPosition($a, $b)
    {
        return $a['POSITION'] - $b['POSITION'];
    }

    /**
     * table column position comparison function
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortColumnPosition($a, $b)
    {
        return $a['COLUMN_POSITION'] - $b['COLUMN_POSITION'];
    }

    /**
     * Normalize positon of index columns array
     *
     * @param array $columns
     * @return array
     */
    protected function _normalizeIndexColumnPosition($columns)
    {
        uasort($columns, [$this, '_sortIndexColumnPosition']);
        $position = 0;
        foreach (array_keys($columns) as $columnId) {
            $columns[$columnId]['POSITION'] = $position;
            $position++;
        }
        return $columns;
    }

    /**
     * Normalize positon of table columns array
     *
     * @param array $columns
     * @return array
     */
    protected function _normalizeColumnPosition($columns)
    {
        uasort($columns, [$this, '_sortColumnPosition']);
        $position = 0;
        foreach (array_keys($columns) as $columnId) {
            $columns[$columnId]['COLUMN_POSITION'] = $position;
            $position++;
        }
        return $columns;
    }
}
