<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Concat
 */
class SubSelect extends \Zend_Db_Expr
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
     * @var \Magento\Framework\App\ResourceConnection
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
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param string $table
     * @param string[] $columns
     * @param string $originColumn
     * @param string $targetColumn
     */
    public function __construct(
        ResourceConnection $resource,
        $table,
        array $columns,
        $originColumn,
        $targetColumn,
        $connectionName = ResourceConnection::DEFAULT_CONNECTION
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
            sprintf(
                '%s = %s',
                $this->getConnection()->quoteIdentifier($this->originColumn),
                $this->getConnection()->quoteIdentifier($this->targetColumn)
            )
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
