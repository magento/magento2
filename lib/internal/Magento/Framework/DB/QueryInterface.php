<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

/**
 * Class QueryInterface
 * @since 2.0.0
 */
interface QueryInterface
{
    /**
     * Retrieve source Criteria object
     *
     * @return \Magento\Framework\Api\CriteriaInterface
     * @since 2.0.0
     */
    public function getCriteria();

    /**
     * Retrieve all ids for query
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllIds();

    /**
     * Add variable to bind list
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @since 2.0.0
     */
    public function addBindParam($name, $value);

    /**
     * Get collection size
     *
     * @return int
     * @since 2.0.0
     */
    public function getSize();

    /**
     * Get sql select string or object
     *
     * @param bool $stringMode
     * @return string || Select
     * @since 2.0.0
     */
    public function getSelectSql($stringMode = false);

    /**
     * Reset Statement object
     *
     * @return void
     * @since 2.0.0
     */
    public function reset();

    /**
     * Fetch all statement
     *
     * @return array
     * @since 2.0.0
     */
    public function fetchAll();

    /**
     * Fetch statement
     *
     * @return mixed
     * @since 2.0.0
     */
    public function fetchItem();

    /**
     * Get Identity Field Name
     *
     * @return string
     * @since 2.0.0
     */
    public function getIdFieldName();

    /**
     * Retrieve connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    public function getConnection();

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @since 2.0.0
     */
    public function getResource();

    /**
     * Add Select Part to skip from count query
     *
     * @param string $name
     * @param bool $toSkip
     * @return void
     * @since 2.0.0
     */
    public function addCountSqlSkipPart($name, $toSkip = true);
}
