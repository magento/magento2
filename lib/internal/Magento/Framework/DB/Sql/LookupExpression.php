<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Class LookupExpression
 */
class LookupExpression extends \Zend_Db_Expr
{

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $targetColumn;

    /**
     * @var string
     */
    protected $targetTable;

    /**
     * @var array
     */
    protected $referenceColumns;

    /**
     * @var array
     */
    protected $sortOrder;

    /**
     * @param Resource $resource
     * @param $targetColumn
     * @param $targetTable
     * @param array $referenceColumns
     * @param array $sortOrder
     */
    public function __construct(
        Resource $resource,
        $targetColumn,
        $targetTable,
        array $referenceColumns = [],
        array $sortOrder = []
    ) {
        $this->targetTable = $targetTable;
        $this->targetColumn = $targetColumn;
        $this->referenceColumns = $referenceColumns;
        $this->sortOrder = $sortOrder;
        $this->resource = $resource;
        $this->adapter = $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * Process WHERE clause
     *
     * @param Select $select
     */
    protected function processWhereCondition(Select $select)
    {
        foreach ($this->referenceColumns as $column => $referenceColumn) {
            $select->where(
                sprintf('%s = %s',
                    $this->adapter->quoteIdentifier('lookup.' . $column),
                    $this->adapter->quoteIdentifier(
                        (isset($referenceColumn['tableAlias']) ? $referenceColumn['tableAlias'] . '.' : '')
                        . (isset($referenceColumn['columnName']) ? $referenceColumn['columnName'] : $column)
                    )
                )
            );
        }
    }

    /**
     * Process ORDER BY clause
     *
     * @param Select $select
     */
    protected function processSortOrder(Select $select)
    {
        foreach ($this->sortOrder as $direction => $column) {
            if (!in_array($direction, [Select::SQL_ASC, Select::SQL_DESC])) {
                $direction = '';
            }
            $select->order(new \Zend_Db_Expr(
                sprintf('%s %s', $this->adapter->quoteIdentifier('lookup.' . $column)), $direction)
            );
        }
    }

    /**
     * Returns lookup SQL
     *
     * @return string
     */
    public function __toString()
    {
        $select = $this->adapter->select()
            ->from(['lookup' => $this->resource->getTableName($this->targetTable)], [$this->targetColumn])
            ->limit(1);
        $this->processWhereCondition($select);
        $this->processSortOrder($select);
        return sprintf('(%s)', $select->assemble());
    }
}
