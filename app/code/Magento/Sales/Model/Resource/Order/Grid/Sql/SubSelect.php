<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Grid\Sql;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Concat
 */
class SubSelect
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string[]
     */
    protected $columns;

    /**
     * @var string
     */
    protected $originColumn;

    /**
     * @var string
     */
    protected $targetColumn;

    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected $connectionName;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @param Resource $resource
     * @param string $connectionName
     * @param string $table
     * @param string[] $columns
     * @param string $originColumn
     * @param string $targetColumn
     */
    public function __construct(
        Resource $resource,
        $connectionName,
        $table,
        array $columns,
        $originColumn,
        $targetColumn
    ) {
        $this->resource = $resource;
        $this->connectionName = $connectionName;
        $this->table = $table;
        $this->columns = $columns;
        $this->originColumn = $originColumn;
        $this->targetColumn = $targetColumn;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $select = $this->getConnection()->select()->from(
            $this->resource->getTableName($this->table),
            array_values($this->columns)
        )->where(
            sprintf('`%s` = %s', $this->originColumn, $this->targetColumn)
        )->limit(1);
        return sprintf('(%s)', $select);
    }

    /**
     * Returns connection
     *
     * @return AdapterInterface
     */
    protected function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }
        return $this->connection;
    }
}
