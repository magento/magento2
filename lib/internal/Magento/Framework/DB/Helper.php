<?php
/**
 * DB helper class for MySql Magento DB Adapter
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

/**
 * DataBase Helper
 */
class Helper extends \Magento\Framework\DB\Helper\AbstractHelper
{
    /**
     * Returns array of quoted orders with direction
     *
     * @param \Magento\Framework\DB\Select $select
     * @param bool $autoReset
     * @return array
     */
    protected function _prepareOrder(\Magento\Framework\DB\Select $select, $autoReset = false)
    {
        $selectOrders = $select->getPart(\Magento\Framework\DB\Select::ORDER);
        if (!$selectOrders) {
            return [];
        }

        $orders = [];
        foreach ($selectOrders as $term) {
            if (is_array($term)) {
                if (!is_numeric($term[0])) {
                    $orders[] = sprintf('%s %s', $this->getConnection()->quoteIdentifier($term[0], true), $term[1]);
                }
            } else {
                if (!is_numeric($term)) {
                    $orders[] = $this->getConnection()->quoteIdentifier($term, true);
                }
            }
        }

        if ($autoReset) {
            $select->reset(\Magento\Framework\DB\Select::ORDER);
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
     * @param bool $reverse OPTIONAL
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
     * @param \Magento\Framework\DB\Select $select
     * @param bool $autoReset
     * @return array
     */
    protected function _prepareGroup(\Magento\Framework\DB\Select $select, $autoReset = false)
    {
        $selectGroups = $select->getPart(\Magento\Framework\DB\Select::GROUP);
        if (!$selectGroups) {
            return [];
        }

        $groups = [];
        foreach ($selectGroups as $term) {
            $groups[] = $this->getConnection()->quoteIdentifier($term, true);
        }

        if ($autoReset) {
            $select->reset(\Magento\Framework\DB\Select::GROUP);
        }

        return $groups;
    }

    /**
     * Prepare and returns having array
     *
     * @param \Magento\Framework\DB\Select $select
     * @param bool $autoReset
     * @return array
     * @throws \Zend_Db_Exception
     */
    protected function _prepareHaving(\Magento\Framework\DB\Select $select, $autoReset = false)
    {
        $selectHavings = $select->getPart(\Magento\Framework\DB\Select::HAVING);
        if (!$selectHavings) {
            return [];
        }

        $havings = [];
        $columns = $select->getPart(\Magento\Framework\DB\Select::COLUMNS);
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
                        throw new \Zend_Db_Exception(
                            sprintf("Can't prepare expression without column alias: '%s'", $correlationName)
                        );
                    }
                }
            }
        }

        if ($autoReset) {
            $select->reset(\Magento\Framework\DB\Select::HAVING);
        }

        return $havings;
    }

    /**
     * Assemble limit
     *
     * @param string $query
     * @param int $limitCount
     * @param int $limitOffset
     * @param array $columnList
     * @return string
     */
    protected function _assembleLimit($query, $limitCount, $limitOffset, $columnList = [])
    {
        if ($limitCount !== null) {
            $limitCount = (int)$limitCount;
            if ($limitCount <= 0) {
                //throw new \Exception("LIMIT argument count={$limitCount} is not valid");
            }

            $limitOffset = (int)$limitOffset;
            if ($limitOffset < 0) {
                //throw new \Exception("LIMIT argument offset={$limitOffset} is not valid");
            }

            if ($limitOffset + $limitCount != $limitOffset + 1) {
                $columns = [];
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
     * @param \Magento\Framework\DB\Select $select
     * @param string|null $groupByCondition OPTIONAL
     * @return mixed|array
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareColumnsList(\Magento\Framework\DB\Select $select, $groupByCondition = null)
    {
        if (!count($select->getPart(\Magento\Framework\DB\Select::FROM))) {
            return $select->getPart(\Magento\Framework\DB\Select::COLUMNS);
        }

        $columns          = $select->getPart(\Magento\Framework\DB\Select::COLUMNS);
        $tables           = $select->getPart(\Magento\Framework\DB\Select::FROM);
        $preparedColumns  = [];

        foreach ($columns as $columnEntry) {
            list($correlationName, $column, $alias) = $columnEntry;
            if ($column instanceof \Zend_Db_Expr) {
                if ($alias !== null) {
                    if (preg_match('/(^|[^a-zA-Z_])^(SELECT)?(SUM|MIN|MAX|AVG|COUNT)\s*\(/i', $column)) {
                        $column = new \Zend_Db_Expr($column);
                    }
                    $preparedColumns[strtoupper($alias)] = [null, $column, $alias];
                } else {
                    throw new \Zend_Db_Exception("Can't prepare expression without alias");
                }
            } else {
                if ($column == \Magento\Framework\DB\Select::SQL_WILDCARD) {
                    if ($tables[$correlationName]['tableName'] instanceof \Zend_Db_Expr) {
                        throw new \Zend_Db_Exception(
                            "Can't prepare expression when tableName is instance of \Zend_Db_Expr"
                        );
                    }
                    $tableColumns = $this->getConnection()->describeTable($tables[$correlationName]['tableName']);
                    foreach (array_keys($tableColumns) as $col) {
                        $preparedColumns[strtoupper($col)] = [$correlationName, $col, null];
                    }
                } else {
                    $columnKey = $alias === null ? $column : $alias;
                    $preparedColumns[strtoupper($columnKey)] = [$correlationName, $column, $alias];
                }
            }
        }

        return $preparedColumns;
    }

    /**
     * Add prepared column group_concat expression
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $fieldAlias Field alias which will be added with column group_concat expression
     * @param string $fields
     * @param string $groupConcatDelimiter
     * @param string $fieldsDelimiter
     * @param string $additionalWhere
     * @return \Magento\Framework\DB\Select
     */
    public function addGroupConcatColumn(
        $select,
        $fieldAlias,
        $fields,
        $groupConcatDelimiter = ',',
        $fieldsDelimiter = '',
        $additionalWhere = ''
    ) {
        if (is_array($fields)) {
            $fieldExpr = $this->getConnection()->getConcatSql($fields, $fieldsDelimiter);
        } else {
            $fieldExpr = $fields;
        }
        if ($additionalWhere) {
            $fieldExpr = $this->getConnection()->getCheckSql($additionalWhere, $fieldExpr, "''");
        }
        $separator = '';
        if ($groupConcatDelimiter) {
            $separator = sprintf(" SEPARATOR '%s'", $groupConcatDelimiter);
        }
        $select->columns([$fieldAlias => new \Zend_Db_Expr(sprintf('GROUP_CONCAT(%s%s)', $fieldExpr, $separator))]);
        return $select;
    }

    /**
     * Returns expression of days passed from $startDate to $endDate
     *
     * @param  string|\Zend_Db_Expr $startDate
     * @param  string|\Zend_Db_Expr $endDate
     * @return \Zend_Db_Expr
     */
    public function getDateDiff($startDate, $endDate)
    {
        $dateDiff = '(TO_DAYS(' . $endDate . ') - TO_DAYS(' . $startDate . '))';
        return new \Zend_Db_Expr($dateDiff);
    }

    /**
     * Escapes and quotes LIKE value.
     * Stating escape symbol in expression is not required, because we use standard MySQL escape symbol.
     * For options and escaping see escapeLikeValue().
     *
     * @param string $value
     * @param array $options
     * @return \Zend_Db_Expr
     *
     * @see escapeLikeValue()
     */
    public function addLikeEscape($value, $options = [])
    {
        $value = $this->escapeLikeValue($value, $options);
        return new \Zend_Db_Expr($this->getConnection()->quote($value));
    }
}
