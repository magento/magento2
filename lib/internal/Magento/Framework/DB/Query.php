<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Psr\Log\LoggerInterface as Logger;

/**
 * Class Query
 */
class Query implements QueryInterface
{
    /**
     * Select object
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $select;

    /**
     * @var \Magento\Framework\Api\CriteriaInterface
     */
    protected $criteria;

    /**
     * Resource instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;

    /**
     * Database's statement for fetch item one by one
     *
     * @var \Zend_Db_Statement_Pdo
     */
    protected $fetchStmt = null;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    private $fetchStrategy;

    /**
     * @var array
     */
    protected $bindParams = [];

    /**
     * @var int
     */
    protected $totalRecords;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * Query Select Parts to be skipped when prepare query for count
     *
     * @var array
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
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Retrieve all ids for query
     *
     * @return array
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
     */
    public function addBindParam($name, $value)
    {
        $this->bindParams[$name] = $value;
    }

    /**
     * Get collection size
     *
     * @return int
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
     */
    public function getIdFieldName()
    {
        return $this->getResource()->getIdFieldName();
    }

    /**
     * Retrieve connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        return $this->getSelect()->getConnection();
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     */
    public function addCountSqlSkipPart($name, $toSkip = true)
    {
        $this->countSqlSkipParts[$name] = $toSkip;
    }

    /**
     * Get SQL for get record count
     *
     * @return Select
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
     */
    protected function getCountSqlSkipParts()
    {
        return $this->countSqlSkipParts;
    }

    /**
     * Get \Magento\Framework\DB\Select object instance
     *
     * @return Select
     */
    protected function getSelect()
    {
        return $this->select;
    }
}
