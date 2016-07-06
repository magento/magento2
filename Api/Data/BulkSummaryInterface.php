<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api\Data;

/**
 * Interface BulkSummaryInterface
 */
interface BulkSummaryInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BULK_ID = 'uuid';
    const DESCRIPTION = 'description';
    const START_TIME = 'start_time';
    const USER_ID = 'user_id';
    /**#@-*/

    /**#@+
     * Bulk statuses constants
     */
    const NOT_STARTED = 0;
    const IN_PROGRESS_SUCCESS = 1;
    const IN_PROGRESS_FAILED = 2;
    const FINISHED_SUCCESSFULLY = 3;
    const FINISHED_WITH_FAILURE = 4;
    /**#@-*/

    /**
     * @return string
     */
    public function getBulkId();

    /**
     * @param string $bulkUuid
     * @return $this
     */
    public function setBulkId($bulkUuid);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getStartTime();

    /**
     * @param string $timestamp
     * @return $this
     */
    public function setStartTime($timestamp);

    /**
     * @return int
     */
    public function getUserId();

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Framework\Bulk\Api\Data\BulkSummaryExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Framework\Bulk\Api\Data\BulkSummaryExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Framework\Bulk\Api\Data\BulkSummaryExtensionInterface $extensionAttributes
    );
}
