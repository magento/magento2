<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\App\ResourceConnection;

/**
 * Class ConditionResolver
 *
 * Mapper for WHERE conditions
 */
class ConditionResolver
{
    /**
     * @var array
     */
    private $conditionMap = [
        'eq' => '%1$s = %2$s',
        'neq' => '%1$s != %2$s',
        'like' => '%1$s LIKE %2$s',
        'nlike' => '%1$s NOT LIKE %2$s',
        'in' => '%1$s IN(%2$s)',
        'nin' => '%1$s NOT IN(%2$s)',
        'notnull' => '%1$s IS NOT NULL',
        'null' => '%1$s IS NULL',
        'gt' => '%1$s > %2$s',
        'lt' => '%1$s < %2$s',
        'gteq' => '%1$s >= %2$s',
        'lteq' => '%1$s <= %2$s',
        'finset' => 'FIND_IN_SET(%2$s, %1$s)'
    ];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resourceConnection->getConnection();
        }
        return $this->connection;
    }

    /**
     * Returns value for condition
     *
     * @param string $condition
     * @param string $referencedEntity
     * @return mixed|null|string|\Zend_Db_Expr
     */
    private function getValue($condition, $referencedEntity)
    {
        $value = null;
        $argument = isset($condition['_value']) ? $condition['_value'] : null;
        if (!isset($condition['type'])) {
            $condition['type'] = 'value';
        }

        switch ($condition['type']) {
            case "value":
                $value = $this->getConnection()->quote($argument);
                break;
            case "variable":
                $value = new \Zend_Db_Expr($argument);
                break;
            case "identifier":
                $value = $this->getConnection()->quoteIdentifier(
                    $referencedEntity ? $referencedEntity . '.' . $argument : $argument
                );
                break;
        }
        return $value;
    }

    /**
     * Returns condition for WHERE
     *
     * @param SelectBuilder $selectBuilder
     * @param string $tableName
     * @param array $condition
     * @param null|string $referencedEntity
     * @return string
     */
    private function getCondition(SelectBuilder $selectBuilder, $tableName, $condition, $referencedEntity = null)
    {
        $columns = $selectBuilder->getColumns();
        if (isset($columns[$condition['attribute']]) && $columns[$condition['attribute']] instanceof \Zend_Db_Expr) {
            $expression = $columns[$condition['attribute']];
        } else {
            $expression = $tableName . '.' . $condition['attribute'];
        }
        return sprintf(
            $this->conditionMap[$condition['operator']],
            $expression,
            $this->getValue($condition, $referencedEntity)
        );
    }

    /**
     * Build WHERE condition
     *
     * @param SelectBuilder $selectBuilder
     * @param array $filterConfig
     * @param string $aliasName
     * @param null|string $referencedAlias
     * @return array
     */
    public function getFilter(SelectBuilder $selectBuilder, $filterConfig, $aliasName, $referencedAlias = null)
    {
        $filtersParts = [];
        foreach ($filterConfig as $filter) {
            $glue = $filter['glue'];
            $parts = [];
            foreach ($filter['condition'] as $condition) {
                if (isset($condition['type']) && $condition['type'] == 'variable') {
                    $selectBuilder->setParams(array_merge($selectBuilder->getParams(), [$condition['_value']]));
                }
                $parts[] = $this->getCondition(
                    $selectBuilder,
                    $aliasName,
                    $condition,
                    $referencedAlias
                );
            }
            if (isset($filter['filter'])) {
                $parts[] = '(' . $this->getFilter(
                    $selectBuilder,
                    $filter['filter'],
                    $aliasName,
                    $referencedAlias
                ) . ')';
            }
            $filtersParts[] = '(' . implode(' ' . strtoupper($glue) . ' ', $parts) . ')';
        }
        return implode(' AND ', $filtersParts);
    }
}
