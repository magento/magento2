<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

/**
 * Class QueryInterface
 */
interface QueryInterface
{
    /**
     * Retrieve source Criteria object
     *
     * @return \Magento\Framework\Api\CriteriaInterface
     */
    public function getCriteria();

    /**
     * Retrieve all ids for query
     *
     * @return array
     */
    public function getAllIds();

    /**
     * Add variable to bind list
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addBindParam($name, $value);

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize();

    /**
     * Get sql select string or object
     *
     * @param bool $stringMode
     * @return string || Select
     */
    public function getSelectSql($stringMode = false);

    /**
     * Reset Statement object
     *
     * @return void
     */
    public function reset();

    /**
     * Fetch all statement
     *
     * @return array
     */
    public function fetchAll();

    /**
     * Fetch statement
     *
     * @return mixed
     */
    public function fetchItem();

    /**
     * Get Identity Field Name
     *
     * @return string
     */
    public function getIdFieldName();

    /**
     * Retrieve connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection();

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource();

    /**
     * Add Select Part to skip from count query
     *
     * @param string $name
     * @param bool $toSkip
     * @return void
     */
    public function addCountSqlSkipPart($name, $toSkip = true);
}
