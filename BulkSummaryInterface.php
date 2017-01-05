<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk;

/**
 * Interface BulkSummaryInterface
 * @api
 */
interface BulkSummaryInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BULK_ID = 'uuid';
    const DESCRIPTION = 'description';
    const START_TIME = 'start_time';
    const USER_ID = 'user_id';
    const OPERATION_COUNT = 'operation_count';
    /**#@-*/

    /**#@+
     * Bulk statuses constants
     */
    const NOT_STARTED = 0;
    const IN_PROGRESS = 1;
    const FINISHED_SUCCESSFULLY = 2;
    const FINISHED_WITH_FAILURE = 3;
    /**#@-*/

    /**
     * Get bulk uuid
     *
     * @return string
     */
    public function getBulkId();

    /**
     * Set bulk uuid
     *
     * @param string $bulkUuid
     * @return $this
     */
    public function setBulkId($bulkUuid);

    /**
     * Get bulk description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set bulk description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get bulk scheduled time
     *
     * @return string
     */
    public function getStartTime();

    /**
     * Set bulk scheduled time
     *
     * @param string $timestamp
     * @return $this
     */
    public function setStartTime($timestamp);

    /**
     * Get user id
     *
     * @return int
     */
    public function getUserId();

    /**
     * Set user id
     *
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId);

    /**
     * Get total number of operations scheduled in scope of this bulk
     *
     * @return int
     */
    public function getOperationCount();

    /**
     * Set total number of operations scheduled in scope of this bulk
     *
     * @param int $operationCount
     * @return $this
     */
    public function setOperationCount($operationCount);
}
