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
 * @since 2.0.0
 */
class SubSelect extends \Zend_Db_Expr
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $table;

    /**
     * @var string[]
     * @since 2.0.0
     */
    protected $columns;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $originColumn;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $targetColumn;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $resource;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $connectionName;

    /**
     * @var AdapterInterface
     * @since 2.0.0
     */
    protected $connection;

    /**
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param string $table
     * @param string[] $columns
     * @param string $originColumn
     * @param string $targetColumn
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getConnection()
    {
        if (!$this->connection) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }
        return $this->connection;
    }
}
