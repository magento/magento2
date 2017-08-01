<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Psr\Log\LoggerInterface as Logger;

/**
 * Class Query
 * @since 2.0.0
 */
class Query implements QueryInterface
{
    /**
     * Select object
     *
     * @var \Magento\Framework\DB\Select
     * @since 2.0.0
     */
    protected $select;

    /**
     * @var \Magento\Framework\Api\CriteriaInterface
     * @since 2.0.0
     */
    protected $criteria;

    /**
     * Resource instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @since 2.0.0
     */
    protected $resource;

    /**
     * Database's statement for fetch item one by one
     *
     * @var \Zend_Db_Statement_Pdo
     * @since 2.0.0
     */
    protected $fetchStmt = null;

    /**
     * @var Logger
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     * @since 2.0.0
     */
    private $fetchStrategy;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $bindParams = [];

    /**
     * @var int
     * @since 2.0.0
     */
    protected $totalRecords;

    /**
     * @var mixed
     * @since 2.0.0
     */
    protected $data;

    /**
     * Query Select Parts to be skipped when prepare query for count
     *
     * @var array
     * @since 2.0.0
     */
    protected $countSqlSkipParts = [
        \Magento\Framework\DB\Select::ORDER => true,
        \Magento\Framework\DB\Select::LIMIT_COUNT => true,
        \Magento\Framework\DB\Select::LIMIT_OFFSET => true,
        \Magento\Framework\DB\Select::COLUMNS => true,
    ];

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param \Magento\Framework\Api\CriteriaInterface $criteria
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\DB\Select $select,
        \Magento\Framework\Api\CriteriaInterface $criteria,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
    ) {
        $this->select = $select;
        $this->criteria = $criteria;
        $this->resource = $resource;
        $this->fetchStrategy = $fetchStrategy;
    }

    /**
     * Retrieve source Criteria object
     *
     * @return \Magento\Framework\Api\CriteriaInterface
     * @since 2.0.0
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Retrieve all ids for query
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        return $this->getConnection()->fetchCol($idsSelect, $this->bindParams);
    }

    /**
     * Add variable to bind list
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @since 2.0.0
     */
    public function addBindParam($name, $value)
    {
        $this->bindParams[$name] = $value;
    }

    /**
     * Get collection size
     *
     * @return int
     * @since 2.0.0
     */
    public function getSize()
    {
        if ($this->totalRecords === null) {
            $sql = $this->getSelectCountSql();
            $this->totalRecords = $this->getConnection()->fetchOne($sql, $this->bindParams);
        }
        return intval($this->totalRecords);
    }

    /**
     * Get sql select string or object
     *
     * @param bool $stringMode
     * @return string || Select
     * @since 2.0.0
     */
    public function getSelectSql($stringMode = false)
    {
        if ($stringMode) {
            return $this->select->__toString();
        }
        return $this->select;
    }

    /**
     * Reset Statement object
     *
     * @return void
     * @since 2.0.0
     */
    public function reset()
    {
        $this->fetchStmt = null;
        $this->data = null;
    }

    /**
     * Fetch all statement
     *
     * @return array
     * @since 2.0.0
     */
    public function fetchAll()
    {
        if ($this->data === null) {
            $select = $this->getSelect();
            $this->data = $this->fetchStrategy->fetchAll($select, $this->bindParams);
        }
        return $this->data;
    }

    /**
     * Fetch statement
     *
     * @return mixed
     * @since 2.0.0
     */
    public function fetchItem()
    {
        if (null === $this->fetchStmt) {
            $this->fetchStmt = $this->getConnection()->query($this->getSelect(), $this->bindParams);
        }
        $data = $this->fetchStmt->fetch();
        if (!$data) {
            $data = [];
        }
        return $data;
    }

    /**
     * Get Identity Field Name
     *
     * @return string
     * @since 2.0.0
     */
    public function getIdFieldName()
    {
        return $this->getResource()->getIdFieldName();
    }

    /**
     * Retrieve connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    public function getConnection()
    {
        return $this->getSelect()->getConnection();
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @since 2.0.0
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Add Select Part to skip from count query
     *
     * @param string $name
     * @param bool $toSkip
     * @return void
     * @since 2.0.0
     */
    public function addCountSqlSkipPart($name, $toSkip = true)
    {
        $this->countSqlSkipParts[$name] = $toSkip;
    }

    /**
     * Get SQL for get record count
     *
     * @return Select
     * @since 2.0.0
     */
    protected function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        foreach ($this->getCountSqlSkipParts() as $part => $toSkip) {
            if ($toSkip) {
                $countSelect->reset($part);
            }
        }
        $countSelect->columns('COUNT(*)');

        return $countSelect;
    }

    /**
     * Returned count SQL skip parts
     *
     * @return array
     * @since 2.0.0
     */
    protected function getCountSqlSkipParts()
    {
        return $this->countSqlSkipParts;
    }

    /**
     * Get \Magento\Framework\DB\Select object instance
     *
     * @return Select
     * @since 2.0.0
     */
    protected function getSelect()
    {
        return $this->select;
    }
}
