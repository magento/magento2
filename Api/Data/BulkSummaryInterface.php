<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Bulk\Api\Data;

/**
 * Interface BulkSummaryInterface
 */
interface BulkSummaryInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BULK_ID = 'uuid';
    const DESCRIPTION = 'description';
    const START_TIME = 'start_time';
    /**#@-*/
    
    /**
     * @return string
     */
    public function getBulkId();

    /**
     * @param string $bulkUiId
     * @return $this
     */
    public function setBulkId($bulkUiId);

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
}
