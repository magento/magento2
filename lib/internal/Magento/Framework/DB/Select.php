<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class for SQL SELECT generation and results.
 *
 * @api
 * @method \Magento\Framework\DB\Select from($name, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select join($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinInner($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinLeft($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinNatural($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinFull($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinRight($name, $cond, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select joinCross($name, $cols = '*', $schema = null)
 * @method \Magento\Framework\DB\Select orWhere($cond, $value = null, $type = null)
 * @method \Magento\Framework\DB\Select group($spec)
 * @method \Magento\Framework\DB\Select order($spec)
 * @method \Magento\Framework\DB\Select limitPage($page, $rowCount)
 * @method \Magento\Framework\DB\Select forUpdate($flag = true)
 * @method \Magento\Framework\DB\Select distinct($flag = true)
 * @method \Magento\Framework\DB\Select reset($part = null)
 * @method \Magento\Framework\DB\Select columns($cols = '*', $correlationName = null)
 */
class Select extends \Zend_Db_Select
{
    /**
     * Condition type
     */
    const TYPE_CONDITION = 'TYPE_CONDITION';

    /**
     * Straight join key
     */
    const STRAIGHT_JOIN = 'straightjoin';

    /**
     * Sql straight join
     */
    const SQL_STRAIGHT_JOIN = 'STRAIGHT_JOIN';

    /**
     * @var Select\SelectRenderer
     * @since 2.1.1
     */
    private $selectRenderer;

    /**
     * Class constructor
     * Add straight join support
     *
     * @param Adapter\Pdo\Mysql $adapter
     * @param Select\SelectRenderer $selectRenderer
     * @param array $parts
     */
    public function __construct(
        \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter,
        \Magento\Framework\DB\Select\SelectRenderer $selectRenderer,
        $parts = []
    ) {
        self::$_partsInit = array_merge(self::$_partsInit, $parts);
        if (!isset(self::$_partsInit[self::STRAIGHT_JOIN])) {
            self::$_partsInit = [self::STRAIGHT_JOIN => false] + self::$_partsInit;
        }

        $this->selectRenderer = $selectRenderer;
        parent::__construct($adapter);
    }

    /**
     * Adds a WHERE condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. Array values are quoted and comma-separated.
     *
     * <code>
     * // simplest but non-secure
     * $select->where("id = $id");
     *
     * // secure (ID is quoted but matched anyway)
     * $select->where('id = ?', $id);
     *
     * // alternatively, with named binding
     * $select->where('id = :id');
     * </code>
     *
     * Note that it is more correct to use named bindings in your
     * queries for values other than strings. When you use named
     * bindings, don't forget to pass the values when actually
     * making a query:
     *
     * <code>
     * $db->fetchAll($select, array('id' => 5));
     * </code>
     *
     * @param string $cond The WHERE condition.
     * @param string $value OPTIONAL A single value to quote into the condition.
     * @param string|int|null $type OPTIONAL The type of the given value
     * @return \Magento\Framework\DB\Select
     */
    public function where($cond, $value = null, $type = null)
    {
        if ($value === null && $type === null) {
            $value = '';
        } elseif ($type == self::TYPE_CONDITION) {
            $type = null;
        }
        if (is_array($value)) {
            $cond = $this->getConnection()->quoteInto($cond, $value);
            $value = null;
        }
        return parent::where($cond, $value, $type);
    }

    /**
     * Reset unused LEFT JOIN(s)
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function resetJoinLeft()
    {
        foreach ($this->_parts[self::FROM] as $tableId => $tableProp) {
            if ($tableProp['joinType'] == self::LEFT_JOIN) {
                $useJoin = false;
                foreach ($this->_parts[self::COLUMNS] as $columnEntry) {
                    list($correlationName, $column) = $columnEntry;
                    if ($column instanceof \Zend_Db_Expr) {
                        if ($this->_findTableInCond(
                            $tableId,
                            $column
                        ) || $this->_findTableInCond(
                            $tableProp['tableName'],
                            $column
                        )
                        ) {
                            $useJoin = true;
                        }
                    } else {
                        if ($correlationName == $tableId) {
                            $useJoin = true;
                        }
                    }
                }
                foreach ($this->_parts[self::WHERE] as $where) {
                    if ($this->_findTableInCond(
                        $tableId,
                        $where
                    ) || $this->_findTableInCond(
                        $tableProp['tableName'],
                        $where
                    )
                    ) {
                        $useJoin = true;
                    }
                }

                $joinUseInCond = $useJoin;
                $joinInTables = [];

                foreach ($this->_parts[self::FROM] as $tableCorrelationName => $table) {
                    if ($tableCorrelationName == $tableId) {
                        continue;
                    }
                    if (!empty($table['joinCondition'])) {
                        if ($this->_findTableInCond(
                            $tableId,
                            $table['joinCondition']
                        ) || $this->_findTableInCond(
                            $tableProp['tableName'],
                            $table['joinCondition']
                        )
                        ) {
                            $useJoin = true;
                            $joinInTables[] = $tableCorrelationName;
                        }
                    }
                }

                if (!$useJoin) {
                    unset($this->_parts[self::FROM][$tableId]);
                } else {
                    $this->_parts[self::FROM][$tableId]['useInCond'] = $joinUseInCond;
                    $this->_parts[self::FROM][$tableId]['joinInTables'] = $joinInTables;
                }
            }
        }

        $this->_resetJoinLeft();

        return $this;
    }

    /**
     * Validate LEFT joins, and remove it if not exists
     *
     * @return $this
     */
    protected function _resetJoinLeft()
    {
        foreach ($this->_parts[self::FROM] as $tableId => $tableProp) {
            if ($tableProp['joinType'] == self::LEFT_JOIN) {
                if ($tableProp['useInCond']) {
                    continue;
                }

                $used = false;
                foreach ($tableProp['joinInTables'] as $table) {
                    if (isset($this->_parts[self::FROM][$table])) {
                        $used = true;
                        break;
                    }
                }

                if (!$used) {
                    unset($this->_parts[self::FROM][$tableId]);
                    return $this->_resetJoinLeft();
                }
            }
        }

        return $this;
    }

    /**
     * Find table name in condition (where, column)
     *
     * @param string $table
     * @param string $cond
     * @return bool
     */
    protected function _findTableInCond($table, $cond)
    {
        $quote = $this->_adapter->getQuoteIdentifierSymbol();

        if (strpos($cond, $quote . $table . $quote . '.') !== false) {
            return true;
        }

        $position = 0;
        $result = 0;
        $needle = [];
        while (is_integer($result)) {
            $result = strpos($cond, $table . '.', $position);

            if (is_integer($result)) {
                $needle[] = $result;
                $position = $result + strlen($table) + 1;
            }
        }

        if (!$needle) {
            return false;
        }

        foreach ($needle as $position) {
            if ($position == 0) {
                return true;
            }
            if (!preg_match('#[a-z0-9_]#is', substr($cond, $position - 1, 1))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Populate the {@link $_parts} 'join' key
     *
     * Does the dirty work of populating the join key.
     *
     * The $name and $cols parameters follow the same logic
     * as described in the from() method.
     *
     * @param  null|string $type Type of join; inner, left, and null are currently supported
     * @param  array|string|\Zend_Db_Expr $name Table name
     * @param  string $cond Join on this condition
     * @param  array|string $cols The columns to select from the joined table
     * @param  string $schema The database name to specify, if any.
     * @return \Magento\Framework\DB\Select This \Magento\Framework\DB\Select object
     * @throws \Zend_Db_Select_Exception
     */
    protected function _join($type, $name, $cond, $cols, $schema = null)
    {
        if ($type == self::INNER_JOIN && empty($cond)) {
            $type = self::CROSS_JOIN;
        }
        return parent::_join($type, $name, $cond, $cols, $schema);
    }

    /**
     * Sets a limit count and offset to the query.
     *
     * @param int $count OPTIONAL The number of rows to return.
     * @param int $offset OPTIONAL Start returning after this many rows.
     * @return $this
     */
    public function limit($count = null, $offset = null)
    {
        if ($count === null) {
            $this->reset(self::LIMIT_COUNT);
        } else {
            $this->_parts[self::LIMIT_COUNT] = (int)$count;
        }
        if ($offset === null) {
            $this->reset(self::LIMIT_OFFSET);
        } else {
            $this->_parts[self::LIMIT_OFFSET] = (int)$offset;
        }
        return $this;
    }

    /**
     * Cross Table Update From Current select
     *
     * @param string|array $table
     * @return string
     */
    public function crossUpdateFromSelect($table)
    {
        return $this->getConnection()->updateFromSelect($this, $table);
    }

    /**
     * Insert to table from current select
     *
     * @param string $tableName
     * @param array $fields
     * @param bool $onDuplicate
     * @return string
     */
    public function insertFromSelect($tableName, $fields = [], $onDuplicate = true)
    {
        $mode = $onDuplicate ? AdapterInterface::INSERT_ON_DUPLICATE : false;
        return $this->getConnection()->insertFromSelect($this, $tableName, $fields, $mode);
    }

    /**
     * Generate INSERT IGNORE query to the table from current select
     *
     * @param string $tableName
     * @param array $fields
     * @return string
     */
    public function insertIgnoreFromSelect($tableName, $fields = [])
    {
        return $this->getConnection()->insertFromSelect($this, $tableName, $fields, AdapterInterface::INSERT_IGNORE);
    }

    /**
     * Retrieve DELETE query from select
     *
     * @param string $table The table name or alias
     * @return string
     */
    public function deleteFromSelect($table)
    {
        return $this->getConnection()->deleteFromSelect($this, $table);
    }

    /**
     * Modify (hack) part of the structured information for the current query
     *
     * @param string $part
     * @param mixed $value
     * @return $this
     * @throws \Zend_Db_Select_Exception
     */
    public function setPart($part, $value)
    {
        $part = strtolower($part);
        if (!array_key_exists($part, $this->_parts)) {
            throw new \Zend_Db_Select_Exception("Invalid Select part '{$part}'");
        }
        $this->_parts[$part] = $value;
        return $this;
    }

    /**
     * Use a STRAIGHT_JOIN for the SQL Select
     *
     * @param bool $flag Whether or not the SELECT use STRAIGHT_JOIN (default true).
     * @return $this
     */
    public function useStraightJoin($flag = true)
    {
        $this->_parts[self::STRAIGHT_JOIN] = (bool)$flag;
        return $this;
    }

    /**
     * Render STRAIGHT_JOIN clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderStraightjoin($sql)
    {
        if ($this->_adapter->supportStraightJoin() && !empty($this->_parts[self::STRAIGHT_JOIN])) {
            $sql .= ' ' . self::SQL_STRAIGHT_JOIN;
        }

        return $sql;
    }

    /**
     * Adds to the internal table-to-column mapping array.
     *
     * @param  string $correlationName The table/join the columns come from.
     * @param  array|string $cols The list of columns; preferably as an array,
     *     but possibly as a string containing one column.
     * @param  bool|string $afterCorrelationName True if it should be prepended,
     *     a correlation name if it should be inserted
     * @return void
     */
    protected function _tableCols($correlationName, $cols, $afterCorrelationName = null)
    {
        if (!is_array($cols)) {
            $cols = [$cols];
        }

        foreach ($cols as $k => $v) {
            if ($v instanceof Select) {
                $cols[$k] = new \Zend_Db_Expr(sprintf('(%s)', $v->assemble()));
            }
        }

        return parent::_tableCols($correlationName, $cols, $afterCorrelationName);
    }

    /**
     * Adds the random order to query
     *
     * @param string $field     integer field name
     * @return $this
     */
    public function orderRand($field = null)
    {
        $this->_adapter->orderRand($this, $field);
        return $this;
    }

    /**
     * Render FOR UPDATE clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderForupdate($sql)
    {
        if ($this->_parts[self::FOR_UPDATE]) {
            $sql = $this->_adapter->forUpdate($sql);
        }

        return $sql;
    }

    /**
     * Add EXISTS clause
     *
     * @param  Select $select
     * @param  string           $joinCondition
     * @param   bool            $isExists
     * @return $this
     */
    public function exists($select, $joinCondition, $isExists = true)
    {
        if ($isExists) {
            $exists = 'EXISTS (%s)';
        } else {
            $exists = 'NOT EXISTS (%s)';
        }
        $select->reset(self::COLUMNS)->columns([new \Zend_Db_Expr('1')])->where($joinCondition);

        $exists = sprintf($exists, $select->assemble());

        $this->where($exists);
        return $this;
    }

    /**
     * Get adapter
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        return $this->_adapter;
    }

    /**
     * Converts this object to an SQL SELECT string.
     *
     * @return string|null This object as a SELECT string. (or null if a string cannot be produced.)
     * @since 2.1.0
     */
    public function assemble()
    {
        return $this->selectRenderer->render($this);
    }

    /**
     * @return string[]
     * @since 2.0.9
     */
    public function __sleep()
    {
        $properties = array_keys(get_object_vars($this));
        $properties = array_diff(
            $properties,
            [
                '_adapter',
                'selectRenderer'
            ]
        );
        return $properties;
    }

    /**
     * Init not serializable fields
     *
     * @return void
     * @since 2.0.9
     */
    public function __wakeup()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_adapter = $objectManager->get(ResourceConnection::class)->getConnection();
        $this->selectRenderer = $objectManager->get(\Magento\Framework\DB\Select\SelectRenderer::class);
    }
}
