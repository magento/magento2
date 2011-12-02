<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Resource helper class for MySql Varien DB Adapter
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Abstract
{
    /**
     * Returns expresion for field unification
     *
     * @param string $field
     * @return Zend_Db_Expr
     */
    public function castField($field)
    {
        return $field;
    }
    /**
     * Returns analytic expression for database column
     *
     * @param string $column
     * @param string $groupAliasName OPTIONAL
     * @param string $orderBy OPTIONAL
     * @return Zend_Db_Expr
     */
    public function prepareColumn($column, $groupAliasName = null, $orderBy = null)
    {
        return new Zend_Db_Expr((string)$column);
    }

    /**
     * Returns select query with analytic functions
     *
     * @param Varien_Db_Select $select
     * @return string
     */
    public function getQueryUsingAnalyticFunction(Varien_Db_Select $select)
    {
        return $select->assemble();
    }

    /**
     *
     * Returns Insert From Select On Duplicate query with analytic functions
     *
     * @param Varien_Db_Select $select
     * @param string $table
     * @param array $table
     * @return string
     */
    public function getInsertFromSelectUsingAnalytic(Varien_Db_Select $select, $table, $fields)
    {
        return $select->insertFromSelect($table, $fields);
    }

    /**
     * Correct limitation of queries with UNION
     * No need to do additional actions on MySQL
     *
     * @param Varien_Db_Select $select
     * @return Varien_Db_Select
     */
    public function limitUnion($select)
    {
        return $select;
    }

    /**
     * Returns array of quoted orders with direction
     *
     * @param Varien_Db_Select $select
     * @param bool $autoReset
     * @return array
     */
    protected function _prepareOrder(Varien_Db_Select $select, $autoReset = false)
    {
        $selectOrders = $select->getPart(Zend_Db_Select::ORDER);
        if (!$selectOrders) {
            return array();
        }

        $orders = array();
        foreach ($selectOrders as $term) {
            if (is_array($term)) {
                if (!is_numeric($term[0])) {
                    $orders[]   = sprintf('%s %s', $this->_getReadAdapter()->quoteIdentifier($term[0], true), $term[1]);
                }
            } else {
                if (!is_numeric($term)) {
                    $orders[] = $this->_getReadAdapter()->quoteIdentifier($term, true);
                }
            }
        }

        if ($autoReset) {
            $select->reset(Zend_Db_Select::ORDER);
        }

        return $orders;
    }

    /**
     * Truncate alias name from field.
     *
     * Result string depends from second optional argument $reverse
     * which can be true if you need the first part of the field.
     * Field can be with 'dot' delimiter.
     *
     * @param string $field
     * @param bool   $reverse OPTIONAL
     * @return string
     */
    protected function _truncateAliasName($field, $reverse = false)
    {
        $string = $field;
        if (!is_numeric($field) && (strpos($field, '.') !== false)) {
            $size  = strpos($field, '.');
            if ($reverse) {
                $string = substr($field, 0, $size);
            } else {
                $string = substr($field, $size + 1);
            }
        }

        return $string;
    }

    /**
     * Returns quoted group by fields
     *
     * @param Varien_Db_Select $select
     * @param bool $autoReset
     * @return array
     */
    protected function _prepareGroup(Varien_Db_Select $select, $autoReset = false)
    {
        $selectGroups = $select->getPart(Zend_Db_Select::GROUP);
        if (!$selectGroups) {
            return array();
        }

        $groups = array();
        foreach ($selectGroups as $term) {
            $groups[] = $this->_getReadAdapter()->quoteIdentifier($term, true);
        }

        if ($autoReset) {
            $select->reset(Zend_Db_Select::GROUP);
        }

        return $groups;
    }

    /**
     * Prepare and returns having array
     *
     * @param Varien_Db_Select $select
     * @param bool $autoReset
     * @return array
     * @throws Zend_Db_Exception
     */
    protected function _prepareHaving(Varien_Db_Select $select, $autoReset = false)
    {
        $selectHavings = $select->getPart(Zend_Db_Select::HAVING);
        if (!$selectHavings) {
            return array();
        }

        $havings = array();
        $columns = $select->getPart(Zend_Db_Select::COLUMNS);
        foreach ($columns as $columnEntry) {
            $correlationName = (string)$columnEntry[1];
            $column          = $columnEntry[2];
            foreach ($selectHavings as $having) {
                /**
                 * Looking for column expression in the having clause
                 */
                if (strpos($having, $correlationName) !== false) {
                    if (is_string($column)) {
                        /**
                         * Replace column expression to column alias in having clause
                         */
                        $havings[] = str_replace($correlationName, $column, $having);
                    } else {
                        throw new Zend_Db_Exception(sprintf("Can't prepare expression without column alias: '%s'", $correlationName));
                    }
                }
            }
        }

        if ($autoReset) {
            $select->reset(Zend_Db_Select::HAVING);
        }

        return $havings;
    }

    /**
     *
     * @param string $query
     * @param int $limitCount
     * @param int $limitOffset
     * @param array $columnList
     * @return string
     */
    protected function _assembleLimit($query, $limitCount, $limitOffset, $columnList = array())
    {
        if ($limitCount !== null) {
              $limitCount = intval($limitCount);
            if ($limitCount <= 0) {
//                throw new Exception("LIMIT argument count={$limitCount} is not valid");
            }

            $limitOffset = intval($limitOffset);
            if ($limitOffset < 0) {
//                throw new Exception("LIMIT argument offset={$limitOffset} is not valid");
            }

            if ($limitOffset + $limitCount != $limitOffset + 1) {
                $columns = array();
                foreach ($columnList as $columnEntry) {
                    $columns[] = $columnEntry[2] ? $columnEntry[2] : $columnEntry[1];
                }

                $query = sprintf('%s LIMIT %s, %s', $query, $limitCount, $limitOffset);
            }
        }

        return $query;
    }

    /**
     * Prepare select column list
     *
     * @param Varien_Db_Select $select
     * @param $groupByCondition OPTIONAL
     * @return array
     * @throws Zend_Db_Exception
     */
    public function prepareColumnsList(Varien_Db_Select $select, $groupByCondition = null)
    {
        if (!count($select->getPart(Zend_Db_Select::FROM))) {
            return $select->getPart(Zend_Db_Select::COLUMNS);
        }

        $columns          = $select->getPart(Zend_Db_Select::COLUMNS);
        $tables           = $select->getPart(Zend_Db_Select::FROM);
        $preparedColumns  = array();

        foreach ($columns as $columnEntry) {
            list($correlationName, $column, $alias) = $columnEntry;
            if ($column instanceof Zend_Db_Expr) {
                if ($alias !== null) {
                    if (preg_match('/(^|[^a-zA-Z_])^(SELECT)?(SUM|MIN|MAX|AVG|COUNT)\s*\(/i', $column, $matches)) {
                        $column = $this->prepareColumn($column, $groupByCondition);
                    }
                    $preparedColumns[strtoupper($alias)] = array(null, $column, $alias);
                } else {
                    throw new Zend_Db_Exception("Can't prepare expression without alias");
                }
            } else {
                if ($column == Zend_Db_Select::SQL_WILDCARD) {
                    if ($tables[$correlationName]['tableName'] instanceof Zend_Db_Expr) {
                        throw new Zend_Db_Exception("Can't prepare expression when tableName is instance of Zend_Db_Expr");
                    }
                    $tableColumns = $this->_getReadAdapter()->describeTable($tables[$correlationName]['tableName']);
                    foreach(array_keys($tableColumns) as $col) {
                        $preparedColumns[strtoupper($col)] = array($correlationName, $col, null);
                    }
                } else {
                    $columnKey = is_null($alias) ? $column : $alias;
                    $preparedColumns[strtoupper($columnKey)] = array($correlationName, $column, $alias);
                }
            }
        }

//        $select->reset(Zend_Db_Select::COLUMNS);
//        $select->setPart(Zend_Db_Select::COLUMNS, array_values($preparedColumns));

        return $preparedColumns;
    }

    /**
     * Add prepared column group_concat expression
     *
     * @param Varien_Db_Select $select
     * @param string $fieldAlias Field alias which will be added with column group_concat expression
     * @param string $fields
     * @param string $groupConcatDelimiter
     * @param string $fieldsDelimiter
     * @param string $additionalWhere
     * @return Varien_Db_Select
     */
    public function addGroupConcatColumn($select, $fieldAlias, $fields, $groupConcatDelimiter = ',', $fieldsDelimiter = '', $additionalWhere = '')
    {
        if (is_array($fields)) {
            $fieldExpr = $this->_getReadAdapter()->getConcatSql($fields, $fieldsDelimiter);
        } else {
            $fieldExpr = $fields;
        }
        if ($additionalWhere) {
            $fieldExpr = $this->_getReadAdapter()->getCheckSql($additionalWhere, $fieldExpr, "''");
        }
        $separator = '';
        if ($groupConcatDelimiter) {
            $separator = sprintf(" SEPARATOR '%s'",  $groupConcatDelimiter);
        }

        $select->columns(array($fieldAlias => new Zend_Db_Expr(sprintf('GROUP_CONCAT(%s%s)', $fieldExpr, $separator))));

        return $select;
    }

    /**
     * Returns expression of days passed from $startDate to $endDate
     *
     * @param  string|Zend_Db_Expr $startDate
     * @param  string|Zend_Db_Expr $endDate
     * @return Zend_Db_Expr
     */
    public function getDateDiff($startDate, $endDate)
    {
        $dateDiff = '(TO_DAYS(' . $endDate . ') - TO_DAYS(' . $startDate . '))';
        return new Zend_Db_Expr($dateDiff);
    }

    /**
     * Escapes and quotes LIKE value.
     * Stating escape symbol in expression is not required, because we use standard MySQL escape symbol.
     * For options and escaping see escapeLikeValue().
     *
     * @param string $value
     * @param array $options
     * @return Zend_Db_Expr
     *
     * @see escapeLikeValue()
     */
    public function addLikeEscape($value, $options = array())
    {
        $value = $this->escapeLikeValue($value, $options);
        return new Zend_Db_Expr($this->_getReadAdapter()->quote($value));
    }
}
